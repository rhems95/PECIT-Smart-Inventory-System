@extends('layouts.psis')

@section('title', 'Cart')
@section('page-title', 'Shopping Cart')

@section('content')
@php
    $total = 0;
@endphp
<div class="psis-card overflow-hidden max-w-3xl">
    <table class="min-w-full text-sm">
        <thead>
            <tr class="text-left bg-slate-50 dark:bg-slate-900/50">
                <th class="px-4 py-3">Item</th>
                <th class="px-4 py-3">Size</th>
                <th class="px-4 py-3">Qty</th>
                <th class="px-4 py-3">Subtotal</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
        @forelse ($cart as $lineKey => $line)
            @php
                $item = $items[$line['inventory_id']] ?? null;
            @endphp
            @if ($item)
                @php
                    $qty = (int) $line['quantity'];
                    $sub = $item->unit_price * $qty;
                    $total += $sub;
                @endphp
                <tr class="border-t border-[var(--psis-border)]">
                    <td class="px-4 py-3">{{ $item->item_name }}</td>
                    <td class="px-4 py-3">{{ $line['size'] ?? '—' }}</td>
                    <td class="px-4 py-3">
                        {{ $qty }}
                        <span class="block text-xs text-slate-500">
                            {{ $item->availableQuantity($line['size'] ?? null) }} avail
                            @if ($line['size']) for {{ $line['size'] }}@endif
                        </span>
                    </td>
                    <td class="px-4 py-3">₱{{ number_format($sub, 2) }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('shop.cart.remove', $lineKey) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600 text-xs">Remove</button>
                        </form>
                    </td>
                </tr>
            @endif
        @empty
            <tr>
                <td colspan="5" class="px-4 py-6 text-slate-500 text-center">Your cart is empty.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    <div class="p-4 flex items-center justify-between border-t border-[var(--psis-border)]">
        <strong>Total: ₱{{ number_format($total, 2) }}</strong>
        @if ($total > 0)
            <form method="POST" action="{{ route('purchases.checkout') }}">
                @csrf
                <button class="psis-btn-primary">Checkout</button>
            </form>
        @endif
    </div>
</div>
@endsection
