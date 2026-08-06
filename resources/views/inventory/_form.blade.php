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
        <label class="psis-label">Location</label>
        <input name="location" value="{{ old('location', $item?->location) }}" class="psis-input">
    </div>
    <div class="md:col-span-2">
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="student_shop" value="1" @checked(old('student_shop', $item?->student_shop))>
            Available in Uniform Shop (students)
        </label>
        <p class="text-xs text-slate-500 mt-1">
            <strong>Shared</strong> (leave department empty): P.E., NSTP, ID lanyard — all students can buy.<br>
            <strong>Exclusive</strong> (select a department): only that department’s students can buy; other departments cannot.
        </p>
    </div>
    <div>
        <label class="psis-label">Exclusive to department</label>
        <select name="department_id" class="psis-input">
            <option value="">Shared — all students (P.E. / NSTP / lanyard)</option>
            @foreach (($departments ?? []) as $dept)
                <option value="{{ $dept->id }}" @selected(old('department_id', $item?->department_id) == $dept->id)>{{ $dept->name }} ({{ $dept->code }})</option>
            @endforeach
        </select>
    </div>
</div>
