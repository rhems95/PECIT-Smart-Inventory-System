<h2>Low Stock Report</h2><table border="1" cellpadding="6"><tr><th>Item</th><th>Available</th><th>Minimum</th></tr>
@foreach($items as $item)<tr><td>{{ $item->item_name }}</td><td>{{ $item->availableQuantity() }}</td><td>{{ $item->minimum_stock }}</td></tr>@endforeach</table>
