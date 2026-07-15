<?php

namespace App\Http\Controllers\Api\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CashflowPeriod;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectAccomplishment;
use App\Models\VariationOrder;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    private const MODULE = 'dashboard';

    private const EXPORT_FORMATS = [
        ['value' => 'pdf', 'label' => 'PDF', 'icon' => 'picture_as_pdf'],
        ['value' => 'excel', 'label' => 'Excel', 'icon' => 'table_chart'],
        ['value' => 'csv', 'label' => 'CSV', 'icon' => 'grid_on'],
    ];

    private const EXPORT_SCOPES = [
        ['value' => 'summary', 'label' => 'Dashboard Summary', 'desc' => 'Core fiscal year cards and totals.', 'icon' => 'dashboard'],
        ['value' => 'project_status', 'label' => 'Project Status', 'desc' => 'Donut chart counts by project status.', 'icon' => 'donut_large'],
        ['value' => 'budget', 'label' => 'Budget vs Expenditure', 'desc' => 'Quarterly budget and disbursement data.', 'icon' => 'bar_chart'],
        ['value' => 'activity', 'label' => 'Recent Activity', 'desc' => 'Latest audit and module updates.', 'icon' => 'history'],
        ['value' => 'deadlines', 'label' => 'Upcoming Deadlines', 'desc' => 'Soonest due dates and milestone targets.', 'icon' => 'event_note'],
    ];

    private const ACTIVITY_MODULES = [
        'projects',
        'contracts',
        'cashflow_periods',
        'variation_orders',
        'project_accomplishments',
        'invoices',
        'payments',
    ];

    public function summary(Request $request): JsonResponse
    {
        $this->authorizeModule($request, 'view');

        return response()->json([
            'data' => $this->buildDashboardPayload($request),
        ]);
    }

    public function export(Request $request)
    {
        $this->authorizeModule($request, 'export');

        $validated = $request->validate([
            'fiscal_year' => ['nullable', 'integer', 'min:2000', 'max:3000'],
            'format' => ['required', 'in:pdf,excel,csv'],
            'scopes' => ['nullable', 'array'],
            'scopes.*' => ['string'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $payload = $this->buildDashboardPayload($request);
        $scopes = collect($validated['scopes'] ?? ['summary'])
            ->intersect(collect(self::EXPORT_SCOPES)->pluck('value'))
            ->values()
            ->all();

        if (empty($scopes)) {
            $scopes = collect(self::EXPORT_SCOPES)->pluck('value')->values()->all();
        }

        $rows = $this->exportRows($payload, $scopes);
        $filename = 'dashboard_summary_fy' . $payload['fiscal_year'] . '_' . now()->format('Ymd_His');
        $format = strtolower($validated['format']);

        if ($format === 'csv') {
            return $this->downloadCsv($rows, $filename);
        }

        if ($format === 'excel') {
            return $this->downloadExcel($rows, $filename, $payload);
        }

        return $this->downloadPdf($rows, $filename, $payload);
    }

    private function buildDashboardPayload(Request $request): array
    {
        $latestYear = $this->latestFiscalYear();
        $fiscalYear = $request->integer('fiscal_year') ?: $latestYear;
        $range = $this->fiscalYearRange($fiscalYear);
        $user = $request->user();

        $projectSummary = $this->projectSummary($range);
        $variationSummary = $this->variationOrderSummary($range);
        $cashflowSummary = $this->cashflowSummary($range);
        $invoiceSummary = $this->invoiceSummary($range);
        $recentUpdates = $this->recentActivity($range);
        $upcomingDeadlines = $this->upcomingDeadlines($range);

        $activeProjects = (int) ($projectSummary['total_projects'] ?? 0);
        $ongoingProjects = (int) ($projectSummary['ongoing_projects'] ?? 0);
        $completedProjects = (int) ($projectSummary['completed_projects'] ?? 0);
        $planningProjects = (int) ($projectSummary['planning_projects'] ?? 0);
        $delayedProjects = (int) ($projectSummary['delayed_projects'] ?? 0);
        $totalBudget = (float) ($projectSummary['total_budget'] ?? 0);
        $ongoingRate = $activeProjects > 0
            ? round(($ongoingProjects / $activeProjects) * 100, 1)
            : 0;
        $completionRate = $activeProjects > 0
            ? round(($completedProjects / $activeProjects) * 100, 1)
            : 0;
        $disbursementRate = (float) ($cashflowSummary['disbursement_rate'] ?? 0);
        $pendingVos = (int) ($variationSummary['pending_vos'] ?? 0);
        $overdueDocuments = (int) ($invoiceSummary['overdue_documents'] ?? 0);
        $systemAlerts = $delayedProjects + $pendingVos + $overdueDocuments;

        return [
            'fiscal_year' => (string) $fiscalYear,
            'fiscal_years' => $this->fiscalYearOptions($fiscalYear),
            'organization' => [
                'name' => 'BFP Region II',
                'region' => 'Region II',
                'office_unit' => $user?->office_unit ?: 'BFP Region II',
                'position' => $user?->position ?: null,
            ],
            'footer_year' => now()->year,
            'permissions' => $user?->modulePermissions(self::MODULE) ?? [],
            'export' => [
                'formats' => self::EXPORT_FORMATS,
                'scopes' => self::EXPORT_SCOPES,
                'defaults' => [
                    'format' => 'pdf',
                    'scopes' => collect(self::EXPORT_SCOPES)->pluck('value')->values()->all(),
                    'date_from' => $range['start']->toDateString(),
                    'date_to' => $range['end']->toDateString(),
                ],
            ],
            'stats' => [
                [
                    'key' => 'active_projects',
                    'label' => 'ACTIVE PROJECTS',
                    'value' => number_format($activeProjects),
                    'badge' => $activeProjects > 0 ? $fiscalYear . ' Scope' : 'No Projects Found',
                    'badgeClass' => 'text-info-badge',
                    'icon' => 'assignment',
                    'iconClass' => 'stat-icon-blue',
                    'cardClass' => '',
                ],
                [
                    'key' => 'ongoing_projects',
                    'label' => 'ONGOING PROJECTS',
                    'value' => number_format($ongoingProjects),
                    'badge' => $ongoingRate > 0 ? $ongoingRate . '% On Track' : 'No Active Work',
                    'badgeClass' => 'text-success-badge',
                    'icon' => 'build',
                    'iconClass' => 'stat-icon-yellow',
                    'cardClass' => '',
                ],
                [
                    'key' => 'total_budget',
                    'label' => 'TOTAL PROJECT BUDGET (FY' . $fiscalYear . ')',
                    'value' => $this->peso($totalBudget),
                    'badge' => 'Disbursement Rate: ' . number_format($disbursementRate, 1) . '%',
                    'badgeClass' => 'text-muted-badge',
                    'icon' => 'payments',
                    'iconClass' => 'stat-icon-teal',
                    'cardClass' => 'stat-card-wide',
                ],
                [
                    'key' => 'completed_projects',
                    'label' => 'COMPLETED PROJECTS',
                    'value' => number_format($completedProjects),
                    'badge' => 'Annual Goal: ' . number_format($completionRate, 1) . '%',
                    'badgeClass' => 'text-success-badge',
                    'icon' => 'check_circle',
                    'iconClass' => 'stat-icon-green',
                    'cardClass' => '',
                ],
                [
                    'key' => 'pending_vos',
                    'label' => 'PENDING VOS',
                    'value' => number_format($pendingVos),
                    'badge' => $pendingVos > 0 ? $pendingVos . ' Awaiting Review' : 'No Open VOs',
                    'badgeClass' => 'text-warning-badge',
                    'icon' => 'edit_note',
                    'iconClass' => 'stat-icon-orange',
                    'cardClass' => '',
                ],
                [
                    'key' => 'overdue_documents',
                    'label' => 'OVERDUE DOCUMENTS',
                    'value' => number_format($overdueDocuments),
                    'badge' => $overdueDocuments > 0 ? $overdueDocuments . ' Due Items' : 'No Overdue Items',
                    'badgeClass' => 'text-muted-badge',
                    'icon' => 'warning',
                    'iconClass' => 'stat-icon-red',
                    'cardClass' => 'stat-card-danger',
                ],
                [
                    'key' => 'system_alerts',
                    'label' => 'SYSTEM ALERTS',
                    'value' => number_format($systemAlerts),
                    'badge' => $systemAlerts > 0 ? $systemAlerts . ' Open Alerts' : 'All Systems Nominal',
                    'badgeClass' => 'text-muted-badge',
                    'icon' => 'notifications_none',
                    'iconClass' => 'stat-icon-gray',
                    'cardClass' => '',
                ],
            ],
            'charts' => [
                'project_status' => [
                    'total' => $activeProjects,
                    'labels' => ['Planning', 'Ongoing', 'Completed', 'Delayed'],
                    'data' => [
                        $planningProjects,
                        $ongoingProjects,
                        $completedProjects,
                        $delayedProjects,
                    ],
                    'colors' => ['#4A90D9', '#7B6B3D', '#3EBD7F', '#E05C5C'],
                ],
                'budget_vs_expenditure' => $cashflowSummary['quarterly_breakdown'],
            ],
            'recent_updates' => $recentUpdates,
            'upcoming_deadlines' => $upcomingDeadlines,
        ];
    }

    private function projectSummary(array $range): array
    {
        $query = Project::query()
            ->where('is_archived', false)
            ->whereNotNull('target_start_date')
            ->whereBetween('target_start_date', [$range['start']->toDateString(), $range['end']->toDateString()]);

        return (clone $query)->selectRaw(
            "COUNT(*) as total_projects,
            SUM(CASE WHEN status IN ('ongoing', 'on_time') THEN 1 ELSE 0 END) as ongoing_projects,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_projects,
            SUM(CASE WHEN status = 'planning' THEN 1 ELSE 0 END) as planning_projects,
            SUM(CASE WHEN status = 'delayed' THEN 1 ELSE 0 END) as delayed_projects,
            COALESCE(SUM(approved_budget), 0) as total_budget"
        )->first()->toArray();
    }

    private function variationOrderSummary(array $range): array
    {
        $query = VariationOrder::query()
            ->where('is_archived', false)
            ->whereNotNull('submitted_at')
            ->whereBetween('submitted_at', [$range['start'], $range['end']]);

        return [
            'pending_vos' => (clone $query)->whereIn('status', ['Draft', 'Submitted', 'Under Review'])->count(),
        ];
    }

    private function cashflowSummary(array $range): array
    {
        $query = CashflowPeriod::query()
            ->where('is_archived', false)
            ->whereNotNull('period_start')
            ->whereBetween('period_start', [$range['start']->toDateString(), $range['end']->toDateString()])
            ->whereHas('contract', fn ($contract) => $contract->where('is_archived', false));

        $quarterRows = (clone $query)
            ->selectRaw('QUARTER(period_start) as fiscal_quarter, COALESCE(SUM(planned_amount), 0) as budget, COALESCE(SUM(actual_amount), 0) as expenditure')
            ->groupByRaw('QUARTER(period_start)')
            ->get()
            ->keyBy(fn ($row) => (int) $row->fiscal_quarter);

        $budgetTotal = 0.0;
        $expenditureTotal = 0.0;
        $budget = [];
        $expenditure = [];
        $labels = [];

        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $row = $quarterRows->get($quarter);
            $budgetValue = (float) ($row->budget ?? 0);
            $expenditureValue = (float) ($row->expenditure ?? 0);

            $labels[] = 'Q' . $quarter;
            $budget[] = $budgetValue;
            $expenditure[] = $expenditureValue;
            $budgetTotal += $budgetValue;
            $expenditureTotal += $expenditureValue;
        }

        return [
            'planned_total' => $budgetTotal,
            'actual_total' => $expenditureTotal,
            'disbursement_rate' => $budgetTotal > 0 ? round(($expenditureTotal / $budgetTotal) * 100, 1) : 0,
            'quarterly_breakdown' => [
                'labels' => $labels,
                'budget' => $budget,
                'expenditure' => $expenditure,
                'subtitle' => 'Comparison of allocated funds vs actual disbursements per quarter.',
            ],
        ];
    }

    private function invoiceSummary(array $range): array
    {
        $query = Invoice::query()
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$range['start']->toDateString(), $range['end']->toDateString()])
            ->whereHas('contract', fn ($contract) => $contract->where('is_archived', false));

        return [
            'overdue_documents' => (clone $query)->where('status', '!=', 'Paid')->whereDate('due_date', '<=', $range['end']->toDateString())->count(),
        ];
    }

    private function recentActivity(array $range): array
    {
        return AuditLog::query()
            ->with('user:id,name,position,office_unit')
            ->whereBetween('performed_at', [$range['start'], $range['end']])
            ->whereIn('module', self::ACTIVITY_MODULES)
            ->latest('performed_at')
            ->limit(3)
            ->get()
            ->map(function (AuditLog $log) {
                $moduleLabel = str_replace('_', ' ', $log->module);
                $title = $log->record_code
                    ? ucfirst($moduleLabel) . ' - ' . $log->record_code
                    : ucfirst($moduleLabel);

                return [
                    'id' => $log->id,
                    'icon' => $this->activityIcon($log->module, $log->action),
                    'iconBg' => $this->activityTone($log->module),
                    'project' => $title,
                    'time' => optional($log->performed_at)->diffForHumans(),
                    'description' => $this->activityDescription($log),
                ];
            })
            ->values()
            ->all();
    }

    private function upcomingDeadlines(array $range): array
    {
        $start = Carbon::today();
        $end = $range['end']->copy()->endOfDay();

        $items = collect();

        Project::query()
            ->where('is_archived', false)
            ->whereNotNull('target_end_date')
            ->whereBetween('target_end_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('target_end_date')
            ->limit(3)
            ->get(['id', 'project_name', 'location', 'target_end_date'])
            ->each(function (Project $project) use (&$items) {
                $dueDate = Carbon::parse($project->target_end_date);

                $items->push([
                    'id' => 'project-' . $project->id,
                    'month' => $dueDate->format('M'),
                    'day' => $dueDate->format('d'),
                    'dateColor' => $dueDate->diffInDays(Carbon::today(), false) <= 14 ? 'date-red' : 'date-blue',
                    'title' => $project->project_name,
                    'description' => $project->location ? 'Target completion in ' . $project->location : 'Project completion deadline',
                    'urgent' => $dueDate->diffInDays(Carbon::today(), false) <= 14,
                ]);
            });

        Invoice::query()
            ->with('contract:id,contract_number')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
            ->where('status', '!=', 'Paid')
            ->orderBy('due_date')
            ->limit(3)
            ->get(['id', 'contract_id', 'invoice_number', 'due_date'])
            ->each(function (Invoice $invoice) use (&$items) {
                $dueDate = Carbon::parse($invoice->due_date);

                $items->push([
                    'id' => 'invoice-' . $invoice->id,
                    'month' => $dueDate->format('M'),
                    'day' => $dueDate->format('d'),
                    'dateColor' => $dueDate->diffInDays(Carbon::today(), false) <= 14 ? 'date-red' : 'date-blue',
                    'title' => 'Invoice ' . $invoice->invoice_number,
                    'description' => 'Contract ' . ($invoice->contract?->contract_number ?? '-'),
                    'urgent' => $dueDate->diffInDays(Carbon::today(), false) <= 14,
                ]);
            });

        ProjectAccomplishment::query()
            ->with('project:id,project_name')
            ->where('is_archived', false)
            ->whereNotNull('target_date')
            ->whereBetween('target_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('target_date')
            ->limit(3)
            ->get(['id', 'project_id', 'milestone_title', 'target_date'])
            ->each(function (ProjectAccomplishment $accomplishment) use (&$items) {
                $dueDate = Carbon::parse($accomplishment->target_date);

                $items->push([
                    'id' => 'accomplishment-' . $accomplishment->id,
                    'month' => $dueDate->format('M'),
                    'day' => $dueDate->format('d'),
                    'dateColor' => $dueDate->diffInDays(Carbon::today(), false) <= 14 ? 'date-red' : 'date-blue',
                    'title' => $accomplishment->milestone_title,
                    'description' => 'Project ' . ($accomplishment->project?->project_name ?? '-'),
                    'urgent' => $dueDate->diffInDays(Carbon::today(), false) <= 14,
                ]);
            });

        return $items
            ->sortBy(fn (array $item) => Carbon::parse($item['month'] . ' ' . $item['day'] . ' ' . $range['end']->year)->timestamp)
            ->values()
            ->take(3)
            ->all();
    }

    private function exportRows(array $payload, array $scopes): array
    {
        $rows = [];

        if (in_array('summary', $scopes, true)) {
            foreach ($payload['stats'] as $stat) {
                $rows[] = ['section' => 'Summary', 'label' => $stat['label'], 'value' => $stat['value'], 'detail' => $stat['badge']];
            }
        }

        if (in_array('project_status', $scopes, true)) {
            $status = $payload['charts']['project_status'];
            foreach ($status['labels'] as $index => $label) {
                $rows[] = ['section' => 'Project Status', 'label' => $label, 'value' => $status['data'][$index] ?? 0, 'detail' => 'Total: ' . $status['total']];
            }
        }

        if (in_array('budget', $scopes, true)) {
            $budget = $payload['charts']['budget_vs_expenditure'];
            foreach ($budget['labels'] as $index => $label) {
                $rows[] = [
                    'section' => 'Budget vs Expenditure',
                    'label' => $label,
                    'value' => $this->peso((float) ($budget['budget'][$index] ?? 0)),
                    'detail' => 'Expenditure: ' . $this->peso((float) ($budget['expenditure'][$index] ?? 0)),
                ];
            }
        }

        if (in_array('activity', $scopes, true)) {
            foreach ($payload['recent_updates'] as $item) {
                $rows[] = ['section' => 'Recent Activity', 'label' => $item['project'], 'value' => $item['time'], 'detail' => $item['description']];
            }
        }

        if (in_array('deadlines', $scopes, true)) {
            foreach ($payload['upcoming_deadlines'] as $item) {
                $rows[] = ['section' => 'Upcoming Deadlines', 'label' => $item['title'], 'value' => $item['month'] . ' ' . $item['day'], 'detail' => $item['description']];
            }
        }

        return $rows;
    }

    private function downloadCsv(array $rows, string $filename)
    {
        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Section', 'Label', 'Value', 'Detail']);

            foreach ($rows as $row) {
                fputcsv($handle, [$row['section'], $row['label'], $row['value'], $row['detail']]);
            }

            fclose($handle);
        }, $filename . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function downloadExcel(array $rows, string $filename, array $payload)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dashboard Summary');

        $sheet->setCellValue('A1', 'BFP Region II Dashboard Summary FY' . $payload['fiscal_year']);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', 'Organization: ' . ($payload['organization']['office_unit'] ?? 'BFP Region II'));
        $sheet->setCellValue('A3', 'Generated: ' . now()->format('M d, Y h:i A'));

        $headersRow = 5;
        $sheet->setCellValue('A' . $headersRow, 'Section');
        $sheet->setCellValue('B' . $headersRow, 'Label');
        $sheet->setCellValue('C' . $headersRow, 'Value');
        $sheet->setCellValue('D' . $headersRow, 'Detail');

        foreach (['A', 'B', 'C', 'D'] as $column) {
            $sheet->getStyle($column . $headersRow)->getFont()->setBold(true);
            $sheet->getStyle($column . $headersRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C82A3E');
            $sheet->getStyle($column . $headersRow)->getFont()->getColor()->setRGB('FFFFFF');
        }

        $row = $headersRow + 1;
        foreach ($rows as $item) {
            $sheet->setCellValue('A' . $row, $item['section']);
            $sheet->setCellValue('B' . $row, $item['label']);
            $sheet->setCellValue('C' . $row, $item['value']);
            $sheet->setCellValue('D' . $row, $item['detail']);
            $row++;
        }

        foreach (range(1, 4) as $columnIndex) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($columnIndex))->setAutoSize(true);
        }

        $temp = tempnam(sys_get_temp_dir(), 'dashboard_');
        (new Xlsx($spreadsheet))->save($temp);

        return response()->download($temp, $filename . '.xlsx')->deleteFileAfterSend(true);
    }

    private function downloadPdf(array $rows, string $filename, array $payload)
    {
        $sections = collect($rows)->groupBy('section');
        $html = '<html><head><style>
            body{font-family:Arial,sans-serif;color:#1f2937;font-size:12px}
            h1{font-size:18px;margin:0 0 6px}
            h2{font-size:14px;margin:18px 0 6px;border-bottom:1px solid #d1d5db;padding-bottom:4px}
            table{width:100%;border-collapse:collapse;margin-bottom:12px}
            th,td{border:1px solid #d1d5db;padding:6px 8px;text-align:left;vertical-align:top}
            th{background:#f3f4f6}
        </style></head><body>';
        $html .= '<h1>BFP Region II Dashboard Summary FY' . e($payload['fiscal_year']) . '</h1>';
        $html .= '<div>Organization: ' . e($payload['organization']['office_unit'] ?? 'BFP Region II') . '</div>';
        $html .= '<div>Generated: ' . e(now()->format('M d, Y h:i A')) . '</div>';

        foreach ($sections as $section => $items) {
            $html .= '<h2>' . e($section) . '</h2><table><thead><tr><th>Label</th><th>Value</th><th>Detail</th></tr></thead><tbody>';
            foreach ($items as $item) {
                $html .= '<tr><td>' . e($item['label']) . '</td><td>' . e((string) $item['value']) . '</td><td>' . e($item['detail']) . '</td></tr>';
            }
            $html .= '</tbody></table>';
        }

        $html .= '</body></html>';

        return Pdf::loadHTML($html)->setPaper('a4', 'portrait')->download($filename . '.pdf');
    }

    private function latestFiscalYear(): int
    {
        $years = collect([
            Project::query()->whereNotNull('target_start_date')->max('target_start_date'),
            Contract::query()->whereNotNull('start_date')->max('start_date'),
            CashflowPeriod::query()->whereNotNull('period_start')->max('period_start'),
            VariationOrder::query()->whereNotNull('submitted_at')->max('submitted_at'),
            ProjectAccomplishment::query()->whereNotNull('target_date')->max('target_date'),
            Invoice::query()->whereNotNull('due_date')->max('due_date'),
            AuditLog::query()->whereNotNull('performed_at')->max('performed_at'),
        ])
            ->filter()
            ->map(fn ($value) => Carbon::parse($value)->year);

        return $years->isNotEmpty() ? (int) $years->max() : now()->year;
    }

    private function fiscalYearOptions(int $selectedYear): array
    {
        return collect(range($selectedYear, max($selectedYear - 3, 2000)))
            ->map(function (int $year) use ($selectedYear) {
                return [
                    'value' => (string) $year,
                    'range' => 'Jan ' . $year . ' - Dec ' . $year,
                    'tag' => $year === $selectedYear ? 'Current' : 'Closed',
                    'tagClass' => $year === $selectedYear ? 'fy-tag-green' : 'fy-tag-gray',
                ];
            })
            ->values()
            ->all();
    }

    private function fiscalYearRange(int $year): array
    {
        return [
            'start' => Carbon::create($year, 1, 1)->startOfDay(),
            'end' => Carbon::create($year, 12, 31)->endOfDay(),
        ];
    }

    private function activityIcon(string $module, string $action): string
    {
        return match ($module) {
            'variation_orders' => 'edit_note',
            'cashflow_periods', 'invoices', 'payments' => 'payments',
            'project_accomplishments' => 'check_circle',
            'contracts' => 'description',
            default => $action === 'deleted' ? 'delete' : 'history',
        };
    }

    private function activityTone(string $module): string
    {
        return match ($module) {
            'variation_orders' => 'icon-bg-red',
            'cashflow_periods', 'invoices', 'payments' => 'icon-bg-blue',
            'project_accomplishments' => 'icon-bg-yellow',
            default => 'icon-bg-blue',
        };
    }

    private function activityDescription(AuditLog $log): string
    {
        $subject = $log->remarks ?: ucfirst(str_replace('_', ' ', $log->module)) . ' record updated.';
        $actor = $log->user?->name;

        if ($actor) {
            return $subject . ' Updated by ' . $actor . '.';
        }

        return $subject;
    }

    private function peso(float $amount): string
    {
        return '₱' . number_format($amount, 2);
    }

    private function authorizeModule(Request $request, string $ability): void
    {
        $user = $request->user();

        abort_unless($user, Response::HTTP_UNAUTHORIZED, 'Authentication required.');
        abort_unless($user->canModule(self::MODULE, $ability), Response::HTTP_FORBIDDEN, 'You do not have permission to ' . $ability . ' the dashboard.');
    }
}
