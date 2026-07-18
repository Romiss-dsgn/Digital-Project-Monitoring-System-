<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\User;
use App\Models\VariationOrder;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class VariationOrdersSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@contrackpro.test')->first()
            ?? User::query()->orderBy('id')->first();

        $contracts = Contract::query()
            ->whereIn('contract_number', [
                'BFP-R2-CON-2026-001',
                'BFP-R2-CON-2026-002',
                'BFP-R2-CON-2026-003',
            ])
            ->where('is_archived', false)
            ->orderBy('contract_number')
            ->get()
            ->keyBy('contract_number');

        $orders = [
            [
                'vo_number' => 'VO-QA-2026-001',
                'contract_number' => 'BFP-R2-CON-2026-001',
                'description' => 'Approved roofing material upgrade for weather-resilience requirements.',
                'reason' => 'Material specification update',
                'amount_change' => 320000,
                'time_impact_days' => 10,
                'status' => 'Approved',
                'submitted_at' => Carbon::now()->subDays(18),
                'reviewed_at' => Carbon::now()->subDays(14),
                'approved_at' => Carbon::now()->subDays(12),
                'approval_remarks' => 'Approved for QA financial impact testing.',
            ],
            [
                'vo_number' => 'VO-QA-2026-002',
                'contract_number' => 'BFP-R2-CON-2026-002',
                'description' => 'Electrical load capacity adjustment for records room equipment.',
                'reason' => 'Scope refinement',
                'amount_change' => 180000,
                'time_impact_days' => 5,
                'status' => 'Under Review',
                'submitted_at' => Carbon::now()->subDays(8),
                'reviewed_at' => Carbon::now()->subDays(3),
            ],
            [
                'vo_number' => 'VO-QA-2026-003',
                'contract_number' => 'BFP-R2-CON-2026-003',
                'description' => 'Draft drainage adjustment pending technical validation.',
                'reason' => 'Site condition adjustment',
                'amount_change' => 95000,
                'time_impact_days' => 3,
                'status' => 'Draft',
                'submitted_at' => null,
            ],
        ];

        foreach ($orders as $order) {
            $contract = $contracts->get($order['contract_number']);

            if (! $contract) {
                continue;
            }

            VariationOrder::updateOrCreate(
                ['vo_number' => $order['vo_number']],
                [
                    'contract_id' => $contract->id,
                    'description' => $order['description'],
                    'reason' => $order['reason'],
                    'amount_change' => $order['amount_change'],
                    'time_impact_days' => $order['time_impact_days'],
                    'status' => $order['status'],
                    'submitted_by' => $order['submitted_at'] ? $admin?->id : null,
                    'submitted_at' => $order['submitted_at'],
                    'reviewed_by' => !empty($order['reviewed_at']) ? $admin?->id : null,
                    'reviewed_at' => $order['reviewed_at'] ?? null,
                    'approved_by' => !empty($order['approved_at']) ? $admin?->id : null,
                    'approved_at' => $order['approved_at'] ?? null,
                    'approval_remarks' => $order['approval_remarks'] ?? null,
                    'is_archived' => false,
                ]
            );
        }

        foreach ($contracts as $contract) {
            $approvedDelta = VariationOrder::query()
                ->where('contract_id', $contract->id)
                ->where('is_archived', false)
                ->where('status', 'Approved')
                ->sum('amount_change');

            $contract->update([
                'revised_contract_amount' => (float) $contract->original_contract_amount + (float) $approvedDelta,
            ]);
        }
    }
}
