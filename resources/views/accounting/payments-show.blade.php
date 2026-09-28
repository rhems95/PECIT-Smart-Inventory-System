@extends('layouts.psis')
@section('page-title', 'Payment '.$purchase->purchase_number)
@section('content')
<div class="space-y-4 max-w-xl">
    <div class="psis-card p-5">
        <x-status-tracker :tracker="\App\Support\OrderStatusTracker::forPurchase($purchase)" />
    </div>
<div class="psis-card p-5 space-y-3 text-sm">
<p>Student: {{ $purchase->user?->name }}</p>
<p>Total: ₱{{ number_format($purchase->total_amount, 2) }}</p>
@if ($payment = $purchase->payments->first())
    <p>Reference: {{ $payment->reference_number }}</p>
    @if ($payment->receipt_path)
        <p>Receipt: <a href="{{ route('purchases.receipt.show', $purchase) }}" target="_blank" class="text-pecit-blue dark:text-pecit-gold hover:underline">View uploaded receipt</a></p>
    @else
        <p class="text-slate-500">No receipt uploaded yet.</p>
    @endif
@endif
@if ($purchase->status === 'payment_submitted')
<form method="POST" action="{{ route('accounting.payments.verify', $purchase) }}">@csrf<button class="psis-btn-primary">Verify Payment & Reserve Stock</button></form>
@endif
    </div>
</div>
@endsection
