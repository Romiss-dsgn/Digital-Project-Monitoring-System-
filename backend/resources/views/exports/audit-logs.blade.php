<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: Arial, sans-serif; font-size: 10px; }
  .header { background: #1a2a4a; color: white; padding: 10px; text-align: center; margin-bottom: 5px; }
  .header h2 { margin: 0; font-size: 14px; }
  .header p { margin: 2px 0; font-size: 9px; }
  .stripe { background: #c0392b; color: white; padding: 4px 10px; font-size: 9px; display: flex; justify-content: space-between; margin-bottom: 10px; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #c0392b; color: white; padding: 5px; text-align: left; font-size: 9px; }
  td { padding: 4px 5px; border-bottom: 1px solid #eee; font-size: 9px; }
  tr:nth-child(even) { background: #f8f9fa; }
  .action { padding: 2px 5px; border-radius: 3px; color: white; font-size: 8px; font-weight: bold; }
  .created  { background: #2e7d32; }
  .updated  { background: #1565c0; }
  .deleted  { background: #d32f2f; }
  .accessed { background: #6a1b9a; }
  .uploaded { background: #0277bd; }
  .footer { margin-top: 10px; font-size: 8px; color: #666; text-align: center; }
</style>
</head>
<body>
<div class="header">
  <h2>LGU Tuao</h2>
  <p>Audit Logs Export &mdash; {{ $label }}</p>
  <p>Generated: {{ now()->format("F d, Y h:i A") }}</p>
</div>
<div class="stripe">
  <span>MUNICIPALITY OF TUAO</span>
  <span>ACCOUNTABILITY RECORD EXPORT</span>
</div>
<table>
  <thead>
    <tr>
      <th>Date/Time</th>
      <th>User</th>
      <th>Role</th>
      <th>Module</th>
      <th>Action</th>
      <th>Record</th>
      <th>Remarks</th>
      @if($includeIp)<th>IP Address</th>@endif
    </tr>
  </thead>
  <tbody>
    @foreach($logs as $log)
    <tr>
      <td>{{ \Carbon\Carbon::parse($log->performed_at)->format("M d, Y H:i") }}</td>
      <td>{{ $log->user_name ?? "System" }}</td>
      <td>{{ $log->role_name ?? "N/A" }}</td>
      <td>{{ $log->module }}</td>
      <td><span class="action {{ strtolower($log->action) }}">{{ strtoupper($log->action) }}</span></td>
      <td>{{ $log->record_code ?? "-" }}</td>
      <td>{{ $log->remarks ?? "-" }}</td>
      @if($includeIp)<td>{{ $log->ip_address ?? "-" }}</td>@endif
    </tr>
    @endforeach
  </tbody>
</table>
<div class="footer">Total Records: {{ count($logs) }} &mdash; {{ $filename }}</div>
</body>
</html>
