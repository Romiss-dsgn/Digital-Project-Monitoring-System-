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
        $cashflowOfficer = User::query()
            ->where('position', 'Cashflow Monitoring Officer')
            ->first()
            ?? User::query()->orderBy('id')->first();

        $invoiceOfficer = User::query()
            ->where('position', 'Invoice Monitoring Officer')
            ->first()
            ?? $cashflowOfficer;

        $contracts = Contract::query()
            ->where('is_archived', false)
            ->orderBy('contract_number')
            ->take(4)
            ->get();

        foreach ($contracts as $contractIndex => $contract) {
            $baseStart = Carbon::parse($contract->start_date ?? now())->startOfMonth();
            $periodDefinitions = [
                [
                    'suffix' => '01',
                    'offset' => 0,
                    'planned_amount' => 150000 + ($contractIndex * 20000),
                    'status' => 'At Risk',
                    'invoice_plan' => [
                        [
                            'suffix' => 'A',
                            'amount' => 180000 + ($contractIndex * 10000),
                            'payment_amount' => 180000 + ($contractIndex * 10000),
                        ],
                        [
                            'suffix' => 'B',
                            'amount' => 95000 + ($contractIndex * 5000),
                            'payment_amount' => null,
                        ],
                    ],
                ],
                [
                    'suffix' => '02',
                    'offset' => 1,
                    'planned_amount' => 320000 + ($contractIndex * 25000),
                    'status' => 'Delayed',
                    'invoice_plan' => [
                        [
                            'suffix' => 'C',
                            'amount' => 260000 + ($contractIndex * 12000),
                            'payment_amount' => null,
                        ],
                    ],
                ],
                [
                    'suffix' => '03',
                    'offset' => 2,
                    'planned_amount' => 260000 + ($contractIndex * 15000),
                    'status' => 'On Track',
                    'invoice_plan' => [
                        [
                            'suffix' => 'D',
                            'amount' => 220000 + ($contractIndex * 8000),
                            'payment_amount' => 90000 + ($contractIndex * 5000),
                        ],
                    ],
                ],
                [
                    'suffix' => '04',
                    'offset' => 3,
                    'planned_amount' => 210000 + ($contractIndex * 10000),
                    'status' => 'On Track',
                    'invoice_plan' => [],
                ],
            ];

            foreach ($periodDefinitions as $definition) {
                $periodStart = $baseStart->copy()->addMonthsNoOverflow($definition['offset'])->startOfMonth();
                $periodEnd = $periodStart->copy()->endOfMonth();
                $periodLabel = sprintf('%s - %s', $contract->contract_number, $periodStart->format('Y-m'));

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

                foreach ($definition['invoice_plan'] as $invoiceIndex => $invoicePlan) {
                    $invoiceNumber = sprintf(
                        '%s-%s-%s',
                        $contract->contract_number,
                        $definition['suffix'],
                        $invoicePlan['suffix']
                    );

                    $invoiceDate = $periodStart->copy()->addDays(7 + ($invoiceIndex * 4));
                    $dueDate = $periodEnd->copy()->addDays(15 + ($invoiceIndex * 2));

                    $invoice = Invoice::updateOrCreate(
                        ['invoice_number' => $invoiceNumber],
                        [
                            'contract_id' => $contract->id,
                            'cashflow_period_id' => $period->id,
                            'billing_period' => $periodStart->format('F Y'),
                            'invoice_amount' => $invoicePlan['amount'],
                            'invoice_date' => $invoiceDate->toDateString(),
                            'due_date' => $dueDate->toDateString(),
                            'status' => $invoicePlan['payment_amount'] !== null && (float) $invoicePlan['payment_amount'] >= (float) $invoicePlan['amount']
                                ? 'Paid'
                                : 'Approved',
                            'remarks' => 'Seeded invoice for cashflow demo data.',
                            'created_by' => $invoiceOfficer?->id,
                        ]
                    );

                    if ($invoicePlan['payment_amount'] !== null) {
                        Payment::updateOrCreate(
                            ['payment_reference_no' => sprintf('%s-%s-PMT', $invoiceNumber, $definition['suffix'])],
                            [
                                'invoice_id' => $invoice->id,
                                'amount_paid' => $invoicePlan['payment_amount'],
                                'payment_date' => $invoiceDate->copy()->addDays(10)->toDateString(),
                                'payment_method' => $contractIndex % 2 === 0 ? 'Bank Transfer' : 'Check',
                                'payment_status' => 'Paid',
                                'recorded_by' => $invoiceOfficer?->id,
                                'remarks' => 'Seeded payment for cashflow demo data.',
                            ]
                        );
                    }
                }

                $period->syncFinancials();
            }
        }
    }
}
