<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\User;
use App\Models\VariationOrder;
use App\Models\VariationOrderItem;
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
                'LGU-TUAO-CON-2026-001',
                'LGU-TUAO-CON-2026-002',
                'LGU-TUAO-CON-2026-003',
            ])
            ->where('is_archived', false)
            ->orderBy('contract_number')
            ->get()
            ->keyBy('contract_number');

        $orders = [
            [
                'vo_number' => 'VO-QA-2026-001',
                'contract_number' => 'LGU-TUAO-CON-2026-001',
                'description' => 'Approved records-room material upgrade for weather-resilience requirements.',
                'reason' => 'Material specification update',
                'amount_change' => 320000,
                'time_impact_days' => 10,
                'status' => 'Approved',
                'submitted_at' => Carbon::now()->subDays(18),
                'reviewed_at' => Carbon::now()->subDays(14),
                'approved_at' => Carbon::now()->subDays(12),
                'approval_remarks' => 'Approved for QA financial impact testing.',
                'items' => [
                    [
                        'line_number' => 1,
                        'item_description' => 'Weather-resistant records room upgrade',
                        'additive_qty' => 1,
                        'additive_unit' => 'lot',
                        'additive_unit_cost' => 320000,
                    ],
                ],
            ],
            [
                'vo_number' => 'VO-QA-2026-002',
                'contract_number' => 'LGU-TUAO-CON-2026-002',
                'description' => 'Drainage alignment adjustment for public market site conditions.',
                'reason' => 'Scope refinement',
                'amount_change' => 180000,
                'time_impact_days' => 5,
                'status' => 'Under Review',
                'submitted_at' => Carbon::now()->subDays(8),
                'reviewed_at' => Carbon::now()->subDays(3),
                'items' => [
                    [
                        'line_number' => 1,
                        'item_description' => 'Drainage alignment and excavation adjustment',
                        'additive_qty' => 1,
                        'additive_unit' => 'lot',
                        'additive_unit_cost' => 180000,
                    ],
                ],
            ],
            [
                'vo_number' => 'VO-QA-2026-003',
                'contract_number' => 'LGU-TUAO-CON-2026-003',
                'description' => 'Draft drainage adjustment pending technical validation.',
                'reason' => 'Site condition adjustment',
                'amount_change' => 95000,
                'time_impact_days' => 3,
                'status' => 'Draft',
                'submitted_at' => null,
                'items' => [
                    [
                        'line_number' => 1,
                        'item_description' => 'Pending drainage adjustment for site condition review',
                        'additive_qty' => 1,
                        'additive_unit' => 'lot',
                        'additive_unit_cost' => 95000,
                    ],
                ],
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

            $variationOrder = VariationOrder::where('vo_number', $order['vo_number'])->first();

            if ($variationOrder) {
                VariationOrderItem::where('variation_order_id', $variationOrder->id)->delete();

                foreach ($order['items'] as $itemIndex => $item) {
                    $additiveTotal = (($item['additive_qty'] ?? 0) * ($item['additive_unit_cost'] ?? 0));
                    $deductiveTotal = (($item['deductive_qty'] ?? 0) * ($item['deductive_unit_cost'] ?? 0));

                    VariationOrderItem::create([
                        'variation_order_id' => $variationOrder->id,
                        'line_number' => $item['line_number'] ?? ($itemIndex + 1),
                        'item_description' => $item['item_description'],
                        'original_qty' => $item['original_qty'] ?? 0,
                        'original_unit' => $item['original_unit'] ?? null,
                        'original_unit_cost' => $item['original_unit_cost'] ?? 0,
                        'original_total_cost' => (($item['original_qty'] ?? 0) * ($item['original_unit_cost'] ?? 0)),
                        'additive_qty' => $item['additive_qty'] ?? 0,
                        'additive_unit' => $item['additive_unit'] ?? null,
                        'additive_unit_cost' => $item['additive_unit_cost'] ?? 0,
                        'additive_total_cost' => $additiveTotal,
                        'deductive_qty' => $item['deductive_qty'] ?? 0,
                        'deductive_unit' => $item['deductive_unit'] ?? null,
                        'deductive_unit_cost' => $item['deductive_unit_cost'] ?? 0,
                        'deductive_total_cost' => $deductiveTotal,
                        'net_line_cost' => $additiveTotal - $deductiveTotal,
                        'remarks' => $item['remarks'] ?? null,
                    ]);
                }
            }
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
