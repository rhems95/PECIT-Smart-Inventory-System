@extends('layouts.psis')
@section('title', $supplier->name)
@section('page-title', $supplier->name)

@section('content')
<div class="space-y-4 max-w-4xl">
    <div class="psis-card p-6 space-y-2">
        <p><span class="text-slate-500">Code:</span> {{ $supplier->supplier_code }}</p>
        <p><span class="text-slate-500">Status:</span> {{ $supplier->is_active ? 'Active' : 'Inactive' }}</p>
        <p><span class="text-slate-500">Contact:</span> {{ $supplier->contact_person ?: '—' }}</p>
        <p><span class="text-slate-500">Phone:</span> {{ $supplier->phone ?: '—' }}</p>
        <p><span class="text-slate-500">Email:</span> {{ $supplier->email ?: '—' }}</p>
        <p><span class="text-slate-500">Address:</span> {{ $supplier->address ?: '—' }}</p>
        <a href="{{ route('admin.suppliers.index') }}" class="psis-btn-outline inline-flex mt-2">Back to suppliers</a>
    </div>
    <div class="psis-card overflow-hidden">
        <p class="px-4 py-3 font-medium">Recent stock movements</p>
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-900/50">
                <tr class="text-left">
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Item</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">In</th>
                    <th class="px-4 py-3">Out</th>
                    <th class="px-4 py-3">Reference</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($supplier->transactions as $row)
                    <tr class="border-t border-[var(--psis-border)]">
                        <td class="px-4 py-3">{{ optional($row->transaction_date ?? $row->created_at)->timezone('Asia/Manila')->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">{{ $row->inventory?->item_name }}</td>
                        <td class="px-4 py-3">{{ $row->typeLabel() }}</td>
                        <td class="px-4 py-3">{{ $row->quantity_in ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $row->quantity_out ?: '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $row->reference_number ?: $row->transaction_number }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-slate-500">No movements linked to this supplier.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
