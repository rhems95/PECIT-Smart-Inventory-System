@php
    $item = $inventory ?? null;
    $sizes = $sizes ?? config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']);
@endphp
<style>
    .psis-inventory-form .psis-uniform-size{display:none}
    .psis-inventory-form:has(#student-shop:checked) .psis-uniform-size{display:block}
</style>
<div
    class="grid md:grid-cols-2 gap-4"
    x-data="{
        studentShop: {{ old('student_shop', $item?->student_shop) ? 'true' : 'false' }},
        itemName: @js(old('item_name', $item?->item_name ?? '')),
        itemCode: @js(old('item_code', $item?->item_code ?? '')),
        get needsSize() {
            if (!this.studentShop) return false;
            const hay = (this.itemName + ' ' + this.itemCode).toLowerCase();
            return !hay.includes('lanyard');
        }
    }"
>
    <div>
        <label class="psis-label">Item Code</label>
        <input name="item_code" x-model="itemCode" value="{{ old('item_code', $item?->item_code) }}" class="psis-input" required>
    </div>
    <div>
        <label class="psis-label">Item Name</label>
        <input name="item_name" x-model="itemName" value="{{ old('item_name', $item?->item_name) }}" class="psis-input" required>
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
        <select name="unit_of_measurement_id" class="psis-input" required>
            <option value="">Select unit</option>
            @foreach ($units ?? [] as $unit)
                <option value="{{ $unit->id }}" @selected(old('unit_of_measurement_id', $item?->unit_of_measurement_id) == $unit->id)>{{ $unit->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="psis-label">Unit Price</label>
        <input type="number" step="0.01" name="unit_price" value="{{ old('unit_price', $item?->unit_price ?? 0) }}" class="psis-input" required>
    </div>
    @if (! $item)
        <div>
            <label class="psis-label">Initial Quantity</label>
            <input type="number" name="quantity" value="{{ old('quantity', 0) }}" class="psis-input" required>
            <p class="text-xs text-slate-500 mt-1 psis-uniform-size">Quantity applies to the selected size below.</p>
        </div>
        <div class="psis-uniform-size">
            <label class="psis-label">Uniform Size <span class="text-red-500">*</span></label>
            <select name="size" class="psis-input" x-bind:required="needsSize">
                <option value="">Select size</option>
                @foreach ($sizes as $size)
                    <option value="{{ $size }}" @selected(old('size') === $size)>{{ $size }}</option>
                @endforeach
            </select>
            @error('size')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
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
            <input type="checkbox" name="student_shop" id="student-shop" value="1" x-model="studentShop" @checked(old('student_shop', $item?->student_shop))>
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
    @if ($item)
        @php($stockBySize = $item->relationLoaded('sizeStocks') ? $item->sizeStocks->keyBy('size') : collect())
        <div class="md:col-span-2 psis-uniform-size">
            <p class="psis-label mb-2">On-hand by size</p>
            <p class="text-xs text-slate-500 mb-2">
                Change <strong>on-hand</strong> for each size, then save. <strong>Available</strong> is always on-hand minus reserved (orders waiting to be claimed) and cannot be typed in.
            </p>
            <div class="overflow-hidden rounded border border-[var(--psis-border)]">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 text-left">
                            <th class="px-4 py-3">Size</th>
                            <th class="px-4 py-3">On Hand</th>
                            <th class="px-4 py-3">Reserved</th>
                            <th class="px-4 py-3">Available</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sizes as $size)
                            @php($stock = $stockBySize->get($size))
                            <tr class="border-t border-[var(--psis-border)]">
                                <td class="px-4 py-3 font-medium">{{ $size }}</td>
                                <td class="px-4 py-3">
                                    <input
                                        type="number"
                                        min="0"
                                        name="size_quantities[{{ $size }}]"
                                        value="{{ old('size_quantities.'.$size, $stock?->quantity ?? 0) }}"
                                        class="psis-input w-24"
                                        x-bind:disabled="!needsSize"
                                    >
                                </td>
                                <td class="px-4 py-3 text-slate-500">{{ $stock?->reserved_quantity ?? 0 }}</td>
                                <td class="px-4 py-3">{{ $stock?->availableQuantity() ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Receiving a shipment? You can also add to one size under
                <a href="{{ route('supply.stock.index') }}" class="text-pecit-blue hover:underline">Stock Operations</a>.
            </p>
        </div>
    @endif
</div>
@once
@push('scripts')
<script>
(function () {
    var form = document.querySelector('.psis-inventory-form');
    if (!form) return;
    function syncUniformSize() {
        var shop = form.querySelector('#student-shop');
        var name = ((form.querySelector('[name="item_name"]') || {}).value || '') + ' ' + ((form.querySelector('[name="item_code"]') || {}).value || '');
        var shopOn = !!(shop && shop.checked);
        var needsSize = shopOn && name.toLowerCase().indexOf('lanyard') === -1;
        form.querySelectorAll('.psis-uniform-size').forEach(function (el) {
            el.style.display = shopOn ? 'block' : 'none';
        });
        var sel = form.querySelector('select[name="size"]');
        if (sel) sel.required = needsSize;
        form.querySelectorAll('input[name^="size_quantities"]').forEach(function (el) {
            el.disabled = !needsSize;
        });
    }
    form.addEventListener('change', syncUniformSize);
    form.addEventListener('input', syncUniformSize);
    syncUniformSize();
})();
</script>
@endpush
@endonce
