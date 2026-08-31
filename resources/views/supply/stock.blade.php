@extends('layouts.psis')
@section('page-title', 'Stock Operations')
@section('content')
@php
    $sizes = config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']);
    $itemMeta = $items->mapWithKeys(fn ($item) => [
        $item->id => [
            'requires_size' => $item->requiresSize(),
            'sizes' => $item->sizeStocks->mapWithKeys(fn ($s) => [
                $s->size => [
                    'on_hand' => $s->quantity,
                    'available' => $s->availableQuantity(),
                ],
            ])->all(),
            'quantity' => $item->quantity,
        ],
    ]);
@endphp
<div
    class="grid lg:grid-cols-2 gap-6"
    x-data='{
        items: @json($itemMeta),
        stockInId: "",
        stockInSize: "",
        adjustId: "",
        adjustSize: "",
        get stockInNeedsSize() {
            return this.items[this.stockInId]?.requires_size ?? false;
        },
        get adjustNeedsSize() {
            return this.items[this.adjustId]?.requires_size ?? false;
        },
        sizeHint(id, size) {
            if (!id || !size) return "";
            const row = this.items[id]?.sizes?.[size];
            if (!row) return "No stock yet for this size — this will create it.";
            return row.on_hand + " on hand / " + row.available + " available";
        }
    }'
>
    <form method="POST" action="{{ route('supply.stock.in') }}" class="psis-card p-5 space-y-3">
        @csrf
        <h3 class="font-semibold">Stock In</h3>
        <select name="inventory_id" class="psis-input" required x-model="stockInId">
            <option value="">Select item</option>
            @foreach ($items as $item)
                <option value="{{ $item->id }}">{{ $item->item_name }}</option>
            @endforeach
        </select>
        <div x-show="stockInNeedsSize" x-cloak>
            <label class="psis-label">Size <span class="text-red-500">*</span></label>
            <select name="size" class="psis-input" x-model="stockInSize" :required="stockInNeedsSize">
                <option value="">Select size</option>
                @foreach ($sizes as $size)
                    <option value="{{ $size }}">{{ $size }}</option>
                @endforeach
            </select>
            <p class="text-xs text-slate-500 mt-1" x-text="sizeHint(stockInId, stockInSize) || 'Quantity is added to the selected size only.'"></p>
        </div>
        <input type="number" name="quantity" min="1" class="psis-input" placeholder="Quantity to add" required>
        <input name="notes" class="psis-input" placeholder="Notes">
        <button class="psis-btn-primary">Record Stock In</button>
    </form>

    <form method="POST" action="{{ route('supply.stock.adjust') }}" class="psis-card p-5 space-y-3">
        @csrf
        <h3 class="font-semibold">Inventory Adjustment</h3>
        <select name="inventory_id" class="psis-input" required x-model="adjustId">
            <option value="">Select item</option>
            @foreach ($items as $item)
                <option value="{{ $item->id }}">{{ $item->item_name }} (total {{ $item->quantity }})</option>
            @endforeach
        </select>
        <div x-show="adjustNeedsSize" x-cloak>
            <label class="psis-label">Size <span class="text-red-500">*</span></label>
            <select name="size" class="psis-input" x-model="adjustSize" :required="adjustNeedsSize">
                <option value="">Select size</option>
                @foreach ($sizes as $size)
                    <option value="{{ $size }}">{{ $size }}</option>
                @endforeach
            </select>
            <p class="text-xs text-slate-500 mt-1" x-text="sizeHint(adjustId, adjustSize) || 'Sets on-hand for that size. Available is on-hand minus reserved.'"></p>
        </div>
        <input type="number" name="new_quantity" min="0" class="psis-input" placeholder="New on-hand quantity" required>
        <input name="notes" class="psis-input" placeholder="Reason">
        <button class="psis-btn-secondary">Adjust</button>
    </form>
</div>
@endsection
