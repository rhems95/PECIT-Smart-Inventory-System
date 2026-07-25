@php($item = $inventory ?? null)
<div class="grid md:grid-cols-2 gap-4">
    <div>
        <label class="psis-label">Item Code</label>
        <input name="item_code" value="{{ old('item_code', $item?->item_code) }}" class="psis-input" required>
    </div>
    <div>
        <label class="psis-label">Item Name</label>
        <input name="item_name" value="{{ old('item_name', $item?->item_name) }}" class="psis-input" required>
    </div>
    <div class="md:col-span-2">
        <label class="psis-label">Description</label>
        <textarea name="description" class="psis-input" rows="2">{{ old('description', $item?->description) }}</textarea>
    </div>
    <div>
        <label class="psis-label">Category</label>
        <select name="category_id" class="psis-input" required>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('category_id', $item?->category_id) == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="psis-label">Unit</label>
        <input name="unit" value="{{ old('unit', $item?->unit ?? 'piece') }}" class="psis-input" required>
    </div>
    <div>
        <label class="psis-label">Unit Price</label>
        <input type="number" step="0.01" name="unit_price" value="{{ old('unit_price', $item?->unit_price ?? 0) }}" class="psis-input" required>
    </div>
    @if (! $item)
        <div>
            <label class="psis-label">Initial Quantity</label>
            <input type="number" name="quantity" value="{{ old('quantity', 0) }}" class="psis-input" required>
        </div>
    @endif
    <div>
        <label class="psis-label">Minimum Stock</label>
        <input type="number" name="minimum_stock" value="{{ old('minimum_stock', $item?->minimum_stock ?? 10) }}" class="psis-input" required>
    </div>
    <div>
        <label class="psis-label">Supplier</label>
        <select name="supplier_id" class="psis-input">
            <option value="">—</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $item?->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="psis-label">Location</label>
        <input name="location" value="{{ old('location', $item?->location) }}" class="psis-input">
    </div>
</div>
