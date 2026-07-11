<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\CashflowPeriod;
use App\Models\Contract;
use App\Models\Contractor;
use App\Models\Project;
use App\Models\User;
use App\Models\VariationOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    private const MODULE = 'reports';
    private const TYPES = ['project-status', 'contract-summary', 'cashflow-analysis', 'variation-orders'];

    public function projectStatus(Request $request): JsonResponse { return $this->show($request, 'project-status'); }

    public function show(Request $request, string $reportType): JsonResponse
    {
        $user = $this->authorizeModule($request, 'view');
        abort_unless(in_array($reportType, self::TYPES, true), 404, 'Unknown report type.');
        return response()->json(['data' => $this->buildReport($reportType, $request), 'meta' => ['permissions' => ['can_export' => $user->canModule(self::MODULE, 'export')]]]);
    }

    public function export(Request $request, string $reportType)
    {
        $this->authorizeModule($request, 'export');
        abort_unless(in_array($reportType, self::TYPES, true), 404, 'Unknown report type.');
        $format = $request->query('format', 'pdf');
        abort_unless(in_array($format, ['pdf', 'xlsx'], true), 422, 'Unsupported export format.');
        $report = $this->buildReport($reportType, $request);
        $filename = str($reportType)->replace('-', '_').'-'.now()->format('Ymd_His');
        if ($format === 'pdf') {
            return Pdf::loadView('exports.report', compact('report'))
                ->setPaper($request->query('paper', 'a4'), $request->query('orientation', 'portrait'))
                ->download("{$filename}.pdf");
        }
        $book = new Spreadsheet(); $sheet = $book->getActiveSheet();
        $sheet->setTitle(substr($report['title'], 0, 31));
        $sheet->setCellValue('A1', $report['title']);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', 'Generated: '.now()->format('M d, Y h:i A'));
        foreach ($report['columns'] as $index => $column) { $cell = Coordinate::stringFromColumnIndex($index + 1).'4'; $sheet->setCellValue($cell, $column['label']); $sheet->getStyle($cell)->getFont()->setBold(true); }
        foreach ($report['rows'] as $rowIndex => $row) foreach ($report['columns'] as $index => $column) $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1).($rowIndex + 5), $row[$column['key']] ?? '');
        foreach (range(1, count($report['columns'])) as $column) $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setAutoSize(true);
        $temp = tempnam(sys_get_temp_dir(), 'report_'); (new Xlsx($book))->save($temp);
        return response()->download($temp, "{$filename}.xlsx")->deleteFileAfterSend(true);
    }

    private function buildReport(string $type, Request $request): array
    {
        return match ($type) {
            'contract-summary' => $this->contractSummary($request),
            'cashflow-analysis' => $this->cashflow($request),
            'variation-orders' => $this->variationOrders($request),
            default => $this->projectStatusData($request),
        };
    }

    private function projectStatusData(Request $request): array
    {
        $projects = $this->projects($request)->with('contracts.contractor')->latest('updated_at')->get();
        return $this->report('project-status', 'Project Status Report', [['label'=>'Projects','value'=>$projects->count()], ['label'=>'Total Budget','value'=>$this->peso($projects->sum('approved_budget'))], ['label'=>'Average Completion','value'=>number_format((float)$projects->avg('progress_percent'), 1).'%']], [['key'=>'project_name','label'=>'Project Name'],['key'=>'contractor','label'=>'Contractor'],['key'=>'status','label'=>'Status'],['key'=>'completion','label'=>'Completion','align'=>'end']], $projects->map(fn($p) => ['project_name'=>$p->project_name, 'contractor'=>$p->contracts->first()?->contractor?->company_name ?? $p->implementing_office ?? '-', 'status'=>$p->status, 'completion'=>number_format((float)$p->progress_percent,0).'%'])->all(), $request);
    }

    private function contractSummary(Request $request): array
    {
        $q = Contract::query()->where('is_archived', false)->with(['project','contractor']); $this->contractFilters($q, $request);
        $items = $q->latest('updated_at')->get();
        return $this->report('contract-summary', 'Contract Summary Report', [['label'=>'Contracts','value'=>$items->count()],['label'=>'Original Amount','value'=>$this->peso($items->sum('original_contract_amount'))],['label'=>'Revised Amount','value'=>$this->peso($items->sum('revised_contract_amount'))]], [['key'=>'contract_number','label'=>'Contract No.'],['key'=>'project','label'=>'Project'],['key'=>'contractor','label'=>'Contractor'],['key'=>'amount','label'=>'Revised Amount','align'=>'end'],['key'=>'status','label'=>'Status']], $items->map(fn($c)=>['contract_number'=>$c->contract_number,'project'=>$c->project?->project_name ?? '-','contractor'=>$c->contractor?->company_name ?? '-','amount'=>$this->peso($c->revised_contract_amount),'status'=>$c->status])->all(), $request);
    }

    private function cashflow(Request $request): array
    {
        $q = CashflowPeriod::query()->where('is_archived', false)->with('contract.project','contract.contractor'); $this->contractFilters($q, $request, 'contract');
        $q->when($request->date_from, fn($x,$date)=>$x->where('period_start','>=',$date))->when($request->date_to, fn($x,$date)=>$x->where('period_end','<=',$date)); $items=$q->latest('period_start')->get();
        return $this->report('cashflow-analysis', 'Cashflow Analysis Report', [['label'=>'Periods','value'=>$items->count()],['label'=>'Planned','value'=>$this->peso($items->sum('planned_amount'))],['label'=>'Actual','value'=>$this->peso($items->sum('actual_amount'))],['label'=>'Variance','value'=>$this->peso($items->sum('variance'))]], [['key'=>'period','label'=>'Period'],['key'=>'contract','label'=>'Contract'],['key'=>'planned','label'=>'Planned','align'=>'end'],['key'=>'actual','label'=>'Actual','align'=>'end'],['key'=>'variance','label'=>'Variance','align'=>'end']], $items->map(fn($p)=>['period'=>$p->period_label,'contract'=>$p->contract?->contract_number ?? '-','planned'=>$this->peso($p->planned_amount),'actual'=>$this->peso($p->actual_amount),'variance'=>$this->peso($p->variance)])->all(), $request);
    }

    private function variationOrders(Request $request): array
    {
        $q=VariationOrder::query()->where('is_archived',false)->with('contract.project','contract.contractor'); $this->contractFilters($q,$request,'contract');
        $q->when($request->date_from,fn($x,$date)=>$x->whereDate('submitted_at','>=',$date))->when($request->date_to,fn($x,$date)=>$x->whereDate('submitted_at','<=',$date)); $items=$q->latest('submitted_at')->get();
        return $this->report('variation-orders','Variation Orders Audit Report',[['label'=>'Variation Orders','value'=>$items->count()],['label'=>'Approved','value'=>$items->where('status','Approved')->count()],['label'=>'Net Cost Change','value'=>$this->peso($items->sum('amount_change'))],['label'=>'Time Impact','value'=>$items->sum('time_impact_days').' days']],[['key'=>'vo_number','label'=>'VO No.'],['key'=>'contract','label'=>'Contract'],['key'=>'amount','label'=>'Cost Change','align'=>'end'],['key'=>'days','label'=>'Time Impact'],['key'=>'status','label'=>'Status']],$items->map(fn($v)=>['vo_number'=>$v->vo_number,'contract'=>$v->contract?->contract_number ?? '-','amount'=>$this->peso($v->amount_change),'days'=>($v->time_impact_days ?? 0).' days','status'=>$v->status])->all(),$request);
    }

    private function report($type,$title,$stats,$columns,$rows,Request $request): array { return ['report_type'=>$type,'title'=>$title,'report_id'=>'BFP-R2-'.now()->format('YmdHis'),'generated_at'=>now()->toIso8601String(),'stats'=>$stats,'columns'=>$columns,'rows'=>$rows,'filters'=>['contractors'=>Contractor::where('is_active',true)->orderBy('company_name')->get(['id','company_name'])]]; }
    private function projects(Request $r) { return Project::where('is_archived',false)->when($r->date_from,fn($q,$d)=>$q->where('target_start_date','>=',$d))->when($r->date_to,fn($q,$d)=>$q->where('target_end_date','<=',$d))->when($r->portfolio==='completed',fn($q)=>$q->where('status','completed'))->when($r->portfolio==='ongoing',fn($q)=>$q->whereIn('status',['ongoing','on_time']))->when($r->contractor_id,fn($q,$id)=>$q->whereHas('contracts',fn($c)=>$c->where('contractor_id',$id))); }
    private function contractFilters($q,Request $r,$relation=''): void
    {
        $projectRelation = ($relation ? $relation.'.' : '').'project';

        $q->when($r->contractor_id, function ($query, $contractorId) use ($relation) {
            return $relation
                ? $query->whereHas($relation, fn ($contract) => $contract->where('contractor_id', $contractorId))
                : $query->where('contractor_id', $contractorId);
        })->when($relation === '' && $r->date_from, fn($x,$d) => $x->where('start_date','>=',$d))
          ->when($relation === '' && $r->date_to, fn($x,$d) => $x->where('end_date','<=',$d))
          ->when($r->portfolio === 'completed', fn($x) => $x->whereHas($projectRelation, fn($p) => $p->where('status','completed')))
          ->when($r->portfolio === 'ongoing', fn($x) => $x->whereHas($projectRelation, fn($p) => $p->whereIn('status',['ongoing','on_time'])));
    }
    private function peso($amount): string { return '₱'.number_format((float)$amount,2); }
    private function authorizeModule(Request $request,string $ability): User { $user=$request->user(); abort_unless($user,Response::HTTP_UNAUTHORIZED,'Authentication required.'); abort_unless($user->canModule(self::MODULE,$ability),Response::HTTP_FORBIDDEN,"You do not have permission to {$ability} this report."); return $user; }
}
