<h2>Daily Inventory — {{ $date }}</h2><table border="1" cellpadding="6"><tr><th>Code</th><th>Item</th><th>Available</th><th>Status</th></tr>
@foreach($items as $item)<tr><td>{{ $item->item_code }}</td><td>{{ $item->item_name }}</td><td>{{ $item->availableQuantity() }}</td><td>{{ $item->status }}</td></tr>@endforeach</table>
