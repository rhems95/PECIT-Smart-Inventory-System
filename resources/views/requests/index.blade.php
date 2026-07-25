@extends('layouts.psis')

@section('title', 'My Requests')
@section('page-title', 'My Supply Requests')

@section('content')
<div class="flex justify-between mb-4">
    <p class="psis-page-subtitle">Track faculty requisition status</p>
    <a href="{{ route('requests.create') }}" class="psis-btn-primary">New Request</a>
</div>
<div class="psis-card overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-900/50"><tr><th class="px-4 py-3 text-left">Number</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Total</th><th class="px-4 py-3 text-left">Date</th><th></th></tr></thead>
        <tbody>
        @foreach ($requests as $req)
            <tr class="border-t border-[var(--psis-border)]">
                <td class="px-4 py-3">{{ $req->request_number }}</td>
                <td class="px-4 py-3">{{ str_replace('_', ' ', $req->status) }}</td>
                <td class="px-4 py-3">₱{{ number_format($req->total_amount, 2) }}</td>
                <td class="px-4 py-3">{{ $req->created_at?->format('M d, Y') }}</td>
                <td class="px-4 py-3"><a href="{{ route('requests.show', $req) }}" class="text-pecit-blue">Details</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $requests->links() }}
@endsection
