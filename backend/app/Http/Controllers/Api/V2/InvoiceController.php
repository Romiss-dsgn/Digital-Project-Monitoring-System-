<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\CashflowPeriod;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\InvoiceDocument;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class InvoiceController extends Controller
{
    private const STATUSES = ['Pending', 'For Review', 'Approved', 'Rejected', 'Paid'];

    public function index(Request $request): JsonResponse
    {
        $query = Invoice::query()
            ->with([
                'contract:id,contract_number,contract_title',
                'cashflowPeriod:id,period_label',
                'verifier:id,name',
                'approver:id,name',
                'creator:id,name',
            ])
            ->withSum('payments as payments_sum_amount_paid', 'amount_paid')
            ->whereHas('contract', fn (Builder $query) => $query->where('is_archived', false))
            ->when($request->string('search')->toString(), function (Builder $query, string $search) {
                $query->where(function (Builder $nested) use ($search) {
                    $nested->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('contract', fn (Builder $contract) => $contract
                            ->where('contract_number', 'like', "%{$search}%"));
                });
            })
            ->when($request->query('status'), fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($request->integer('contract_id'), fn (Builder $query, int $contractId) => $query->where('contract_id', $contractId))
            ->latest();

        $invoices = $query->paginate($this->perPage($request));

        return response()->json([
            'data' => collect($invoices->items())
                ->map(fn (Invoice $invoice) => $this->formatInvoice($invoice)),
            'meta' => $this->paginationMeta($invoices),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $query = Invoice::query()
            ->whereHas('contract', fn (Builder $query) => $query->where('is_archived', false));

        return response()->json([
            'data' => [
                'total_amount' => (float) (clone $query)->sum('invoice_amount'),
                'total_paid' => (float) (clone $query)->where('status', 'Paid')->sum('invoice_amount'),
                'pending_count' => (clone $query)->where('status', 'Pending')->count(),
                'for_review_count' => (clone $query)->where('status', 'For Review')->count(),
                'approved_count' => (clone $query)->where('status', 'Approved')->count(),
                'rejected_count' => (clone $query)->where('status', 'Rejected')->count(),
                'permissions' => $request->user()->modulePermissions('invoices'),
            ],
        ]);
    }

    public function options(Request $request): JsonResponse
    {
        return response()->json([
            'contracts' => Contract::query()
                ->where('is_archived', false)
                ->orderBy('contract_number')
                ->get(['id', 'contract_number', 'contract_title']),
            'cashflow_periods' => CashflowPeriod::query()
                ->whereHas('contract', fn (Builder $query) => $query->where('is_archived', false))
                ->get(['id', 'period_label']),
            'statuses' => self::STATUSES,
            'permissions' => $request->user()->modulePermissions('invoices'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contract_id' => [
                'required',
                Rule::exists('contracts', 'id')->where(fn ($query) => $query->where('is_archived', false)),
            ],
            'cashflow_period_id' => [
                'nullable',
                Rule::exists('cashflow_periods', 'id'),
            ],
            'previous_accomplishment_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'accomplishment_today_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'invoice_number' => ['required', 'string', 'max:255', 'unique:invoices,invoice_number'],
            'billing_period' => ['nullable', 'string', 'max:255'],
            'invoice_amount' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'invoice_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        $invoice = DB::transaction(function () use ($request, $validated) {
            $invoice = Invoice::create($validated + [
                'created_by' => $request->user()->id,
            ]);

            AuditLogger::record(
                $request,
                'created',
                'invoices',
                $invoice->id,
                $invoice->invoice_number,
                null,
                $invoice->toArray()
            );

            return $invoice;
        });

        return response()->json([
            'message' => 'Invoice created successfully.',
            'data' => $this->formatInvoice($invoice->load(['contract', 'cashflowPeriod'])->loadSum('payments', 'amount_paid')),
        ], 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load([
            'contract:id,contract_number,contract_title',
            'cashflowPeriod:id,period_label',
            'verifier:id,name',
            'approver:id,name',
            'creator:id,name',
            'documents',
        ]);
        $invoice->loadSum('payments', 'amount_paid');

        return response()->json(['data' => $this->formatInvoice($invoice)]);
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'invoice_number' => [
                'sometimes', 'string', 'max:255',
                Rule::unique('invoices', 'invoice_number')->ignore($invoice->id),
            ],
            'cashflow_period_id' => [
                'sometimes', 'nullable',
                Rule::exists('cashflow_periods', 'id'),
            ],
            'previous_accomplishment_percent' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'accomplishment_today_percent' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'billing_period' => ['sometimes', 'nullable', 'string', 'max:255'],
            'invoice_amount' => ['sometimes', 'numeric', 'min:0', 'max:9999999999999.99'],
            'invoice_date' => ['sometimes', 'nullable', 'date'],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'remarks' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);

        $oldValues = $invoice->toArray();

        DB::transaction(function () use ($request, $invoice, $validated, $oldValues) {
            $invoice->update($validated);

            AuditLogger::record(
                $request,
                'updated',
                'invoices',
                $invoice->id,
                $invoice->invoice_number,
                $oldValues,
                $invoice->fresh()->toArray()
            );
        });

        return response()->json([
            'message' => 'Invoice updated successfully.',
            'data' => $this->formatInvoice($invoice->fresh(['contract', 'cashflowPeriod'])->loadSum('payments', 'amount_paid')),
        ]);
    }

    public function verify(Request $request, Invoice $invoice): JsonResponse
    {
        $oldValues = $invoice->toArray();

        $invoice->update([
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'status' => 'For Review',
        ]);

        AuditLogger::record(
            $request,
            'verified',
            'invoices',
            $invoice->id,
            $invoice->invoice_number,
            $oldValues,
            $invoice->fresh()->toArray()
        );

        return response()->json([
            'message' => 'Invoice verified successfully.',
            'data' => $this->formatInvoice($invoice->fresh(['contract', 'cashflowPeriod'])->loadSum('payments', 'amount_paid')),
        ]);
    }

    public function approve(Request $request, Invoice $invoice): JsonResponse
    {
        abort_unless($request->user()->hasModulePermission('invoices', 'approve'), 403);

        $oldValues = $invoice->toArray();

        $invoice->update([
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'status' => 'Approved',
        ]);

        AuditLogger::record(
            $request,
            'approved',
            'invoices',
            $invoice->id,
            $invoice->invoice_number,
            $oldValues,
            $invoice->fresh()->toArray()
        );

        return response()->json([
            'message' => 'Invoice approved successfully.',
            'data' => $this->formatInvoice($invoice->fresh(['contract', 'cashflowPeriod'])->loadSum('payments', 'amount_paid')),
        ]);
    }

    public function destroy(Request $request, Invoice $invoice): JsonResponse
    {
        $oldValues = $invoice->toArray();

        $invoice->delete();

        AuditLogger::record(
            $request,
            'deleted',
            'invoices',
            $invoice->id,
            $invoice->invoice_number,
            $oldValues,
            null
        );

        return response()->json(['message' => 'Invoice deleted successfully.']);
    }

    public function uploadDocument(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,docx,xlsx,jpg,jpeg,png', 'max:25600'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $file = $request->file('file');
        $path = $file->store("invoice-documents/{$invoice->id}", 'local');

        try {
            $document = DB::transaction(function () use ($request, $invoice, $file, $path, $validated) {
                return InvoiceDocument::create([
                    'invoice_id' => $invoice->id,
                    'document_title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'uploaded_by' => $request->user()->id,
                    'uploaded_at' => now(),
                    'remarks' => $validated['remarks'] ?? null,
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        AuditLogger::record(
            $request,
            'uploaded',
            'invoice_documents',
            $document->id,
            $invoice->invoice_number,
            null,
            $document->toArray()
        );

        return response()->json([
            'message' => 'Invoice document uploaded successfully.',
            'data' => $this->formatDocument($document->load('uploader:id,name')),
        ], 201);
    }

    public function downloadDocument(InvoiceDocument $document): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($document->file_path), 404, 'Stored file was not found.');

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    private function formatInvoice(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'contract_id' => $invoice->contract_id,
            'contract_number' => $invoice->contract?->contract_number,
            'cashflow_period_id' => $invoice->cashflow_period_id,
            'cashflow_period_label' => $invoice->cashflowPeriod?->period_label,
            'invoice_number' => $invoice->invoice_number,
            'billing_period' => $invoice->billing_period,
            'invoice_amount' => (float) $invoice->invoice_amount,
            'previous_accomplishment_percent' => $invoice->previous_accomplishment_percent !== null ? (float) $invoice->previous_accomplishment_percent : null,
            'accomplishment_today_percent' => $invoice->accomplishment_today_percent !== null ? (float) $invoice->accomplishment_today_percent : null,
            'invoice_date' => optional($invoice->invoice_date)->format('Y-m-d'),
            'due_date' => optional($invoice->due_date)->format('Y-m-d'),
            'paid_amount' => (float) ($invoice->payments_sum_amount_paid ?? 0),
            'remaining_balance' => max(0, (float) $invoice->invoice_amount - (float) ($invoice->payments_sum_amount_paid ?? 0)),
            'status' => $invoice->status,
            'verified_by' => $invoice->verifier?->name,
            'verified_at' => optional($invoice->verified_at)->format('Y-m-d H:i:s'),
            'approved_by' => $invoice->approver?->name,
            'approved_at' => optional($invoice->approved_at)->format('Y-m-d H:i:s'),
            'remarks' => $invoice->remarks,
            'documents' => $invoice->relationLoaded('documents')
                ? $invoice->documents->map(fn (InvoiceDocument $doc) => $this->formatDocument($doc))
                : [],
            'created_at' => optional($invoice->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($invoice->updated_at)->format('Y-m-d H:i:s'),
        ];
    }

    private function formatDocument(InvoiceDocument $document): array
    {
        return [
            'id' => $document->id,
            'invoice_id' => $document->invoice_id,
            'document_title' => $document->document_title,
            'file_name' => $document->file_name,
            'file_type' => $document->file_type,
            'uploaded_by' => $document->uploader?->name,
            'uploaded_at' => optional($document->uploaded_at)->format('Y-m-d H:i:s'),
            'remarks' => $document->remarks,
        ];
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 10), 1), 100);
    }

    private function paginationMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
