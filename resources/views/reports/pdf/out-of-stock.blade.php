<h2 style="font-family:sans-serif;color:#0B3C91;">Out of Stock Report — PECIT PSIS</h2>
<p>Generated: {{ now()->toFormattedDateString() }}</p>
<table border="1" cellpadding="6" cellspacing="0" width="100%">
<tr style="background:#0B3C91;color:#fff;"><th>Item</th><th>Category</th><th>Location</th><th>Minimum Stock</th></tr>
@forelse($items as $item)
<tr><td>{{ $item->item_name }}</td><td>{{ $item->category?->name }}</td><td>{{ $item->location ?? '—' }}</td><td>{{ $item->minimum_stock }}</td></tr>
@empty
<tr><td colspan="4">No out-of-stock items.</td></tr>
@endforelse
</table>
