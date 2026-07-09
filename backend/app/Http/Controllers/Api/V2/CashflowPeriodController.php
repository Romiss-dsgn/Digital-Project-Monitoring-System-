<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\CashflowPeriod;
use App\Models\Contract;
use App\Models\Invoice;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;

class CashflowPeriodController extends Controller
{
    private const STATUSES = ['On Track', 'At Risk', 'Delayed', 'Completed'];

    public function index(Request $request): JsonResponse
    {
        $query = CashflowPeriod::query()
            ->with([
                'contract:id,contract_number,contract_title',
                'creator:id,name',
            ])
            ->where('is_archived', false)
            ->whereHas('contract', fn (Builder $query) => $query->where('is_archived', false))
            ->when($request->string('search')->toString(), function (Builder $query, string $search) {
                $query->where(function (Builder $nested) use ($search) {
                    $nested->where('period_label', 'like', "%{$search}%")
                        ->orWhereHas('contract', fn (Builder $contract) => $contract
                            ->where('contract_number', 'like', "%{$search}%"));
                });
            })
            ->when($request->query('status'), fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($request->integer('contract_id'), fn (Builder $query, int $contractId) => $query->where('contract_id', $contractId))
            ->latest();

        $periods = $query->paginate($this->perPage($request));

        return response()->json([
            'data' => collect($periods->items())
                ->map(fn (CashflowPeriod $period) => $this->formatPeriod($period)),
            'meta' => $this->paginationMeta($periods),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $query = CashflowPeriod::query()
            ->where('is_archived', false)
            ->whereHas('contract', fn (Builder $query) => $query->where('is_archived', false));

        $totalPlanned = (clone $query)->sum('planned_amount');
        $totalActual = (clone $query)->sum('actual_amount');
        $revisedContractAmount = Contract::query()
            ->whereIn('id', (clone $query)->distinct()->pluck('contract_id'))
            ->sum('revised_contract_amount');
        $budgetStatus = $this->determineBudgetStatus((float) $totalPlanned, (float) $totalActual);

        $monthlyData = $this->getMonthlyBreakdown($query);

        return response()->json([
            'data' => [
                'planned_total' => (float) $totalPlanned,
                'actual_total' => (float) $totalActual,
                'revised_contract_amount' => (float) $revisedContractAmount,
                'remaining_total' => (float) ($totalPlanned - $totalActual),
                'variance_total' => (float) ((clone $query)->sum('variance')),
                'budget_status' => $budgetStatus,
                'on_track_count' => (clone $query)->where('status', 'On Track')->count(),
                'at_risk_count' => (clone $query)->where('status', 'At Risk')->count(),
                'delayed_count' => (clone $query)->where('status', 'Delayed')->count(),
                'completed_count' => (clone $query)->where('status', 'Completed')->count(),
                'monthly_breakdown' => $monthlyData,
                'permissions' => $request->user()->modulePermissions('cashflow_periods'),
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
            'permissions' => $request->user()->modulePermissions('cashflow_periods'),
        ]);
    }

    private function getMonthlyBreakdown($query): array
    {
        $monthlyData = [];
        $now = now();

        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $month = $date->format('Y-m');
            $planned = (clone $query)
                ->whereNotNull('period_start')
                ->whereRaw("DATE_FORMAT(period_start, '%Y-%m') = ?", [$month])
                ->sum('planned_amount');
            $actual = (clone $query)
                ->whereNotNull('period_start')
                ->whereRaw("DATE_FORMAT(period_start, '%Y-%m') = ?", [$month])
                ->sum('actual_amount');

            $monthlyData[] = [
                'month' => $date->format('M'),
                'planned' => (float) $planned,
                'actual' => (float) $actual,
            ];
        }

        return $monthlyData;
    }

    private function determineBudgetStatus(float $plannedAmount, float $actualAmount): string
    {
        $variance = $actualAmount - $plannedAmount;
        $tolerance = abs($plannedAmount) * 0.01;

        if (abs($variance) <= $tolerance) {
            return 'Within Budget';
        }

        return $variance > 0 ? 'Over Budget' : 'Under Budget';
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contract_id' => [
                'required',
                Rule::exists('contracts', 'id')->where(fn ($query) => $query->where('is_archived', false)),
            ],
            'period_label' => ['required', 'string', 'max:255'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
            'planned_amount' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
        ]);

        $period = DB::transaction(function () use ($request, $validated) {
            $period = CashflowPeriod::create($validated + [
                'created_by' => $request->user()->id,
                'status' => 'On Track',
                'is_archived' => false,
            ]);

            $period->syncFinancials();
            AuditLogger::record(
                $request,
                'created',
                'cashflow_periods',
                $period->id,
                $period->period_label,
                null,
                $period->toArray()
            );

            return $period;
        });

        return response()->json([
            'message' => 'Cashflow period created successfully.',
            'data' => $this->formatPeriod($period->fresh(['contract', 'creator'])),
        ], 201);
    }

    public function show(CashflowPeriod $period): JsonResponse
    {
        abort_if($period->is_archived, 404);
        $period->load(['contract:id,contract_number,contract_title', 'creator:id,name']);

        return response()->json(['data' => $this->formatPeriod($period)]);
    }

    public function update(Request $request, CashflowPeriod $period): JsonResponse
    {
        abort_if($period->is_archived, 404);

        $validated = $request->validate([
            'period_label' => ['sometimes', 'string', 'max:255'],
            'period_start' => ['sometimes', 'nullable', 'date'],
            'period_end' => ['sometimes', 'nullable', 'date', 'after_or_equal:period_start'],
            'planned_amount' => ['sometimes', 'numeric', 'min:0', 'max:9999999999999.99'],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
        ]);

        $oldValues = $period->toArray();

        DB::transaction(function () use ($request, $period, $validated, $oldValues) {
            $period->update($validated);
            $period->syncFinancials();

            AuditLogger::record(
                $request,
                'updated',
                'cashflow_periods',
                $period->id,
                $period->period_label,
                $oldValues,
                $period->fresh()->toArray()
            );
        });

        return response()->json([
            'message' => 'Cashflow period updated successfully.',
            'data' => $this->formatPeriod($period->fresh(['contract', 'creator'])),
        ]);
    }

    public function destroy(Request $request, CashflowPeriod $period): JsonResponse
    {
        abort_if($period->is_archived, 404);

        $oldValues = $period->toArray();

        DB::transaction(function () use ($request, $period, $oldValues) {
            // Archive instead of deleting records needed by reports and audit history.
            $period->update(['is_archived' => true]);

            AuditLogger::record(
                $request,
                'archived',
                'cashflow_periods',
                $period->id,
                $period->period_label,
                $oldValues,
                $period->fresh()->toArray()
            );
        });

        return response()->json(['message' => 'Cashflow period archived successfully.']);
    }

    public function getInvoices(CashflowPeriod $period): JsonResponse
    {
        abort_if($period->is_archived, 404);

        $invoices = Invoice::query()
            ->where('cashflow_period_id', $period->id)
            ->with(['contract:id,contract_number', 'verifier:id,name', 'approver:id,name', 'creator:id,name'])
            ->whereHas('contract', fn (Builder $query) => $query->where('is_archived', false))
            ->latest()
            ->get()
            ->map(fn (Invoice $invoice) => $this->formatInvoice($invoice));

        return response()->json(['data' => $invoices]);
    }

    private function formatPeriod(CashflowPeriod $period): array
    {
        $plannedAmount = (float) $period->planned_amount;
        $actualAmount = (float) $period->actual_amount;

        return [
            'id' => $period->id,
            'contract_id' => $period->contract_id,
            'contract_number' => $period->contract?->contract_number,
            'contract_title' => $period->contract?->contract_title,
            'period_label' => $period->period_label,
            'period_start' => optional($period->period_start)->format('Y-m-d'),
            'period_end' => optional($period->period_end)->format('Y-m-d'),
            'planned_amount' => $plannedAmount,
            'actual_amount' => $actualAmount,
            'variance' => (float) $period->variance,
            'budget_status' => $this->determineBudgetStatus($plannedAmount, $actualAmount),
            'status' => $period->status,
            'is_archived' => (bool) $period->is_archived,
            'created_by' => $period->creator?->name,
            'created_at' => optional($period->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($period->updated_at)->format('Y-m-d H:i:s'),
        ];
    }

    private function formatInvoice(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'contract_id' => $invoice->contract_id,
            'contract_number' => $invoice->contract?->contract_number,
            'invoice_number' => $invoice->invoice_number,
            'billing_period' => $invoice->billing_period,
            'invoice_amount' => (float) $invoice->invoice_amount,
            'invoice_date' => optional($invoice->invoice_date)->format('Y-m-d'),
            'due_date' => optional($invoice->due_date)->format('Y-m-d'),
            'status' => $invoice->status,
            'verified_by' => $invoice->verifier?->name,
            'verified_at' => optional($invoice->verified_at)->format('Y-m-d H:i:s'),
            'approved_by' => $invoice->approver?->name,
            'approved_at' => optional($invoice->approved_at)->format('Y-m-d H:i:s'),
            'remarks' => $invoice->remarks,
            'created_at' => optional($invoice->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($invoice->updated_at)->format('Y-m-d H:i:s'),
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
