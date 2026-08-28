<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\VariationOrder;
use App\Models\VariationOrderDocument;
use App\Models\VariationOrderItem;
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
            ->withCount('items')
            ->with([
                'contract:id,contract_number,contract_title,project_id,contractor_id,original_contract_amount,revised_contract_amount',
                'contract.project:id,project_code,project_name,approved_budget',
                'contract.contractor:id,company_name',
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
                            ->orWhere('contract_title', 'like', "%{$search}%")
                            ->orWhereHas('project', fn (Builder $project) => $project
                                ->where('project_code', 'like', "%{$search}%")
                                ->orWhere('project_name', 'like', "%{$search}%"))
                            ->orWhereHas('contractor', fn (Builder $contractor) => $contractor
                                ->where('company_name', 'like', "%{$search}%")));
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
                ->with([
                    'project:id,project_code,project_name,approved_budget',
                    'contractor:id,company_name',
                ])
                ->orderBy('contract_number')
                ->get(['id', 'project_id', 'contractor_id', 'contract_number', 'contract_title', 'original_contract_amount', 'revised_contract_amount'])
                ->map(fn (Contract $contract) => [
                    'id' => $contract->id,
                    'contract_number' => $contract->contract_number,
                    'contract_title' => $contract->contract_title,
                    'project_name' => $contract->project?->project_name,
                    'project_ref' => $contract->project?->project_code,
                    'approved_budget_for_contract' => (float) ($contract->project?->approved_budget ?? 0),
                    'contractor_name' => $contract->contractor?->company_name,
                    'original_contract_amount' => (float) $contract->original_contract_amount,
                    'revised_contract_amount' => (float) $contract->revised_contract_amount,
                ])
                ->values(),
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
            'amount_change' => ['nullable', 'numeric', 'max:9999999999999.99'],
            'time_impact_days' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.line_number' => ['nullable', 'integer', 'min:1'],
            'items.*.item_description' => ['required_with:items', 'string', 'max:5000'],
            'items.*.original_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.original_unit' => ['nullable', 'string', 'max:255'],
            'items.*.original_unit_cost' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'items.*.additive_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.additive_unit' => ['nullable', 'string', 'max:255'],
            'items.*.additive_unit_cost' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'items.*.deductive_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.deductive_unit' => ['nullable', 'string', 'max:255'],
            'items.*.deductive_unit_cost' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'items.*.remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $order = DB::transaction(function () use ($request, $validated) {
            $items = $this->normalizeItems($validated['items'] ?? []);
            $this->ensureDeductiveTotalIsPresent($items, (float) ($validated['amount_change'] ?? 0));
            $amountChange = $items !== []
                ? $this->calculateWorksheetNetAmount($items)
                : (float) ($validated['amount_change'] ?? 0);

            $order = VariationOrder::create([
                'contract_id' => $validated['contract_id'],
                'vo_number' => $validated['vo_number'],
                'description' => $validated['description'] ?? null,
                'reason' => $validated['reason'] ?? null,
                'amount_change' => $amountChange,
                'time_impact_days' => $validated['time_impact_days'] ?? null,
                'status' => 'Draft',
                'is_archived' => false,
            ]);

            $this->syncItems($order, $items);

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
            'data' => $this->formatOrder($order->load(['contract.project', 'contract.contractor', 'submitter', 'items'])),
        ], 201);
    }

    public function show(VariationOrder $order): JsonResponse
    {
        $order->load([
            'contract:id,contract_number,contract_title,project_id,contractor_id,original_contract_amount,revised_contract_amount',
            'contract.project:id,project_code,project_name,approved_budget',
            'contract.contractor:id,company_name',
            'submitter:id,name',
            'reviewer:id,name',
            'approver:id,name',
            'items',
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
            'amount_change' => ['nullable', 'numeric', 'max:9999999999999.99'],
            'time_impact_days' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'approval_remarks' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.line_number' => ['nullable', 'integer', 'min:1'],
            'items.*.item_description' => ['required_with:items', 'string', 'max:5000'],
            'items.*.original_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.original_unit' => ['nullable', 'string', 'max:255'],
            'items.*.original_unit_cost' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'items.*.additive_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.additive_unit' => ['nullable', 'string', 'max:255'],
            'items.*.additive_unit_cost' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'items.*.deductive_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.deductive_unit' => ['nullable', 'string', 'max:255'],
            'items.*.deductive_unit_cost' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'items.*.remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldValues = $order->toArray();

        DB::transaction(function () use ($request, $order, $validated, $oldValues) {
            $itemsProvided = array_key_exists('items', $validated);
            $items = $itemsProvided ? $this->normalizeItems($validated['items'] ?? []) : null;
            if ($itemsProvided) {
                $this->ensureDeductiveTotalIsPresent($items ?? [], (float) ($validated['amount_change'] ?? $order->amount_change));
            }
            $amountChange = $itemsProvided
                ? ($items !== [] ? $this->calculateWorksheetNetAmount($items) : 0)
                : ($validated['amount_change'] ?? $order->amount_change);

            $order->update([
                'contract_id' => $validated['contract_id'] ?? $order->contract_id,
                'vo_number' => $validated['vo_number'] ?? $order->vo_number,
                'description' => array_key_exists('description', $validated) ? $validated['description'] : $order->description,
                'reason' => array_key_exists('reason', $validated) ? $validated['reason'] : $order->reason,
                'amount_change' => $amountChange,
                'time_impact_days' => array_key_exists('time_impact_days', $validated) ? $validated['time_impact_days'] : $order->time_impact_days,
                'approval_remarks' => array_key_exists('approval_remarks', $validated) ? $validated['approval_remarks'] : $order->approval_remarks,
            ]);

            if ($itemsProvided) {
                $this->syncItems($order, $items ?? []);
            }

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
            'data' => $this->formatOrder($order->fresh(['contract.project', 'contract.contractor', 'submitter', 'reviewer', 'approver', 'items'])),
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
            'data' => $this->formatOrder($order->fresh(['contract.project', 'contract.contractor', 'submitter', 'items'])),
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
            'data' => $this->formatOrder($order->fresh(['contract.project', 'contract.contractor', 'submitter', 'reviewer', 'approver', 'items'])),
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
        $originalAmount = (float) ($order->contract?->original_contract_amount ?? 0);
        $approvedBudget = (float) ($order->contract?->project?->approved_budget ?? 0);
        $currentRevisedAmount = (float) ($order->contract?->revised_contract_amount ?? $originalAmount);
        $items = $order->relationLoaded('items')
            ? $order->items->map(fn (VariationOrderItem $item) => $this->formatItem($item))->values()
            : collect();
        $totals = $this->summarizeWorksheetItems($items->all());
        $revisedAmount = in_array($order->status, ['Approved', 'Rejected', 'Archived'], true)
            ? $currentRevisedAmount
            : $currentRevisedAmount + ($items->isNotEmpty() ? $totals['net_amount'] : (float) $order->amount_change);

        return [
            'id' => $order->id,
            'contract_id' => $order->contract_id,
            'contract_number' => $order->contract?->contract_number,
            'contract_title' => $order->contract?->contract_title,
            'project_name' => $order->contract?->project?->project_name,
            'project_ref' => $order->contract?->project?->project_code,
            'contractor_name' => $order->contract?->contractor?->company_name,
            'approved_budget_for_contract' => $approvedBudget,
            'original_contract_amount' => $originalAmount,
            'vo_number' => $order->vo_number,
            'description' => $order->description,
            'reason' => $order->reason,
            'items_count' => $order->items_count ?? $items->count(),
            'items' => $items->all(),
            'amount_change' => $items->isNotEmpty() ? $totals['net_amount'] : (float) $order->amount_change,
            'additive_amount' => $items->isNotEmpty() ? $totals['additive_total'] : max((float) $order->amount_change, 0),
            'deductive_amount' => $items->isNotEmpty() ? $totals['deductive_total'] : max(0 - (float) $order->amount_change, 0),
            'revised_contract_amount' => $revisedAmount,
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

    private function formatItem(VariationOrderItem $item): array
    {
        return [
            'id' => $item->id,
            'line_number' => $item->line_number,
            'item_description' => $item->item_description,
            'original_qty' => (float) $item->original_qty,
            'original_unit' => $item->original_unit,
            'original_unit_cost' => (float) $item->original_unit_cost,
            'original_total_cost' => (float) $item->original_total_cost,
            'additive_qty' => (float) $item->additive_qty,
            'additive_unit' => $item->additive_unit,
            'additive_unit_cost' => (float) $item->additive_unit_cost,
            'additive_total_cost' => (float) $item->additive_total_cost,
            'deductive_qty' => (float) $item->deductive_qty,
            'deductive_unit' => $item->deductive_unit,
            'deductive_unit_cost' => (float) $item->deductive_unit_cost,
            'deductive_total_cost' => (float) $item->deductive_total_cost,
            'net_line_cost' => (float) $item->net_line_cost,
            'remarks' => $item->remarks,
        ];
    }

    private function normalizeItems(array $items): array
    {
        return collect($items)
            ->filter(fn (array $item) => $this->itemHasData($item))
            ->values()
            ->map(function (array $item, int $index) {
                $originalQty = (float) ($item['original_qty'] ?? 0);
                $originalUnitCost = (float) ($item['original_unit_cost'] ?? 0);
                $additiveQty = (float) ($item['additive_qty'] ?? 0);
                $additiveUnitCost = (float) ($item['additive_unit_cost'] ?? 0);
                $deductiveQty = (float) ($item['deductive_qty'] ?? 0);
                $deductiveUnitCost = (float) ($item['deductive_unit_cost'] ?? 0);

                $originalTotal = round($originalQty * $originalUnitCost, 2);
                $additiveTotal = round($additiveQty * $additiveUnitCost, 2);
                $deductiveTotal = round($deductiveQty * $deductiveUnitCost, 2);

                return [
                    'line_number' => (int) ($item['line_number'] ?? ($index + 1)),
                    'item_description' => trim((string) ($item['item_description'] ?? '')),
                    'original_qty' => $originalQty,
                    'original_unit' => $item['original_unit'] ?? null,
                    'original_unit_cost' => $originalUnitCost,
                    'original_total_cost' => $originalTotal,
                    'additive_qty' => $additiveQty,
                    'additive_unit' => $item['additive_unit'] ?? null,
                    'additive_unit_cost' => $additiveUnitCost,
                    'additive_total_cost' => $additiveTotal,
                    'deductive_qty' => $deductiveQty,
                    'deductive_unit' => $item['deductive_unit'] ?? null,
                    'deductive_unit_cost' => $deductiveUnitCost,
                    'deductive_total_cost' => $deductiveTotal,
                    'net_line_cost' => round($additiveTotal - $deductiveTotal, 2),
                    'remarks' => $item['remarks'] ?? null,
                ];
            })
            ->all();
    }

    private function itemHasData(array $item): bool
    {
        $description = trim((string) ($item['item_description'] ?? ''));

        return $description !== ''
            || (float) ($item['original_qty'] ?? 0) != 0.0
            || (float) ($item['additive_qty'] ?? 0) != 0.0
            || (float) ($item['deductive_qty'] ?? 0) != 0.0
            || (float) ($item['original_unit_cost'] ?? 0) != 0.0
            || (float) ($item['additive_unit_cost'] ?? 0) != 0.0
            || (float) ($item['deductive_unit_cost'] ?? 0) != 0.0;
    }

    private function summarizeWorksheetItems(array $items): array
    {
        $additiveTotal = collect($items)->sum(fn (array $item) => (float) ($item['additive_total_cost'] ?? 0));
        $deductiveTotal = collect($items)->sum(fn (array $item) => (float) ($item['deductive_total_cost'] ?? 0));

        return [
            'additive_total' => round($additiveTotal, 2),
            'deductive_total' => round($deductiveTotal, 2),
            'net_amount' => round($additiveTotal - $deductiveTotal, 2),
        ];
    }

    private function calculateWorksheetNetAmount(array $items): float
    {
        return $this->summarizeWorksheetItems($items)['net_amount'];
    }

    private function ensureDeductiveTotalIsPresent(array $items, float $amountChange): void
    {
        $deductiveTotal = $items !== []
            ? $this->summarizeWorksheetItems($items)['deductive_total']
            : max(0 - $amountChange, 0);

        abort_if($deductiveTotal <= 0, 422, 'Deductive amount must be greater than zero.');
    }

    private function syncItems(VariationOrder $order, array $items): void
    {
        $order->items()->delete();

        if ($items === []) {
            return;
        }

        foreach ($items as $item) {
            VariationOrderItem::create([
                'variation_order_id' => $order->id,
                'line_number' => $item['line_number'] ?? null,
                'item_description' => $item['item_description'],
                'original_qty' => $item['original_qty'],
                'original_unit' => $item['original_unit'],
                'original_unit_cost' => $item['original_unit_cost'],
                'original_total_cost' => $item['original_total_cost'],
                'additive_qty' => $item['additive_qty'],
                'additive_unit' => $item['additive_unit'],
                'additive_unit_cost' => $item['additive_unit_cost'],
                'additive_total_cost' => $item['additive_total_cost'],
                'deductive_qty' => $item['deductive_qty'],
                'deductive_unit' => $item['deductive_unit'],
                'deductive_unit_cost' => $item['deductive_unit_cost'],
                'deductive_total_cost' => $item['deductive_total_cost'],
                'net_line_cost' => $item['net_line_cost'],
                'remarks' => $item['remarks'] ?? null,
            ]);
        }
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
