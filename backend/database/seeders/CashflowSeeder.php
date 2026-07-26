<?php

namespace Database\Seeders;

use App\Models\CashflowPeriod;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CashflowSeeder extends Seeder
{
    public function run(): void
    {
        $cashflowOfficer = User::where('email', 'qa.monitor@contrackpro.test')->first()
            ?? User::where('email', 'admin@contrackpro.test')->first();

        $contracts = Contract::query()
            ->whereIn('contract_number', [
                'LGU-TUAO-CON-2026-001',
                'LGU-TUAO-CON-2026-002',
                'LGU-TUAO-CON-2026-003',
            ])
            ->where('is_archived', false)
            ->orderBy('contract_number')
            ->get();

        $periods = [
            [
                'contract_number' => 'LGU-TUAO-CON-2026-001',
                'label_suffix' => 'Mobilization',
                'planned_amount' => 1250000,
                'status' => 'Completed',
                'invoice_number' => 'INV-QA-2026-001',
                'invoice_amount' => 1250000,
                'invoice_status' => 'Paid',
                'payment_amount' => 1250000,
            ],
            [
                'contract_number' => 'LGU-TUAO-CON-2026-002',
                'label_suffix' => 'Progress Billing 1',
                'planned_amount' => 680000,
                'status' => 'At Risk',
                'invoice_number' => 'INV-QA-2026-002',
                'invoice_amount' => 680000,
                'invoice_status' => 'Approved',
                'payment_amount' => null,
            ],
            [
                'contract_number' => 'LGU-TUAO-CON-2026-003',
                'label_suffix' => 'Advance Review',
                'planned_amount' => 420000,
                'status' => 'On Track',
                'invoice_number' => 'INV-QA-2026-003',
                'invoice_amount' => 420000,
                'invoice_status' => 'Pending',
                'payment_amount' => null,
            ],
        ];

        $contractsByNumber = $contracts->keyBy('contract_number');

        foreach ($periods as $index => $definition) {
            $contract = $contractsByNumber->get($definition['contract_number']);

            if (! $contract) {
                continue;
            }

            $periodStart = Carbon::parse($contract->start_date ?? now())
                ->addMonthsNoOverflow($index)
                ->startOfMonth();
            $periodEnd = $periodStart->copy()->endOfMonth();
            $periodLabel = "{$contract->contract_number} - {$definition['label_suffix']}";

            $period = CashflowPeriod::updateOrCreate(
                [
                    'contract_id' => $contract->id,
                    'period_label' => $periodLabel,
                ],
                [
                    'period_start' => $periodStart->toDateString(),
                    'period_end' => $periodEnd->toDateString(),
                    'planned_amount' => $definition['planned_amount'],
                    'actual_amount' => 0,
                    'variance' => 0,
                    'status' => $definition['status'],
                    'created_by' => $cashflowOfficer?->id,
                    'is_archived' => false,
                ]
            );

            $invoice = Invoice::updateOrCreate(
                ['invoice_number' => $definition['invoice_number']],
                [
                    'contract_id' => $contract->id,
                    'cashflow_period_id' => $period->id,
                    'billing_period' => $periodStart->format('F Y'),
                    'invoice_amount' => $definition['invoice_amount'],
                    'invoice_date' => $periodStart->copy()->addDays(7)->toDateString(),
                    'due_date' => $periodEnd->copy()->addDays(15)->toDateString(),
                    'status' => $definition['invoice_status'],
                    'remarks' => 'QA seed invoice. Upload supporting files during E2E tests.',
                    'created_by' => $cashflowOfficer?->id,
                ]
            );

            $paymentReference = "{$definition['invoice_number']}-PMT";

            if ($definition['payment_amount'] !== null) {
                Payment::updateOrCreate(
                    ['payment_reference_no' => $paymentReference],
                    [
                        'invoice_id' => $invoice->id,
                        'amount_paid' => $definition['payment_amount'],
                        'payment_date' => $periodStart->copy()->addDays(20)->toDateString(),
                        'payment_method' => 'Bank Transfer',
                        'payment_status' => 'Paid',
                        'recorded_by' => $cashflowOfficer?->id,
                        'remarks' => 'QA seed payment for paid invoice scenario.',
                    ]
                );
            } else {
                Payment::where('payment_reference_no', $paymentReference)->delete();
            }

            $period->syncFinancials();
        }
    }
}
