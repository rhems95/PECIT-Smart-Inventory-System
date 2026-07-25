<h2>Inventory Valuation</h2><table border="1" cellpadding="6"><tr><th>Item</th><th>Qty</th><th>Unit Price</th><th>Value</th></tr>
@foreach($items as $item)<tr><td>{{ $item->item_name }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_price,2) }}</td><td>{{ number_format($item->quantity * $item->unit_price,2) }}</td></tr>@endforeach
</table><p><strong>Total: ₱{{ number_format($total,2) }}</strong></p>
