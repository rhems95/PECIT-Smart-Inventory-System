@extends('layouts.psis')

@section('title', $purchase->purchase_number)
@section('page-title', 'Purchase '.$purchase->purchase_number)

@section('content')
<div class="space-y-4 max-w-3xl">
    <div class="psis-card p-5 text-sm space-y-1">
        <p>Status: <strong>{{ str_replace('_', ' ', $purchase->status) }}</strong></p>
        <p>Total: ₱{{ number_format($purchase->total_amount, 2) }}</p>
        @if ($payment = $purchase->payments->first())
            <p>Payment Ref: {{ $payment->reference_number }}</p>
            @if ($payment->receipt_path)
                <p>Receipt: <a href="{{ asset('storage/'.$payment->receipt_path) }}" target="_blank" class="text-pecit-blue dark:text-pecit-gold hover:underline">View uploaded receipt</a></p>
            @endif
        @endif
        <a href="{{ route('purchases.payment-slip', $purchase) }}" class="psis-btn-secondary inline-flex mt-2">Download Payment Slip (PDF)</a>
    </div>

    @if (auth()->user()->hasRole('Student') && in_array($purchase->status, ['payment_submitted', 'pending']))
    <div class="psis-card p-5">
        <h3 class="font-semibold mb-3">Upload Payment Receipt</h3>
        <p class="text-sm text-slate-500 mb-3">After paying at the accounting office, upload your receipt (JPG, PNG, or PDF, max 5MB).</p>
        <form method="POST" action="{{ route('purchases.receipt.upload', $purchase) }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" required class="block w-full text-sm">
            @error('receipt')<p class="text-red-500 text-sm">{{ $message }}</p>@enderror
            <button type="submit" class="psis-btn-primary">Upload Receipt</button>
        </form>
    </div>
    @endif

    <div class="psis-card overflow-hidden">
        <table class="min-w-full text-sm">
            @foreach ($purchase->items as $line)
                <tr class="border-t border-[var(--psis-border)]"><td class="px-4 py-3">{{ $line->inventory?->item_name }}</td><td class="px-4 py-3">{{ $line->quantity }} × ₱{{ number_format($line->unit_price,2) }}</td><td class="px-4 py-3 text-right">₱{{ number_format($line->subtotal,2) }}</td></tr>
            @endforeach
        </table>
    </div>
</div>
@endsection
