@extends('layouts.psis')

@section('title', 'Release '.$purchase->purchase_number)
@section('page-title', 'Release Purchase')

@section('content')
<div class="space-y-4 max-w-3xl">
    <div class="psis-card p-5 grid sm:grid-cols-2 gap-3 text-sm">
        <p><span class="text-slate-500">Purchase #:</span> <strong>{{ $purchase->purchase_number }}</strong></p>
        <p><span class="text-slate-500">Student:</span> {{ $purchase->user?->name ?? '—' }}</p>
        <p><span class="text-slate-500">Status:</span>
            <span class="inline-flex px-2 py-0.5 rounded text-xs bg-slate-100 dark:bg-slate-700">
                {{ str_replace('_', ' ', $purchase->status) }}
            </span>
        </p>
        <p><span class="text-slate-500">Total:</span> ₱{{ number_format($purchase->total_amount, 2) }}</p>
        @if ($payment = $purchase->payments->first())
            <p><span class="text-slate-500">Payment Ref:</span> {{ $payment->reference_number }}</p>
            <p><span class="text-slate-500">Payment:</span> {{ str_replace('_', ' ', $payment->status) }}</p>
        @endif
    </div>

    <div class="psis-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900/50 text-left text-slate-500 dark:text-slate-400">
                    <th class="px-4 py-3 font-medium">Item</th>
                    <th class="px-4 py-3 font-medium">Size</th>
                    <th class="px-4 py-3 font-medium">Qty</th>
                    <th class="px-4 py-3 font-medium">Unit Price</th>
                    <th class="px-4 py-3 font-medium text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->items as $line)
                    <tr class="border-t border-[var(--psis-border)]">
                        <td class="px-4 py-3">{{ $line->inventory?->item_name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $line->size ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $line->quantity }}</td>
                        <td class="px-4 py-3">₱{{ number_format($line->unit_price, 2) }}</td>
                        <td class="px-4 py-3 text-right">₱{{ number_format($line->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($purchase->status === 'payment_verified')
        <form method="POST" action="{{ route('supply.purchases.release', $purchase) }}">
            @csrf
            <button type="submit" class="psis-btn-primary" onclick="return confirm('Confirm release and deduct inventory?')">
                Confirm Release
            </button>
            <a href="{{ route('supply.purchases') }}" class="psis-btn-outline ml-2">Back</a>
        </form>
    @else
        <p class="text-sm text-slate-500">This purchase is not ready for release (status: {{ str_replace('_', ' ', $purchase->status) }}).</p>
        <a href="{{ route('supply.purchases') }}" class="psis-btn-outline inline-flex mt-2">Back</a>
    @endif
</div>
@endsection
