@extends('layouts.psis')

@section('title', $supplyRequest->request_number)
@section('page-title', 'Request '.$supplyRequest->request_number)

@section('content')
<div class="space-y-4 max-w-4xl">
    @include('partials.supply-request-detail')
    @if ($supplyRequest->user_id === auth()->id() && in_array($supplyRequest->status, ['pending', 'accounting_review', 'admin_review']))
        <form method="POST" action="{{ route('requests.cancel', $supplyRequest) }}" onsubmit="return confirm('Cancel this request?')">
            @csrf
            <button class="psis-btn-outline text-red-600">Cancel Request</button>
        </form>
    @endif
</div>
@endsection
