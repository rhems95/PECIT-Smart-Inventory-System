@extends('layouts.psis')

@section('title', 'Student Shop')
@section('page-title', 'Supply Shop')

@section('content')
<form method="GET" class="mb-4"><input name="search" value="{{ request('search') }}" class="psis-input max-w-sm" placeholder="Search shop..."><button class="psis-btn-primary ml-2">Search</button></form>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ($items as $item)
        <div class="psis-card p-4 flex flex-col">
            <h3 class="font-semibold">{{ $item->item_name }}</h3>
            <p class="text-sm text-slate-500 mt-1">{{ $item->category?->name }}</p>
            <p class="text-lg font-bold text-pecit-blue dark:text-pecit-gold mt-2">₱{{ number_format($item->unit_price, 2) }}</p>
            <p class="text-xs text-slate-500">Available: {{ $item->availableQuantity() }} {{ $item->unit }}</p>
            @if ($item->availableQuantity() > 0)
                <form method="POST" action="{{ route('shop.cart.add') }}" class="mt-auto pt-3 flex gap-2">
                    @csrf
                    <input type="hidden" name="inventory_id" value="{{ $item->id }}">
                    <input type="number" name="quantity" value="1" min="1" max="{{ $item->availableQuantity() }}" class="psis-input w-20">
                    <button class="psis-btn-primary flex-1">Add to Cart</button>
                </form>
            @endif
        </div>
    @endforeach
</div>
<div class="mt-4 flex gap-3">{{ $items->links() }} <a href="{{ route('shop.cart') }}" class="psis-btn-secondary">View Cart ({{ count($cart) }})</a></div>
@endsection
