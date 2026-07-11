<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\VariationOrder;
use App\Models\VariationOrderDocument;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class VariationOrderController extends Controller
{
    private const STATUSES = ['Draft', 'Submitted', 'Under Review', 'Approved', 'Rejected', 'Archived'];

    public function index(Request $request): JsonResponse
    {
        $query = VariationOrder::query()
            ->with([
                'contract:id,contract_number,contract_title',
                'submitter:id,name',
                'reviewer:id,name',
                'approver:id,name',
            ])
            ->where('is_archived', false)
            ->when($request->string('search')->toString(), function (Builder $query, string $search) {
                $query->where(function (Builder $nested) use ($search) {
                    $nested->where('vo_number', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('contract', fn (Builder $contract) => $contract
                            ->where('contract_number', 'like', "%{$search}%")
                            ->orWhere('contract_title', 'like', "%{$search}%"));
                });
            })
            ->when($request->query('status'), fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($request->integer('contract_id'), fn (Builder $query, int $contractId) => $query->where('contract_id', $contractId))
            ->latest();

        $orders = $query->paginate($this->perPage($request));

        return response()->json([
            'data' => collect($orders->items())
                ->map(fn (VariationOrder $order) => $this->formatOrder($order)),
            'meta' => $this->paginationMeta($orders),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $query = VariationOrder::query()->where('is_archived', false);

        $totalAmount = (clone $query)->sum('amount_change');
        $approvedAmount = (clone $query)->where('status', 'Approved')->sum('amount_change');

        $draftCount = (clone $query)->where('status', 'Draft')->count();
        $submittedCount = (clone $query)->where('status', 'Submitted')->count();
        $underReviewCount = (clone $query)->where('status', 'Under Review')->count();
        $approvedCount = (clone $query)->where('status', 'Approved')->count();
        $rejectedCount = (clone $query)->where('status', 'Rejected')->count();
        $totalCount = (clone $query)->count();

        $monthlyBreakdown = $this->getMonthlyBreakdown($query);

        return response()->json([
            'data' => [
                'total_vos' => $totalCount,
                'approved_count' => $approvedCount,
                'pending_count' => $draftCount + $submittedCount + $underReviewCount,
                'rejected_count' => $rejectedCount,
                'total_cost_impact' => (float) $totalAmount,
                'approved_cost_impact' => (float) $approvedAmount,
                'average_approval_days' => $this->calculateAverageApprovalDays($query),
                'status_distribution' => [
                    'draft' => $draftCount,
                    'submitted' => $submittedCount,
                    'under_review' => $underReviewCount,
                    'approved' => $approvedCount,
                    'rejected' => $rejectedCount,
                ],
                'monthly_breakdown' => $monthlyBreakdown,
                'permissions' => $request->user()->modulePermissions('variation_orders'),
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
            'statuses' => self::STATUSES,
            'permissions' => $request->user()->modulePermissions('variation_orders'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contract_id' => [
                'required',
                Rule::exists('contracts', 'id')->where(fn ($query) => $query->where('is_archived', false)),
            ],
            'vo_number' => ['required', 'string', 'max:255', 'unique:variation_orders,vo_number'],
            'description' => ['nullable', 'string', 'max:5000'],
            'reason' => ['nullable', 'string', 'max:5000'],
            'amount_change' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'time_impact_days' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
        ]);

        $order = DB::transaction(function () use ($request, $validated) {
            $order = VariationOrder::create($validated + [
                'status' => 'Draft',
                'is_archived' => false,
            ]);

            AuditLogger::record(
                $request,
                'created',
                'variation_orders',
                $order->id,
                $order->vo_number,
                null,
                $order->toArray()
            );

            return $order;
        });

        return response()->json([
            'message' => 'Variation order created successfully.',
            'data' => $this->formatOrder($order->load(['contract', 'submitter'])),
        ], 201);
    }

    public function show(VariationOrder $order): JsonResponse
    {
        $order->load([
            'contract:id,contract_number,contract_title',
            'submitter:id,name',
            'reviewer:id,name',
            'approver:id,name',
            'documents.uploader:id,name',
        ]);

        return response()->json(['data' => $this->formatOrder($order)]);
    }

    public function update(Request $request, VariationOrder $order): JsonResponse
    {
        $request->request->remove('status');

        $validated = $request->validate([
            'contract_id' => [
                'sometimes',
                Rule::exists('contracts', 'id')->where(fn ($query) => $query->where('is_archived', false)),
            ],
            'vo_number' => [
                'sometimes', 'string', 'max:255',
                Rule::unique('variation_orders', 'vo_number')->ignore($order->id),
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'reason' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'amount_change' => ['sometimes', 'numeric', 'min:0', 'max:9999999999999.99'],
            'time_impact_days' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'approval_remarks' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);

        $oldValues = $order->toArray();

        DB::transaction(function () use ($request, $order, $validated, $oldValues) {
            $order->update($validated);

            AuditLogger::record(
                $request,
                'updated',
                'variation_orders',
                $order->id,
                $order->vo_number,
                $oldValues,
                $order->fresh()->toArray()
            );
        });

        return response()->json([
            'message' => 'Variation order updated successfully.',
            'data' => $this->formatOrder($order->fresh(['contract', 'submitter', 'reviewer', 'approver'])),
        ]);
    }

    public function submit(Request $request, VariationOrder $order): JsonResponse
    {
        abort_if($order->status !== 'Draft', 422, 'Only draft orders can be submitted.');

        $oldValues = $order->toArray();

        $order->update([
            'status' => 'Submitted',
            'submitted_by' => $request->user()->id,
            'submitted_at' => now(),
        ]);

        AuditLogger::record(
            $request,
            'submitted',
            'variation_orders',
            $order->id,
            $order->vo_number,
            $oldValues,
            $order->fresh()->toArray()
        );

        return response()->json([
            'message' => 'Variation order submitted successfully.',
            'data' => $this->formatOrder($order->fresh(['contract', 'submitter'])),
        ]);
    }

    public function review(Request $request, VariationOrder $order): JsonResponse
    {
        abort_if(!in_array($order->status, ['Submitted', 'Under Review']), 422, 'Order must be in Submitted status for review.');
        abort_unless($request->user()->hasModulePermission('variation_orders', 'approve'), 403);

        $validated = $request->validate([
            'review_action' => ['required', Rule::in(['Under Review', 'Approved', 'Rejected'])],
            'approval_remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        abort_if($validated['review_action'] === 'Approved' && $order->status === 'Approved', 422, 'This variation order has already been approved.');

        $oldValues = $order->toArray();

        DB::transaction(function () use ($request, $order, $validated, $oldValues) {
            $updatePayload = [
                'status' => $validated['review_action'],
                'reviewed_by' => $order->reviewed_by ?: $request->user()->id,
                'reviewed_at' => $order->reviewed_at ?: now(),
            ];

            if ($validated['review_action'] === 'Approved') {
                $updatePayload['approved_by'] = $request->user()->id;
                $updatePayload['approved_at'] = now();
            }

            $order->update($updatePayload);

            if ($validated['review_action'] === 'Approved') {
                $contract = $order->contract()->lockForUpdate()->first();
                $baseAmount = $contract->revised_contract_amount ?? $contract->original_contract_amount ?? 0;
                $contract->update([
                    'revised_contract_amount' => (float) $baseAmount + (float) $order->amount_change,
                ]);
            }

            AuditLogger::record(
                $request,
                $validated['review_action'] === 'Rejected' ? 'rejected' : 'reviewed',
                'variation_orders',
                $order->id,
                $order->vo_number,
                $oldValues,
                $order->fresh()->toArray()
            );
        });

        return response()->json([
            'message' => $validated['review_action'] === 'Rejected'
                ? 'Variation order rejected successfully.'
                : 'Variation order reviewed successfully.',
            'data' => $this->formatOrder($order->fresh(['contract', 'submitter', 'reviewer', 'approver'])),
        ]);
    }

    public function destroy(Request $request, VariationOrder $order): JsonResponse
    {
        $oldValues = $order->toArray();

        $order->update(['is_archived' => true]);
        $newValues = $order->toArray();

        AuditLogger::record(
            $request,
            'archived',
            'variation_orders',
            $order->id,
            $order->vo_number,
            $oldValues,
            $newValues
        );

        return response()->json(['message' => 'Variation order archived successfully.']);
    }

    public function uploadDocument(Request $request, VariationOrder $order): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,docx,xlsx,jpg,jpeg,png', 'max:25600'],
            'document_title' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $file = $request->file('file');
        $path = $file->store("variation-order-documents/{$order->id}", 'local');

        try {
            $document = DB::transaction(function () use ($request, $order, $file, $path, $validated) {
                return VariationOrderDocument::create([
                    'variation_order_id' => $order->id,
                    'document_title' => $validated['document_title'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
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
            'variation_order_documents',
            $document->id,
            $order->vo_number,
            null,
            $document->toArray()
        );

        return response()->json([
            'message' => 'Variation order document uploaded successfully.',
            'data' => $this->formatDocument($document->load('uploader:id,name')),
        ], 201);
    }

    public function downloadDocument(VariationOrderDocument $document): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($document->file_path), 404, 'Stored file was not found.');

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    private function formatOrder(VariationOrder $order): array
    {
        return [
            'id' => $order->id,
            'contract_id' => $order->contract_id,
            'contract_number' => $order->contract?->contract_number,
            'contract_title' => $order->contract?->contract_title,
            'vo_number' => $order->vo_number,
            'description' => $order->description,
            'reason' => $order->reason,
            'amount_change' => (float) $order->amount_change,
            'time_impact_days' => $order->time_impact_days,
            'status' => $order->status,
            'submitted_by' => $order->submitter?->name,
            'submitted_at' => optional($order->submitted_at)->format('Y-m-d H:i:s'),
            'reviewed_by' => $order->reviewer?->name,
            'reviewed_at' => optional($order->reviewed_at)->format('Y-m-d H:i:s'),
            'approved_by' => $order->approver?->name,
            'approved_at' => optional($order->approved_at)->format('Y-m-d H:i:s'),
            'approval_remarks' => $order->approval_remarks,
            'documents' => $order->relationLoaded('documents')
                ? $order->documents->map(fn (VariationOrderDocument $doc) => $this->formatDocument($doc))
                : [],
            'created_at' => optional($order->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($order->updated_at)->format('Y-m-d H:i:s'),
        ];
    }

    private function formatDocument(VariationOrderDocument $document): array
    {
        return [
            'id' => $document->id,
            'variation_order_id' => $document->variation_order_id,
            'document_title' => $document->document_title,
            'file_name' => $document->file_name,
            'file_type' => $document->file_type,
            'uploaded_by' => $document->uploader?->name,
            'uploaded_at' => optional($document->uploaded_at)->format('Y-m-d H:i:s'),
            'remarks' => $document->remarks,
        ];
    }

    private function calculateAverageApprovalDays($query): float
    {
        $approvedOrders = $query->where('status', 'Approved')
            ->whereNotNull('submitted_at')
            ->whereNotNull('approved_at')
            ->get(['submitted_at', 'approved_at']);

        if ($approvedOrders->isEmpty()) {
            return 0;
        }

        $totalDays = $approvedOrders->sum(function ($order) {
            return \Carbon\Carbon::parse($order->submitted_at)->diffInDays($order->approved_at);
        });

        return round($totalDays / $approvedOrders->count(), 1);
    }

    private function getMonthlyBreakdown($query): array
    {
        $monthlyData = [];
        $now = now();
        
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $month = $date->format('Y-m');
            $amount = (clone $query)
                ->whereNotNull('submitted_at')
                ->whereRaw("DATE_FORMAT(submitted_at, '%Y-%m') = ?", [$month])
                ->sum('amount_change');
            
            $monthlyData[] = [
                'month' => $date->format('M Y'),
                'amount' => (float) $amount,
            ];
        }

        $maxAmount = max(array_column($monthlyData, 'amount')) ?: 1;
        
        return array_map(function ($item) use ($maxAmount) {
            return [
                'month' => $item['month'],
                'amount' => $item['amount'],
                'percent' => $maxAmount > 0 ? round(($item['amount'] / $maxAmount) * 100) : 0,
            ];
        }, $monthlyData);
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
