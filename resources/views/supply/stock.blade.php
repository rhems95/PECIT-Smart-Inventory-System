@extends('layouts.psis')
@section('page-title', 'Stock Operations')
@section('content')
<div class="grid lg:grid-cols-2 gap-6">
<form method="POST" action="{{ route('supply.stock.in') }}" class="psis-card p-5 space-y-3">@csrf<h3 class="font-semibold">Stock In</h3>
<select name="inventory_id" class="psis-input" required>@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->item_name }}</option>@endforeach</select>
<input type="number" name="quantity" min="1" class="psis-input" placeholder="Quantity" required>
<input name="notes" class="psis-input" placeholder="Notes">
<button class="psis-btn-primary">Record Stock In</button></form>
<form method="POST" action="{{ route('supply.stock.adjust') }}" class="psis-card p-5 space-y-3">@csrf<h3 class="font-semibold">Inventory Adjustment</h3>
<select name="inventory_id" class="psis-input" required>@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->item_name }} ({{ $item->quantity }})</option>@endforeach</select>
<input type="number" name="new_quantity" min="0" class="psis-input" placeholder="New quantity" required>
<input name="notes" class="psis-input" placeholder="Reason">
<button class="psis-btn-secondary">Adjust</button></form>
</div>
@endsection
