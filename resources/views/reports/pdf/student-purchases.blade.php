<h2 style="font-family:sans-serif;color:#0B3C91;">Student Purchase Report — PECIT PSIS</h2>
<p>Generated: {{ now()->toFormattedDateString() }}</p>
<table border="1" cellpadding="6" cellspacing="0" width="100%">
<tr style="background:#0B3C91;color:#fff;"><th>Purchase #</th><th>Student</th><th>Total</th><th>Status</th><th>Date</th></tr>
@forelse($purchases as $purchase)
<tr><td>{{ $purchase->purchase_number }}</td><td>{{ $purchase->user?->name }}</td><td>₱{{ number_format($purchase->total_amount, 2) }}</td><td>{{ $purchase->status }}</td><td>{{ $purchase->created_at?->format('M d, Y') }}</td></tr>
@empty
<tr><td colspan="5">No purchases found.</td></tr>
@endforelse
</table>
