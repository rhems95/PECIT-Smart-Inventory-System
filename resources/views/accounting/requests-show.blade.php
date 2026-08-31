@extends('layouts.psis')

@section('page-title', 'Review '.$supplyRequest->request_number)

@section('content')
<div class="space-y-4 max-w-5xl">
    <div class="psis-card p-5">
        <x-status-tracker :tracker="\App\Support\OrderStatusTracker::forSupplyRequest($supplyRequest)" />
    </div>
<form method="POST" action="{{ route('accounting.requests.review', $supplyRequest) }}" class="space-y-4">
    @csrf

    <div class="psis-card p-5 grid sm:grid-cols-2 gap-3 text-sm">
        <p><span class="text-slate-500">Request #:</span> <strong>{{ $supplyRequest->request_number }}</strong></p>
        <p><span class="text-slate-500">Requester:</span> {{ $supplyRequest->user?->name ?? '—' }}</p>
        <p><span class="text-slate-500">Department:</span> {{ $supplyRequest->department?->name ?? $supplyRequest->user?->department?->name ?? '—' }}</p>
        <p><span class="text-slate-500">Status:</span> {{ str_replace('_', ' ', $supplyRequest->status) }}</p>
        <p class="sm:col-span-2"><span class="text-slate-500">Purpose:</span> {{ $supplyRequest->purpose ?? '—' }}</p>
    </div>

    <div class="psis-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900/50 text-left text-slate-500 dark:text-slate-400">
                    <th class="px-4 py-3 font-medium">Item</th>
                    <th class="px-4 py-3 font-medium">Requested</th>
                    <th class="px-4 py-3 font-medium">Approve Qty</th>
                    <th class="px-4 py-3 font-medium">Unit Price (₱)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($supplyRequest->items as $index => $line)
                    <tr class="border-t border-[var(--psis-border)]">
                        <td class="px-4 py-3">
                            {{ $line->inventory?->item_name ?? '—' }}
                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $line->id }}">
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $line->quantity_requested }}</td>
                        <td class="px-4 py-3">
                            <input
                                type="number"
                                name="items[{{ $index }}][quantity_approved]"
                                value="{{ old("items.$index.quantity_approved", $line->quantity_approved ?: $line->quantity_requested) }}"
                                min="0"
                                class="psis-input w-28"
                                required
                            >
                        </td>
                        <td class="px-4 py-3">
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="items[{{ $index }}][unit_price]"
                                value="{{ old("items.$index.unit_price", $line->unit_price) }}"
                                class="psis-input w-32"
                                required
                            >
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex flex-wrap gap-2">
        <button type="submit" class="psis-btn-primary">Forward to Admin</button>
        <a href="{{ route('accounting.requests') }}" class="psis-btn-outline">Back</a>
    </div>
</form>
</div>
@endsection
