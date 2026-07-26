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
  .status { padding: 2px 5px; border-radius: 3px; color: white; font-size: 8px; font-weight: bold; }
  .active   { background: #2e7d32; }
  .inactive { background: #d32f2f; }
  .pending  { background: #f9a825; }
  .footer { margin-top: 10px; font-size: 8px; color: #666; text-align: center; }
</style>
</head>
<body>
<div class="header">
  <h2>LGU Tuao</h2>
  <p>User List Export</p>
  <p>Generated: {{ now()->format("F d, Y h:i A") }}</p>
</div>
<div class="stripe">
  <span>MUNICIPALITY OF TUAO</span>
  <span>USER LIST EXPORT</span>
</div>
<table>
  <thead>
    <tr>
      <th>Name</th>
      <th>Username</th>
      <th>Email</th>
      <th>Personnel ID</th>
      <th>Position</th>
      <th>Status</th>
      @if($includeExtras)
      <th>Role</th>
      <th>Unit</th>
      <th>Joined Date</th>
      <th>Last Active</th>
      @endif
    </tr>
  </thead>
  <tbody>
    @foreach($users as $u)
    <tr>
      <td>{{ $u['name'] }}</td>
      <td>{{ $u['username'] }}</td>
      <td>{{ $u['email'] }}</td>
      <td>{{ $u['badge_number'] }}</td>
      <td>{{ $u['position'] }}</td>
      <td><span class="status {{ $u['status'] }}">{{ strtoupper($u['status']) }}</span></td>
      @if($includeExtras)
      <td>{{ $u['role'] ?? 'N/A' }}</td>
      <td>{{ $u['office_unit'] ?? 'N/A' }}</td>
      <td>{{ $u['accepted_at'] ?? 'N/A' }}</td>
      <td>{{ $u['last_active'] ?? 'Never' }}</td>
      @endif
    </tr>
    @endforeach
  </tbody>
</table>
<div class="footer">Total Records: {{ count($users) }} &mdash; {{ $filename }}</div>
</body>
</html>
