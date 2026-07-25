@extends('layouts.psis')

@section('title', 'New Request')
@section('page-title', 'Submit Supply Request')

@section('content')
<form method="POST" action="{{ route('requests.store') }}" class="psis-card p-6 space-y-4 max-w-4xl" x-data="{ rows: [{ inventory_id: '', quantity: 1 }] }">
    @csrf
    <div>
        <label class="psis-label">Purpose</label>
        <textarea name="purpose" class="psis-input" rows="3" required>{{ old('purpose') }}</textarea>
    </div>
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold">Items</h3>
            <button type="button" class="psis-btn-outline text-sm" @click="rows.push({ inventory_id: '', quantity: 1 })">Add line</button>
        </div>
        <template x-for="(row, index) in rows" :key="index">
            <div class="grid md:grid-cols-3 gap-3 items-end">
                <div class="md:col-span-2">
                    <label class="psis-label">Inventory Item</label>
                    <select class="psis-input" :name="`items[${index}][inventory_id]`" x-model="row.inventory_id" required>
                        <option value="">Select item</option>
                        @foreach ($inventory as $item)
                            <option value="{{ $item->id }}">{{ $item->item_name }} (avail: {{ $item->availableQuantity() }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="psis-label">Quantity</label>
                    <input type="number" min="1" class="psis-input" :name="`items[${index}][quantity]`" x-model="row.quantity" required>
                </div>
            </div>
        </template>
    </div>
    <button type="submit" class="psis-btn-primary">Submit Request</button>
</form>
@endsection
