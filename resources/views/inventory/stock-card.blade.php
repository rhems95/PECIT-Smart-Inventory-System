@extends('layouts.psis')

@section('title', 'Stock Card — '.$inventory->item_name)
@section('page-title', 'Stock Card')

@section('content')
<div class="space-y-4">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="font-semibold">{{ $inventory->item_name }}</p>
            <p class="text-sm text-slate-500 font-mono">{{ $inventory->item_code }} · Unit: {{ $inventory->unitLabel() }}</p>
            <p class="text-sm mt-1">On hand {{ \App\Support\Qty::format($inventory->quantity) }} · Reserved {{ \App\Support\Qty::format($inventory->reserved_quantity) }} · Available {{ \App\Support\Qty::format($inventory->availableQuantity()) }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('inventory.show', $inventory) }}" class="psis-btn-outline">Item details</a>
            @can('update', $inventory)
                <a href="{{ route('supply.stock.index') }}" class="psis-btn-outline">Stock Operations</a>
            @endcan
        </div>
    </div>

    <form method="GET" class="psis-card p-4 flex flex-wrap gap-2 items-end">
        <div>
            <label class="psis-label">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="psis-input">
        </div>
        <div>
            <label class="psis-label">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="psis-input">
        </div>
        <div>
            <label class="psis-label">Type</label>
            <select name="type" class="psis-input">
                <option value="">All physical</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="psis-label">Supplier</label>
            <select name="supplier_id" class="psis-input">
                <option value="">All</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="psis-label">Reference</label>
            <input type="search" name="reference" value="{{ request('reference') }}" class="psis-input" placeholder="PO / DR / TXN">
        </div>
        <button class="psis-btn-primary">Filter</button>
        <a href="{{ route('inventory.stock-card', $inventory) }}" class="psis-btn-outline">Reset</a>
    </form>

    <p class="text-xs text-slate-500">Stock Card shows physical movements only (in, out, release, adjustment, damage, return). Reservations are not stock-out.</p>

    <div class="psis-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-900/50">
                <tr class="text-left">
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Transaction Type</th>
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Supplier / Source</th>
                    <th class="px-4 py-3">Remarks</th>
                    <th class="px-4 py-3">Stock In</th>
                    <th class="px-4 py-3">Stock Out</th>
                    <th class="px-4 py-3">Running Balance</th>
                    <th class="px-4 py-3">Unit Cost</th>
                    <th class="px-4 py-3">Total Cost</th>
                    <th class="px-4 py-3">Performed By</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($entries as $row)
                    <tr class="border-t border-[var(--psis-border)]">
                        <td class="px-4 py-3 whitespace-nowrap">{{ optional($row->transaction_date ?? $row->created_at)->timezone('Asia/Manila')->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3">
                            {{ $row->typeLabel() }}
                            @if ($row->size)
                                <span class="text-slate-500">({{ $row->size }})</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-xs">
                            {{ $row->reference_number ?: $row->transaction_number }}
                            @if ($row->delivery_receipt_number)
                                <div class="text-slate-500">DR {{ $row->delivery_receipt_number }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            {{ $row->supplier?->name ?: '—' }}
                            @if ($row->source_type)
                                <div class="text-xs text-slate-500">{{ $row->source_type->label() }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $row->notes ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $row->quantity_in ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $row->quantity_out ?: '—' }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $row->runningBalance() }}</td>
                        <td class="px-4 py-3">{{ $row->unit_cost !== null ? '₱'.number_format($row->unit_cost, 2) : '—' }}</td>
                        <td class="px-4 py-3">{{ $row->total_cost !== null ? '₱'.number_format($row->total_cost, 2) : '—' }}</td>
                        <td class="px-4 py-3">{{ $row->performer?->name ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-6 text-slate-500">No physical stock movements yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $entries->links() }}
</div>
@endsection
