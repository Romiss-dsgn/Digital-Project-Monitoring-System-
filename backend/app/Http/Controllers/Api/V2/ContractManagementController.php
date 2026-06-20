<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Contractor;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContractManagementController extends Controller
{
    public function index()
    {
        $contracts = Contract::query()
            ->with([
                'project:id,project_code,project_name,project_type',
                'contractor:id,company_name',
            ])
            ->where('is_archived', false)
            ->latest()
            ->get();

        return response()->json([
            'data' => $contracts->map(fn (Contract $contract) => $this->formatContract($contract)),
        ]);
    }

    public function options()
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
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedContract($request);

        $contract = Contract::create($validated + [
            'created_by' => $request->user()?->id,
            'revised_contract_amount' => $validated['original_contract_amount'],
            'duration_days' => $this->durationDays($validated['start_date'] ?? null, $validated['end_date'] ?? null),
            'is_archived' => false,
        ]);

        return response()->json([
            'message' => 'Contract created successfully.',
            'data' => $this->formatContract($contract->load(['project', 'contractor'])),
        ], 201);
    }

    public function show(Contract $contract)
    {
        return response()->json([
            'data' => $this->formatContract($contract->load(['project', 'contractor'])),
        ]);
    }

    public function update(Request $request, Contract $contract)
    {
        $validated = $this->validatedContract($request, $contract->id);

        $contract->update($validated + [
            'revised_contract_amount' => $validated['original_contract_amount'],
            'duration_days' => $this->durationDays($validated['start_date'] ?? null, $validated['end_date'] ?? null),
        ]);

        return response()->json([
            'message' => 'Contract updated successfully.',
            'data' => $this->formatContract($contract->fresh(['project', 'contractor'])),
        ]);
    }

    public function destroy(Contract $contract)
    {
        // Archive instead of hard-delete so related reports and audit trails remain usable.
        $contract->update(['is_archived' => true]);

        return response()->json([
            'message' => 'Contract archived successfully.',
        ]);
    }

    private function validatedContract(Request $request, ?int $contractId = null): array
    {
        return $request->validate([
            'contract_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('contracts', 'contract_number')->ignore($contractId),
            ],
            'contract_title' => ['required', 'string', 'max:255'],
            'project_id' => ['required', 'exists:projects,id'],
            'contractor_id' => ['required', 'exists:contractors,id'],
            'contract_type' => ['nullable', 'string', 'max:255'],
            'original_contract_amount' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);
    }

    private function formatContract(Contract $contract): array
    {
        return [
            'id' => $contract->id,
            'contract_number' => $contract->contract_number,
            'contract_title' => $contract->contract_title,
            'contract_type' => $contract->contract_type,
            'project_id' => $contract->project_id,
            'project_name' => $contract->project?->project_name,
            'project_type' => $contract->project?->project_type,
            'contractor_id' => $contract->contractor_id,
            'contractor_name' => $contract->contractor?->company_name,
            'original_contract_amount' => (float) $contract->original_contract_amount,
            'start_date' => optional($contract->start_date)->format('Y-m-d'),
            'end_date' => optional($contract->end_date)->format('Y-m-d'),
            'status' => $contract->status,
            'remarks' => $contract->remarks,
            'created_at' => optional($contract->created_at)->format('Y-m-d H:i:s'),
        ];
    }

    private function durationDays(?string $startDate, ?string $endDate): ?int
    {
        if (!$startDate || !$endDate) {
            return null;
        }

        return Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));
    }
}
