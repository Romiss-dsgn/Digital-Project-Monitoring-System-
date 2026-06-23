<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\Contractor;
use App\Models\Project;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ContractManagementController extends Controller
{
    private const STATUSES = [
        'Draft',
        'Pending Review',
        'Active',
        'Delayed',
        'Completed',
        'Rejected',
        'Terminated',
        'Expired',
    ];

    public function index(Request $request): JsonResponse
    {
        $query = Contract::query()
            ->with([
                'project:id,project_code,project_name,project_type,location',
                'contractor:id,company_name',
            ])
            ->withCount([
                'documents as document_count' => fn (Builder $query) => $query->where('is_archived', false),
                'documents as approved_document_count' => fn (Builder $query) => $query
                    ->where('is_archived', false)
                    ->where('status', 'Approved'),
            ])
            ->where('is_archived', false)
            ->when($request->string('search')->toString(), function (Builder $query, string $search) {
                $query->where(function (Builder $nested) use ($search) {
                    $nested->where('contract_number', 'like', "%{$search}%")
                        ->orWhere('contract_title', 'like', "%{$search}%")
                        ->orWhereHas('project', fn (Builder $project) => $project
                            ->where('project_name', 'like', "%{$search}%"))
                        ->orWhereHas('contractor', fn (Builder $contractor) => $contractor
                            ->where('company_name', 'like', "%{$search}%"));
                });
            })
            ->when($request->query('status'), fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($request->integer('project_id'), fn (Builder $query, int $projectId) => $query->where('project_id', $projectId))
            ->when($request->integer('contractor_id'), fn (Builder $query, int $contractorId) => $query->where('contractor_id', $contractorId))
            ->when($request->query('start_from'), fn (Builder $query, string $date) => $query->whereDate('start_date', '>=', $date))
            ->when($request->query('start_to'), fn (Builder $query, string $date) => $query->whereDate('start_date', '<=', $date))
            ->latest();

        $contracts = $query->paginate($this->perPage($request));

        return response()->json([
            'data' => collect($contracts->items())
                ->map(fn (Contract $contract) => $this->formatContract($contract)),
            'meta' => $this->paginationMeta($contracts),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $contracts = Contract::query()->where('is_archived', false);
        $documents = ContractDocument::query()
            ->where('is_archived', false)
            ->whereHas('contract', fn (Builder $query) => $query->where('is_archived', false));

        $totalDocuments = (clone $documents)->count();
        $approvedDocuments = (clone $documents)->where('status', 'Approved')->count();
        $latestStartDate = (clone $contracts)->max('start_date');

        $recentDocuments = (clone $documents)
            ->with(['contract:id,contract_number,contract_title', 'uploader:id,name'])
            ->latest('uploaded_at')
            ->limit(5)
            ->get()
            ->map(fn (ContractDocument $document) => $this->formatDocument($document));

        return response()->json([
            'data' => [
                'ongoing_projects' => Project::query()
                    ->where('is_archived', false)
                    ->where('status', 'Ongoing')
                    ->count(),
                'active_contracts' => (clone $contracts)->where('status', 'Active')->count(),
                'pending_review' => (clone $contracts)->where('status', 'Pending Review')->count(),
                'total_value' => (float) (clone $contracts)->sum('revised_contract_amount'),
                'docs_compliance' => $totalDocuments > 0
                    ? round(($approvedDocuments / $totalDocuments) * 100, 1)
                    : 0,
                'approved_documents' => $approvedDocuments,
                'total_documents' => $totalDocuments,
                'fiscal_year' => $latestStartDate ? Carbon::parse($latestStartDate)->year : now()->year,
                'recent_documents' => $recentDocuments,
                'permissions' => $request->user()->modulePermissions('contracts'),
            ],
        ]);
    }

    public function options(Request $request): JsonResponse
    {
        return response()->json([
            'projects' => Project::query()
                ->where('is_archived', false)
                ->orderBy('project_name')
                ->get(['id', 'project_code', 'project_name']),
            'contractors' => Contractor::query()
                ->where('is_active', true)
                ->orderBy('company_name')
                ->get(['id', 'company_name']),
            'statuses' => self::STATUSES,
            'permissions' => $request->user()->modulePermissions('contracts'),
            'document_permissions' => $request->user()->modulePermissions('contract_documents'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatedContract($request);

        if (! in_array($validated['status'], ['Draft', 'Pending Review'], true)) {
            abort_unless($request->user()->hasModulePermission('contracts', 'approve'), 403);
        }

        $contract = DB::transaction(function () use ($request, $validated) {
            $contract = Contract::create($validated + [
                'created_by' => $request->user()->id,
                'revised_contract_amount' => $validated['revised_contract_amount']
                    ?? $validated['original_contract_amount'],
                'duration_days' => $this->durationDays($validated['start_date'] ?? null, $validated['end_date'] ?? null),
                'is_archived' => false,
            ]);

            AuditLogger::record(
                $request,
                'created',
                'contracts',
                $contract->id,
                $contract->contract_number,
                null,
                $contract->toArray()
            );

            return $contract;
        });

        return response()->json([
            'message' => 'Contract created successfully.',
            'data' => $this->formatContract($contract->load(['project', 'contractor'])),
        ], 201);
    }

    public function show(Contract $contract): JsonResponse
    {
        abort_if($contract->is_archived, 404);

        $contract->load([
            'project',
            'contractor',
            'documents' => fn ($query) => $query
                ->where('is_archived', false)
                ->with('uploader:id,name')
                ->latest('uploaded_at'),
        ])->loadCount([
            'documents as document_count' => fn (Builder $query) => $query->where('is_archived', false),
            'documents as approved_document_count' => fn (Builder $query) => $query
                ->where('is_archived', false)
                ->where('status', 'Approved'),
        ]);

        $data = $this->formatContract($contract);
        $data['documents'] = $contract->documents
            ->map(fn (ContractDocument $document) => $this->formatDocument($document));

        return response()->json(['data' => $data]);
    }

    public function update(Request $request, Contract $contract): JsonResponse
    {
        abort_if($contract->is_archived, 404);

        $validated = $this->validatedContract($request, $contract->id);

        if ($contract->status !== $validated['status']) {
            abort_unless($request->user()->hasModulePermission('contracts', 'approve'), 403);
        }

        $oldValues = $contract->toArray();

        DB::transaction(function () use ($request, $contract, $validated, $oldValues) {
            $contract->update($validated + [
                'revised_contract_amount' => $validated['revised_contract_amount']
                    ?? $contract->revised_contract_amount,
                'duration_days' => $this->durationDays($validated['start_date'] ?? null, $validated['end_date'] ?? null),
                'approved_by' => $validated['status'] === 'Active' ? $request->user()->id : $contract->approved_by,
                'approved_at' => $validated['status'] === 'Active' ? now() : $contract->approved_at,
            ]);

            AuditLogger::record(
                $request,
                'updated',
                'contracts',
                $contract->id,
                $contract->contract_number,
                $oldValues,
                $contract->fresh()->toArray()
            );
        });

        return response()->json([
            'message' => 'Contract updated successfully.',
            'data' => $this->formatContract($contract->fresh(['project', 'contractor'])),
        ]);
    }

    public function destroy(Request $request, Contract $contract): JsonResponse
    {
        abort_if($contract->is_archived, 404);
        $oldValues = $contract->toArray();

        DB::transaction(function () use ($request, $contract, $oldValues) {
            // Archive instead of deleting records needed by reports and audit history.
            $contract->update(['is_archived' => true]);

            AuditLogger::record(
                $request,
                'archived',
                'contracts',
                $contract->id,
                $contract->contract_number,
                $oldValues,
                $contract->fresh()->toArray()
            );
        });

        return response()->json(['message' => 'Contract archived successfully.']);
    }

    public function uploadDocuments(Request $request, Contract $contract): JsonResponse
    {
        abort_if($contract->is_archived, 404);

        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['required', 'file', 'mimes:pdf,docx,xlsx', 'max:25600'],
            'document_category' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $storedPaths = [];

        try {
            $documents = DB::transaction(function () use ($request, $contract, $validated, &$storedPaths) {
                return collect($request->file('files'))->map(function ($file) use ($request, $contract, $validated, &$storedPaths) {
                    $path = $file->store("contract-documents/{$contract->id}", 'local');
                    $storedPaths[] = $path;

                    $document = ContractDocument::create([
                        'contract_id' => $contract->id,
                        'document_title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                        'document_category' => $validated['document_category'] ?? 'Contract Document',
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'version' => '1.0',
                        'status' => 'Uploaded',
                        'uploaded_by' => $request->user()->id,
                        'uploaded_at' => now(),
                        'remarks' => $validated['remarks'] ?? null,
                        'is_archived' => false,
                    ]);

                    AuditLogger::record(
                        $request,
                        'uploaded',
                        'contract_documents',
                        $document->id,
                        $contract->contract_number,
                        null,
                        $document->toArray()
                    );

                    return $this->formatDocument($document->load('uploader:id,name'));
                });
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        return response()->json([
            'message' => 'Contract documents uploaded successfully.',
            'data' => $documents,
        ], 201);
    }

    public function updateDocumentStatus(Request $request, ContractDocument $document): JsonResponse
    {
        abort_if($document->is_archived, 404);
        $validated = $request->validate([
            'status' => ['required', Rule::in(['Uploaded', 'Under Review', 'Approved', 'Rejected'])],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);
        $oldValues = $document->toArray();

        $document->update($validated);
        AuditLogger::record(
            $request,
            'reviewed',
            'contract_documents',
            $document->id,
            $document->contract?->contract_number,
            $oldValues,
            $document->fresh()->toArray()
        );

        return response()->json([
            'message' => 'Document status updated.',
            'data' => $this->formatDocument($document->fresh(['uploader:id,name', 'contract:id,contract_number'])),
        ]);
    }

    public function destroyDocument(Request $request, ContractDocument $document): JsonResponse
    {
        abort_if($document->is_archived, 404);
        $oldValues = $document->toArray();
        $document->update(['is_archived' => true]);

        AuditLogger::record(
            $request,
            'archived',
            'contract_documents',
            $document->id,
            $document->contract?->contract_number,
            $oldValues,
            $document->fresh()->toArray()
        );

        return response()->json(['message' => 'Document archived successfully.']);
    }

    public function downloadDocument(ContractDocument $document): StreamedResponse
    {
        abort_if($document->is_archived, 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404, 'Stored file was not found.');

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    private function validatedContract(Request $request, ?int $contractId = null): array
    {
        $validated = $request->validate([
            'contract_number' => [
                'required',
                'string',
                'max:255',
                'regex:/^BFP-R2-CON-\d{4}-\d{3,}$/i',
                Rule::unique('contracts', 'contract_number')->ignore($contractId),
            ],
            'contract_title' => ['required', 'string', 'max:255'],
            'project_id' => [
                'required',
                Rule::exists('projects', 'id')->where(fn ($query) => $query->where('is_archived', false)),
            ],
            'contractor_id' => [
                'required',
                Rule::exists('contractors', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            // These fields are nullable in the schema, so the API accepts drafts with partial details.
            'contract_type' => ['nullable', 'string', 'max:100'],
            'original_contract_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'revised_contract_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(self::STATUSES)],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ], [
            'contract_number.regex' => 'Use the format BFP-R2-CON-YYYY-NNN.',
        ]);

        // Keep stored records consistent even when the frontend is saving an early draft.
        $validated['contract_number'] = strtoupper($validated['contract_number']);
        $validated['contract_type'] = $validated['contract_type'] ?: 'Infrastructure Works';
        $validated['original_contract_amount'] = $validated['original_contract_amount'] ?? 0;
        $validated['revised_contract_amount'] = $validated['revised_contract_amount']
            ?? $validated['original_contract_amount'];

        return $validated;
    }

    private function formatContract(Contract $contract): array
    {
        $documentCount = (int) ($contract->document_count ?? 0);
        $approvedCount = (int) ($contract->approved_document_count ?? 0);

        return [
            'id' => $contract->id,
            'contract_number' => $contract->contract_number,
            'contract_title' => $contract->contract_title,
            'contract_type' => $contract->contract_type,
            'project_id' => $contract->project_id,
            'project_code' => $contract->project?->project_code,
            'project_name' => $contract->project?->project_name,
            'project_type' => $contract->project?->project_type,
            'project_location' => $contract->project?->location,
            'contractor_id' => $contract->contractor_id,
            'contractor_name' => $contract->contractor?->company_name,
            'original_contract_amount' => (float) $contract->original_contract_amount,
            'revised_contract_amount' => (float) $contract->revised_contract_amount,
            'start_date' => optional($contract->start_date)->format('Y-m-d'),
            'end_date' => optional($contract->end_date)->format('Y-m-d'),
            'duration_days' => $contract->duration_days,
            'status' => $contract->status,
            'remarks' => $contract->remarks,
            'document_count' => $documentCount,
            'approved_document_count' => $approvedCount,
            'document_compliance' => $documentCount > 0 ? round(($approvedCount / $documentCount) * 100, 1) : 0,
            'created_at' => optional($contract->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($contract->updated_at)->format('Y-m-d H:i:s'),
        ];
    }

    private function formatDocument(ContractDocument $document): array
    {
        return [
            'id' => $document->id,
            'contract_id' => $document->contract_id,
            'contract_number' => $document->contract?->contract_number,
            'contract_title' => $document->contract?->contract_title,
            'document_title' => $document->document_title,
            'document_category' => $document->document_category,
            'file_name' => $document->file_name,
            'file_type' => $document->file_type,
            'version' => $document->version,
            'status' => $document->status,
            'uploaded_by' => $document->uploader?->name,
            'uploaded_at' => optional($document->uploaded_at)->format('Y-m-d H:i:s'),
            'remarks' => $document->remarks,
        ];
    }

    private function durationDays(?string $startDate, ?string $endDate): ?int
    {
        if (! $startDate || ! $endDate) {
            return null;
        }

        return Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));
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
