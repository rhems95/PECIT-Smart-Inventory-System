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
            'available' => $item->availableQuantity(),
        ],
    ]);
@endphp
<div
    class="space-y-4"
    x-data='{
        tab: "in",
        items: @json($itemMeta),
        itemId: @json((string) request('item', '')),
        size: "",
        source: "manual_external",
        get needsSize() {
            return this.items[this.itemId]?.requires_size ?? false;
        },
        sizeHint() {
            if (!this.itemId || !this.size) return "";
            const row = this.items[this.itemId]?.sizes?.[this.size];
            if (!row) return "No stock yet for this size — stock-in will create it.";
            return row.on_hand + " on hand / " + row.available + " available";
        }
    }'
>
    <p class="text-sm text-slate-500">Physical stock only. Approving a request still <strong>reserves</strong> stock; this page is for receiving, counting, damage, and returns. Releases happen on Release Items / Student Purchases.</p>

    <div class="flex flex-wrap gap-2">
        <button type="button" class="psis-btn-outline" :class="tab === 'in' && 'ring-2 ring-pecit-blue'" @click="tab = 'in'">Stock In</button>
        <button type="button" class="psis-btn-outline" :class="tab === 'out' && 'ring-2 ring-pecit-blue'" @click="tab = 'out'">Stock Out</button>
        <button type="button" class="psis-btn-outline" :class="tab === 'adjust' && 'ring-2 ring-pecit-blue'" @click="tab = 'adjust'">Adjustment</button>
        <button type="button" class="psis-btn-outline" :class="tab === 'damage' && 'ring-2 ring-pecit-blue'" @click="tab = 'damage'">Damage</button>
        <button type="button" class="psis-btn-outline" :class="tab === 'bad' && 'ring-2 ring-pecit-blue'" @click="tab = 'bad'">Bad Order</button>
        <button type="button" class="psis-btn-outline" :class="tab === 'return' && 'ring-2 ring-pecit-blue'" @click="tab = 'return'">Return to Supplier</button>
        <a href="{{ route('admin.suppliers.index') }}" class="psis-btn-outline">Suppliers</a>
        <a href="{{ route('supply.purchase-history') }}" class="psis-btn-outline">Purchase History</a>
    </div>

    <form method="POST" action="{{ route('supply.stock.in') }}" class="psis-card p-5 space-y-3" x-show="tab === 'in'" x-cloak>
        @csrf
        <h3 class="font-semibold">Stock In</h3>
        <select name="inventory_id" class="psis-input" required x-model="itemId">
            <option value="">Select item</option>
            @foreach ($items as $item)
                <option value="{{ $item->id }}" @selected((string) request('item') === (string) $item->id)>{{ $item->item_name }} ({{ $item->item_code }})</option>
            @endforeach
        </select>
        <div x-show="needsSize">
            <label class="psis-label">Size <span class="text-red-500">*</span></label>
            <select name="size" class="psis-input" x-model="size" :required="needsSize">
                <option value="">Select size</option>
                @foreach ($sizes as $size)
                    <option value="{{ $size }}">{{ $size }}</option>
                @endforeach
            </select>
            <p class="text-xs text-slate-500 mt-1" x-text="sizeHint()"></p>
        </div>
        <label class="psis-label">Source</label>
        <select name="source_type" class="psis-input" required x-model="source">
            @foreach ($sources as $source)
                <option value="{{ $source->value }}">{{ $source->label() }}</option>
            @endforeach
        </select>
        <div x-show="source !== 'donation' && source !== 'opening_balance'">
            <label class="psis-label">Supplier <span class="text-red-500" x-show="source === 'purchase_order'">*</span></label>
            <select name="supplier_id" class="psis-input" :required="source === 'purchase_order'">
                <option value="">None / not applicable</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->supplier_code }})</option>
                @endforeach
            </select>
        </div>
        <div x-show="source === 'purchase_order' || source === 'emergency_purchase' || source === 'manual_external' || source === 'other'">
            <label class="psis-label">Reference / PO number <span class="text-red-500" x-show="source === 'purchase_order' || source === 'emergency_purchase'">*</span></label>
            <input name="reference_number" class="psis-input" placeholder="PO-001 or external document no.">
        </div>
        <div x-show="source === 'purchase_order'">
            <label class="psis-label">Delivery receipt number</label>
            <input name="delivery_receipt_number" class="psis-input" placeholder="DR number">
        </div>
        <input type="number" name="quantity" min="0.0001" step="any" class="psis-input" placeholder="Quantity to add" required>
        <div x-show="source === 'purchase_order' || source === 'emergency_purchase' || source === 'manual_external' || source === 'other'">
            <label class="psis-label">Bought by</label>
            <select name="purchased_by" class="psis-input">
                @foreach ($buyers as $buyer)
                    <option value="{{ $buyer->id }}" @selected($buyer->id === auth()->id())>
                        {{ $buyer->name }}@if ($buyer->department) — {{ $buyer->department->code }}@endif
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-slate-500 mt-1">Staff member who bought this item. Shown on Purchase History.</p>
        </div>
        <input type="number" step="0.01" min="0" name="unit_cost" class="psis-input" placeholder="Unit cost (optional)">
        <input name="notes" class="psis-input" placeholder="Remarks">
        <button class="psis-btn-primary">Record Stock In</button>
    </form>

    <form method="POST" action="{{ route('supply.stock.out') }}" class="psis-card p-5 space-y-3" x-show="tab === 'out'" x-cloak>
        @csrf
        <h3 class="font-semibold">Stock Out</h3>
        <p class="text-xs text-slate-500">Manual removal that is not a faculty/student release. Reason is required.</p>
        @include('supply._stock-deduct-fields', ['requireSupplier' => false, 'showDr' => false])
        <button class="psis-btn-primary">Record Stock Out</button>
    </form>

    <form method="POST" action="{{ route('supply.stock.adjust') }}" class="psis-card p-5 space-y-3" x-show="tab === 'adjust'" x-cloak>
        @csrf
        <h3 class="font-semibold">Quantity adjustment</h3>
        <p class="text-xs text-slate-500">Physical count, found, or lost items. Sets the new on-hand quantity. Selling price is edited on the item, not here.</p>
        <select name="inventory_id" class="psis-input" required x-model="itemId">
            <option value="">Select item</option>
            @foreach ($items as $item)
                <option value="{{ $item->id }}">{{ $item->item_name }} (on hand {{ $item->quantity }})</option>
            @endforeach
        </select>
        <div x-show="needsSize">
            <label class="psis-label">Size <span class="text-red-500">*</span></label>
            <select name="size" class="psis-input" x-model="size" :required="tab === 'adjust' && needsSize">
                <option value="">Select size</option>
                @foreach ($sizes as $size)
                    <option value="{{ $size }}">{{ $size }}</option>
                @endforeach
            </select>
            <p class="text-xs text-slate-500 mt-1" x-text="sizeHint() || 'Sets on-hand for that size.'"></p>
        </div>
        <input type="number" name="new_quantity" min="0" step="any" class="psis-input" placeholder="New on-hand quantity" required>
        <input name="notes" class="psis-input" placeholder="Reason (required)" required>
        <button class="psis-btn-secondary">Adjust</button>
    </form>

    <form method="POST" action="{{ route('supply.stock.damage') }}" class="psis-card p-5 space-y-3" x-show="tab === 'damage'" x-cloak>
        @csrf
        <h3 class="font-semibold">Damage</h3>
        <p class="text-xs text-slate-500">Unusable stock is deducted from on-hand. Reserved orders are not touched.</p>
        @include('supply._stock-deduct-fields', ['requireSupplier' => false, 'showDr' => false])
        <button class="psis-btn-primary">Record Damage</button>
    </form>

    <form method="POST" action="{{ route('supply.stock.bad-order') }}" class="psis-card p-5 space-y-3" x-show="tab === 'bad'" x-cloak>
        @csrf
        <h3 class="font-semibold">Bad order</h3>
        <p class="text-xs text-slate-500">Incorrect or defective delivery already in stock. Deducts only the defective quantity.</p>
        @include('supply._stock-deduct-fields', ['requireSupplier' => false, 'showDr' => true])
        <button class="psis-btn-primary">Record Bad Order</button>
    </form>

    <form method="POST" action="{{ route('supply.stock.return') }}" class="psis-card p-5 space-y-3" x-show="tab === 'return'" x-cloak>
        @csrf
        <h3 class="font-semibold">Return to supplier</h3>
        <p class="text-xs text-slate-500">Items physically leave PECIT. Only the returned quantity is deducted (e.g. 10 received, 2 returned).</p>
        @include('supply._stock-deduct-fields', ['requireSupplier' => true, 'showDr' => true])
        <button class="psis-btn-primary">Record Return</button>
    </form>
</div>
@endsection
