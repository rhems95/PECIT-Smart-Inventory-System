@extends('layouts.psis')
@section('page-title', 'Approve '.$supplyRequest->request_number)
@section('content')
@include('partials.supply-request-detail')
<div class="flex gap-3 mt-4">
<form method="POST" action="{{ route('admin.requests.approve', $supplyRequest) }}">@csrf<button class="psis-btn-primary">Approve & Reserve</button></form>
<form method="POST" action="{{ route('admin.requests.reject', $supplyRequest) }}" class="flex gap-2 items-end">@csrf
<input name="rejection_reason" class="psis-input" placeholder="Rejection reason" required>
<button class="psis-btn-outline text-red-600">Reject</button></form>
</div>
@endsection
