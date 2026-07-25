<h2 style="font-family:sans-serif;color:#0B3C91;">Audit Trail Report — PECIT PSIS</h2>
<p>Generated: {{ now()->toFormattedDateString() }}</p>
<table border="1" cellpadding="6" cellspacing="0" width="100%" style="font-size:11px;">
<tr style="background:#0B3C91;color:#fff;"><th>Date</th><th>User</th><th>Action</th><th>IP</th></tr>
@forelse($logs as $log)
<tr><td>{{ $log->created_at?->format('Y-m-d H:i') }}</td><td>{{ $log->user?->name ?? 'System' }}</td><td>{{ $log->action }}</td><td>{{ $log->ip_address ?? '—' }}</td></tr>
@empty
<tr><td colspan="4">No audit logs found.</td></tr>
@endforelse
</table>
