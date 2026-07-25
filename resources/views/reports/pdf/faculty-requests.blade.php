<h2>Faculty Requests</h2><table border="1" cellpadding="6"><tr><th>Number</th><th>User</th><th>Status</th><th>Total</th></tr>
@foreach($requests as $req)<tr><td>{{ $req->request_number }}</td><td>{{ $req->user?->name }}</td><td>{{ $req->status }}</td><td>{{ number_format($req->total_amount,2) }}</td></tr>@endforeach</table>
