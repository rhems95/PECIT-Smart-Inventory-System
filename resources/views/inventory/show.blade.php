@extends('layouts.psis')

@section('title', $inventory->item_name)
@section('page-title', $inventory->item_name)

@section('content')
<div class="space-y-4">
    <div class="psis-card p-6 space-y-6">
        <dl class="grid sm:grid-cols-2 xl:grid-cols-3 gap-x-8 gap-y-4">
            <div>
                <dt class="text-slate-500 text-sm">Code</dt>
                <dd class="mt-0.5 font-mono">{{ $inventory->item_code }}</dd>
            </div>
            <div>
                <dt class="text-slate-500 text-sm">Category</dt>
                <dd class="mt-0.5">{{ $inventory->category?->name ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500 text-sm">Student shop</dt>
                <dd class="mt-0.5">{{ $inventory->student_shop ? 'Yes' : 'No' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500 text-sm">Shop access</dt>
                <dd class="mt-0.5">
                    @if (! $inventory->student_shop)
                        —
                    @elseif ($inventory->department)
                        Exclusive to {{ $inventory->department->name }} only
                    @else
                        Shared (all students — e.g. P.E. / NSTP / lanyard)
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-slate-500 text-sm">On hand</dt>
                <dd class="mt-0.5">{{ $inventory->quantity }} {{ $inventory->unit }} (reserved: {{ $inventory->reserved_quantity }})</dd>
            </div>
            <div>
                <dt class="text-slate-500 text-sm">Available</dt>
                <dd class="mt-0.5">{{ $inventory->availableQuantity() }}</dd>
            </div>
            <div>
                <dt class="text-slate-500 text-sm">Unit price</dt>
                <dd class="mt-0.5">₱{{ number_format($inventory->unit_price, 2) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500 text-sm">Location</dt>
                <dd class="mt-0.5">{{ $inventory->location ?: '—' }}</dd>
            </div>
            <div class="sm:col-span-2 xl:col-span-3">
                <dt class="text-slate-500 text-sm">Description</dt>
                <dd class="mt-0.5">{{ $inventory->description ?: '—' }}</dd>
            </div>
        </dl>

        @if ($inventory->requiresSize())
            @php($stockBySize = $inventory->sizeStocks->keyBy('size'))
            @php($sizes = config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']))
            <div>
                <p class="text-slate-500 mb-1">Stock by size</p>
                <p class="text-xs text-slate-500 mb-3">Available = on-hand − reserved. Edit on-hand per size on the Edit page.</p>
                <div class="overflow-x-auto rounded border border-[var(--psis-border)]">
                    <table class="w-full table-fixed text-sm">
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

        @can('update', $inventory)
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('inventory.edit', $inventory) }}" class="psis-btn-outline inline-flex">Edit item &amp; size stock</a>
                <a href="{{ route('supply.stock.index') }}" class="psis-btn-outline inline-flex">Stock Operations</a>
                @can('delete', $inventory)
                    <form method="POST" action="{{ route('inventory.destroy', $inventory) }}" onsubmit="return confirm('Delete this item? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="psis-btn-outline inline-flex text-red-600">Delete item</button>
                    </form>
                @endcan
            </div>
        @endcan
    </div>
</div>
@endsection
