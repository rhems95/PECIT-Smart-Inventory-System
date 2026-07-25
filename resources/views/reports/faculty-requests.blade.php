@extends('layouts.psis')
@section('page-title', 'Faculty Request Report')
@section('content')
<div class="psis-card overflow-hidden"><table class="min-w-full text-sm"><thead><tr><th class="px-4 py-3 text-left">Number</th><th>User</th><th>Status</th><th>Total</th></tr></thead><tbody>
@foreach($requests as $req)<tr class="border-t border-[var(--psis-border)]"><td class="px-4 py-3">{{ $req->request_number }}</td><td class="px-4 py-3">{{ $req->user?->name }}</td><td class="px-4 py-3">{{ $req->status }}</td><td class="px-4 py-3">₱{{ number_format($req->total_amount,2) }}</td></tr>@endforeach
</tbody></table></div>
@endsection
