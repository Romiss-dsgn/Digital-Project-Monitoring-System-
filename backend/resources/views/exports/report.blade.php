<!doctype html>
<html><head><meta charset="utf-8"><style>
body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 10px; }
h1 { font-size: 18px; color: #7f1d1d; margin: 0 0 4px; } .meta { color: #6b7280; margin-bottom: 18px; }
.stats { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 18px; } .stats td { background: #f3f4f6; padding: 9px; }
.label { color: #6b7280; font-size: 9px; } .value { font-size: 13px; font-weight: bold; margin-top: 3px; }
table.data { width: 100%; border-collapse: collapse; } .data th { background: #7f1d1d; color: #fff; padding: 7px; text-align: left; } .data td { border-bottom: 1px solid #e5e7eb; padding: 7px; } .end { text-align: right; }
.footer { position: fixed; bottom: 0; color: #6b7280; font-size: 8px; width: 100%; }
</style></head><body>
<h1>{{ strtoupper($report['title']) }}</h1>
<div class="meta">Report ID: {{ $report['report_id'] }} &nbsp; | &nbsp; LGU Tuao &nbsp; | &nbsp; Generated: {{ \Carbon\Carbon::parse($report['generated_at'])->format('M d, Y h:i A') }}</div>
<table class="stats"><tr>@foreach($report['stats'] as $stat)<td><div class="label">{{ $stat['label'] }}</div><div class="value">{{ $stat['value'] }}</div></td>@endforeach</tr></table>
<table class="data"><thead><tr>@foreach($report['columns'] as $column)<th class="{{ ($column['align'] ?? '') === 'end' ? 'end' : '' }}">{{ $column['label'] }}</th>@endforeach</tr></thead><tbody>
@forelse($report['rows'] as $row)<tr>@foreach($report['columns'] as $column)<td class="{{ ($column['align'] ?? '') === 'end' ? 'end' : '' }}">{{ $row[$column['key']] ?? '-' }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($report['columns']) }}">No records match the selected filters.</td></tr>@endforelse
</tbody></table><div class="footer">ConTrackPro · Official Report</div>
</body></html>
