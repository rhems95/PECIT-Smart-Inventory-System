@extends('layouts.psis')

@section('title', 'Release '.$supplyRequest->request_number)
@section('page-title', 'Release Request')

@section('content')
<div class="space-y-4 max-w-4xl">
    <div class="psis-card p-5 grid sm:grid-cols-2 gap-3 text-sm">
        <p><span class="text-slate-500">Request #:</span> <strong>{{ $supplyRequest->request_number }}</strong></p>
        <p><span class="text-slate-500">Requester:</span> {{ $supplyRequest->user?->name ?? '—' }}</p>
        <p><span class="text-slate-500">Department:</span> {{ $supplyRequest->department?->name ?? $supplyRequest->user?->department?->name ?? '—' }}</p>
        <p><span class="text-slate-500">Status:</span>
            <span class="inline-flex px-2 py-0.5 rounded text-xs bg-slate-100 dark:bg-slate-700">
                {{ str_replace('_', ' ', $supplyRequest->status) }}
            </span>
        </p>
        <p class="sm:col-span-2"><span class="text-slate-500">Purpose:</span> {{ $supplyRequest->purpose ?? '—' }}</p>
        <p><span class="text-slate-500">Total:</span> ₱{{ number_format($supplyRequest->total_amount, 2) }}</p>
    </div>

    <div class="psis-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900/50 text-left text-slate-500 dark:text-slate-400">
                    <th class="px-4 py-3 font-medium">Item</th>
                    <th class="px-4 py-3 font-medium">Requested</th>
                    <th class="px-4 py-3 font-medium">Approved</th>
                    <th class="px-4 py-3 font-medium text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($supplyRequest->items as $line)
                    <tr class="border-t border-[var(--psis-border)]">
                        <td class="px-4 py-3">{{ $line->inventory?->item_name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $line->quantity_requested }}</td>
                        <td class="px-4 py-3">{{ $line->quantity_approved ?: '—' }}</td>
                        <td class="px-4 py-3 text-right">₱{{ number_format($line->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($supplyRequest->status === 'approved')
        <form method="POST" action="{{ route('supply.releases.release', $supplyRequest) }}">
            @csrf
            <button type="submit" class="psis-btn-primary" onclick="return confirm('Confirm release and deduct inventory?')">
                Release Items
            </button>
            <a href="{{ route('supply.releases') }}" class="psis-btn-outline ml-2">Back</a>
        </form>
    @else
        <p class="text-sm text-slate-500">This request is not ready for release (status: {{ str_replace('_', ' ', $supplyRequest->status) }}).</p>
        <a href="{{ route('supply.releases') }}" class="psis-btn-outline inline-flex mt-2">Back</a>
    @endif
</div>
@endsection
