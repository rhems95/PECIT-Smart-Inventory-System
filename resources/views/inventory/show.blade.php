@extends('layouts.psis')

@section('title', $inventory->item_name)
@section('page-title', $inventory->item_name)

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 psis-card p-6 space-y-3">
        <p><span class="text-slate-500">Code:</span> {{ $inventory->item_code }}</p>
        <p><span class="text-slate-500">Category:</span> {{ $inventory->category?->name }}</p>
        <p><span class="text-slate-500">Description:</span> {{ $inventory->description ?: '—' }}</p>
        <p><span class="text-slate-500">On hand:</span> {{ $inventory->quantity }} {{ $inventory->unit }} (reserved: {{ $inventory->reserved_quantity }})</p>
        <p><span class="text-slate-500">Available:</span> {{ $inventory->availableQuantity() }}</p>
        <p><span class="text-slate-500">Unit price:</span> ₱{{ number_format($inventory->unit_price, 2) }}</p>
        <p><span class="text-slate-500">Location:</span> {{ $inventory->location ?: '—' }}</p>
        @if (auth()->user()->hasAnyRole(['Administrator', 'Supply Personnel']))
            <a href="{{ route('inventory.edit', $inventory) }}" class="psis-btn-outline inline-flex mt-2">Edit</a>
        @endif
    </div>
    <div class="psis-card p-6 text-center">
        <p class="text-sm text-slate-500 mb-3">Barcode / QR</p>
        <div class="inline-block">{!! $qrSvg !!}</div>
    </div>
</div>
@endsection
