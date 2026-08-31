@extends('layouts.psis')

@section('title', $inventory->item_name)
@section('page-title', $inventory->item_name)

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 psis-card p-6 space-y-3">
        <p><span class="text-slate-500">Code:</span> {{ $inventory->item_code }}</p>
        <p><span class="text-slate-500">Category:</span> {{ $inventory->category?->name }}</p>
        <p><span class="text-slate-500">Student shop:</span> {{ $inventory->student_shop ? 'Yes' : 'No' }}</p>
        <p><span class="text-slate-500">Shop access:</span>
            @if (! $inventory->student_shop)
                —
            @elseif ($inventory->department)
                Exclusive to {{ $inventory->department->name }} only
            @else
                Shared (all students — e.g. P.E. / NSTP / lanyard)
            @endif
        </p>
        <p><span class="text-slate-500">Description:</span> {{ $inventory->description ?: '—' }}</p>
        <p><span class="text-slate-500">On hand:</span> {{ $inventory->quantity }} {{ $inventory->unit }} (reserved: {{ $inventory->reserved_quantity }})</p>
        <p><span class="text-slate-500">Available:</span> {{ $inventory->availableQuantity() }}</p>
        @if ($inventory->requiresSize())
            @php($stockBySize = $inventory->sizeStocks->keyBy('size'))
            @php($sizes = config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']))
            <div class="pt-2">
                <p class="text-slate-500 mb-2">Stock by size</p>
                <p class="text-xs text-slate-500 mb-2">Available = on-hand − reserved. Edit on-hand per size on the Edit page.</p>
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
                                    <td class="px-4 py-3">{{ $size }}</td>
                                    <td class="px-4 py-3">{{ $stock?->quantity ?? 0 }}</td>
                                    <td class="px-4 py-3">{{ $stock?->reserved_quantity ?? 0 }}</td>
                                    <td class="px-4 py-3">{{ $stock?->availableQuantity() ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        <p><span class="text-slate-500">Unit price:</span> ₱{{ number_format($inventory->unit_price, 2) }}</p>
        <p><span class="text-slate-500">Location:</span> {{ $inventory->location ?: '—' }}</p>
        @if (auth()->user()->hasAnyRole(['Administrator', 'Supply Personnel']))
            <div class="flex flex-wrap gap-2 mt-2">
                <a href="{{ route('inventory.edit', $inventory) }}" class="psis-btn-outline inline-flex">Edit item &amp; size stock</a>
                <a href="{{ route('supply.stock.index') }}" class="psis-btn-outline inline-flex">Stock Operations</a>
            </div>
        @endif
    </div>
    <div class="psis-card p-6 text-center">
        <p class="text-sm text-slate-500 mb-3">Barcode / QR</p>
        <div class="inline-block">{!! $qrSvg !!}</div>
    </div>
</div>
@endsection
