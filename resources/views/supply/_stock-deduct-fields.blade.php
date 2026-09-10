@php
    $sizes = $sizes ?? config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']);
    $requireSupplier = $requireSupplier ?? false;
    $showDr = $showDr ?? false;
@endphp
<select name="inventory_id" class="psis-input" required x-model="itemId">
    <option value="">Select item</option>
    @foreach ($items as $item)
        <option value="{{ $item->id }}">{{ $item->item_name }} (avail {{ $item->availableQuantity() }})</option>
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
@if ($requireSupplier)
    <label class="psis-label">Supplier <span class="text-red-500">*</span></label>
    <select name="supplier_id" class="psis-input" required>
        <option value="">Select supplier</option>
        @foreach ($suppliers as $supplier)
            <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->supplier_code }})</option>
        @endforeach
    </select>
@else
    <label class="psis-label">Supplier (optional)</label>
    <select name="supplier_id" class="psis-input">
        <option value="">None</option>
        @foreach ($suppliers as $supplier)
            <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->supplier_code }})</option>
        @endforeach
    </select>
@endif
<input name="reference_number" class="psis-input" placeholder="Reference number (optional)">
@if ($showDr)
    <input name="delivery_receipt_number" class="psis-input" placeholder="Delivery receipt number (optional)">
@endif
<input type="number" name="quantity" min="1" class="psis-input" placeholder="Quantity" required>
<input name="notes" class="psis-input" placeholder="Reason / remarks (required)" required>
