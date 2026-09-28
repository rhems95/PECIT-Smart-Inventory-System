@extends('layouts.psis')

@section('title', 'New Request')
@section('page-title', 'Submit Supply Request')

@section('content')
@include('partials.faculty-budget', ['budget' => $budget, 'budgetMode' => 'create'])
<form method="POST" action="{{ route('requests.store') }}" class="psis-card p-6 space-y-4 max-w-4xl">
    @csrf
    <div>
        <label class="psis-label">Purpose</label>
        <textarea name="purpose" class="psis-input" rows="3" required>{{ old('purpose') }}</textarea>
    </div>
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold">Items</h3>
            <button type="button" class="psis-btn-outline text-sm" id="add-request-line">Add line</button>
        </div>
        <div id="request-lines" class="space-y-3">
            @php
                $oldItems = old('items', [['inventory_id' => '', 'quantity' => 1]]);
            @endphp
            @foreach ($oldItems as $index => $oldLine)
            <div class="request-line grid md:grid-cols-3 gap-3 items-end">
                <div class="md:col-span-2">
                    <label class="psis-label">Inventory Item</label>
                    <select class="psis-input" name="items[{{ $index }}][inventory_id]" required>
                        <option value="">Select item</option>
                        @foreach ($inventory as $item)
                            <option value="{{ $item->id }}" data-price="{{ $item->unit_price }}" @selected((string) ($oldLine['inventory_id'] ?? '') === (string) $item->id)>{{ $item->item_name }} — ₱{{ number_format($item->unit_price, 2) }} (avail: {{ $item->availableQuantity() }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="psis-label">Quantity</label>
                    <input type="number" min="1" class="psis-input" name="items[{{ $index }}][quantity]" value="{{ $oldLine['quantity'] ?? 1 }}" required>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <p id="request-estimate" class="text-sm text-slate-500">Estimated total: ₱0.00</p>
    <button type="submit" class="psis-btn-primary">Submit Request</button>
</form>
@endsection

@push('scripts')
<script>
document.getElementById('add-request-line')?.addEventListener('click', function () {
    var wrap = document.getElementById('request-lines');
    var lines = wrap.querySelectorAll('.request-line');
    var first = lines[0];
    if (!first) return;
    var clone = first.cloneNode(true);
    var index = lines.length;
    clone.querySelectorAll('[name]').forEach(function (el) {
        el.name = el.name.replace(/items\[\d+]/, 'items[' + index + ']');
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
        if (el.type === 'number') el.value = '1';
    });
    wrap.appendChild(clone);
    bindEstimate();
    updateEstimate();
});

function lineTotal(line) {
    var select = line.querySelector('select');
    var qty = parseFloat(line.querySelector('input[type="number"]')?.value || '0');
    var price = parseFloat(select?.selectedOptions?.[0]?.getAttribute('data-price') || '0');
    return (qty > 0 ? qty : 0) * (price > 0 ? price : 0);
}

function updateEstimate() {
    var total = 0;
    document.querySelectorAll('.request-line').forEach(function (line) {
        total += lineTotal(line);
    });
    var el = document.getElementById('request-estimate');
    if (el) el.textContent = 'Estimated total: ₱' + total.toFixed(2);
}

function bindEstimate() {
    document.querySelectorAll('.request-line select, .request-line input[type="number"]').forEach(function (el) {
        el.removeEventListener('change', updateEstimate);
        el.removeEventListener('input', updateEstimate);
        el.addEventListener('change', updateEstimate);
        el.addEventListener('input', updateEstimate);
    });
}

bindEstimate();
updateEstimate();
</script>
@endpush
