<h2 style="font-family:sans-serif;color:#0B3C91;">Monthly Inventory Report — PECIT PSIS</h2>
<p>Period: {{ $month }} | Transactions this month: {{ $monthlyTransactions }}</p>
<table border="1" cellpadding="6" cellspacing="0" width="100%">
<tr style="background:#0B3C91;color:#fff;"><th>Item</th><th>Category</th><th>Qty</th><th>Reserved</th><th>Available</th><th>Status</th></tr>
@foreach($items as $item)
<tr><td>{{ $item->item_name }}</td><td>{{ $item->category?->name }}</td><td>{{ $item->quantity }}</td><td>{{ $item->reserved_quantity }}</td><td>{{ $item->availableQuantity() }}</td><td>{{ $item->status }}</td></tr>
@endforeach
</table>
