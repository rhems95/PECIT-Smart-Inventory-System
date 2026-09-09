@extends('layouts.psis')

@section('title', 'Inventory')
@section('page-title', 'Inventory')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search items..." class="psis-input max-w-xs">
            <select name="category_id" class="psis-input max-w-xs">
                <option value="">All categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="status" class="psis-input max-w-xs">
                <option value="">All statuses</option>
                <option value="available" @selected(request('status') === 'available')>Available</option>
                <option value="low_stock" @selected(request('status') === 'low_stock')>Low stock</option>
                <option value="out_of_stock" @selected(request('status') === 'out_of_stock')>Out of stock</option>
            </select>
            <button class="psis-btn-primary">Filter</button>
        </form>
        @if (auth()->user()->hasAnyRole(['Administrator', 'Supply Personnel']))
            <a href="{{ route('inventory.create') }}" class="psis-btn-secondary">Add Item</a>
        @endif
    </div>

    <div class="psis-card overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-900/50">
                <tr class="text-left">
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">Item</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">On Hand</th>
                    <th class="px-4 py-3">Reserved</th>
                    <th class="px-4 py-3">Available</th>
                    <th class="px-4 py-3">Price</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($items as $item)
                <tr class="border-t border-[var(--psis-border)] hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                    <td class="px-4 py-3 font-mono text-xs">{{ $item->item_code }}</td>
                    <td class="px-4 py-3 font-medium">{{ $item->item_name }}</td>
                    <td class="px-4 py-3">{{ $item->category?->name }}</td>
                    <td class="px-4 py-3 font-semibold">{{ $item->quantity }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $item->reserved_quantity }}</td>
                    <td class="px-4 py-3">{{ $item->availableQuantity() }} {{ $item->unit }}</td>
                    <td class="px-4 py-3">₱{{ number_format($item->unit_price, 2) }}</td>
                    <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full @if($item->status==='low_stock') bg-amber-100 text-amber-800 @elseif($item->status==='out_of_stock') bg-red-100 text-red-800 @else bg-green-100 text-green-800 @endif">{{ str_replace('_',' ', $item->status) }}</span></td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('inventory.show', $item) }}" class="text-pecit-blue hover:underline">View</a>
                        @can('delete', $item)
                            <form method="POST" action="{{ route('inventory.destroy', $item) }}" class="inline ml-3" onsubmit="return confirm('Delete this item? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $items->links() }}
</div>
@endsection
