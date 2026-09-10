@extends('layouts.psis')
@section('page-title', 'Suppliers')
@section('content')
<div class="space-y-4">
    <form method="POST" action="{{ route('admin.suppliers.store') }}" class="psis-card p-4 grid md:grid-cols-3 gap-3">
        @csrf
        <input name="supplier_code" class="psis-input" placeholder="Code (auto if empty)">
        <input name="name" class="psis-input" placeholder="Supplier name" required>
        <input name="contact_person" class="psis-input" placeholder="Contact person">
        <input name="phone" class="psis-input" placeholder="Phone">
        <input name="email" class="psis-input" placeholder="Email" type="email">
        <input name="address" class="psis-input md:col-span-3" placeholder="Address">
        <button class="psis-btn-primary">Add supplier</button>
    </form>

    <div class="psis-card overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-900/50">
                <tr class="text-left">
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Contact</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $supplier)
                    <tr class="border-t border-[var(--psis-border)]" x-data="{ editing: false }">
                        <td class="px-4 py-3 font-mono text-xs" x-show="!editing">{{ $supplier->supplier_code }}</td>
                        <td class="px-4 py-3" x-show="!editing">{{ $supplier->name }}</td>
                        <td class="px-4 py-3" x-show="!editing">{{ $supplier->contact_person ?: '—' }} {{ $supplier->phone ? '· '.$supplier->phone : '' }}</td>
                        <td class="px-4 py-3" x-show="!editing">{{ $supplier->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap" x-show="!editing">
                            <a href="{{ route('admin.suppliers.show', $supplier) }}" class="text-pecit-blue hover:underline">View</a>
                            <button type="button" class="text-pecit-blue hover:underline ml-3" @click="editing = true">Edit</button>
                            <form method="POST" action="{{ route('admin.suppliers.toggle', $supplier) }}" class="inline ml-3">
                                @csrf
                                <button type="submit" class="text-slate-600 hover:underline">{{ $supplier->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                        </td>
                        <td colspan="5" class="px-4 py-3" x-show="editing" x-cloak>
                            <form method="POST" action="{{ route('admin.suppliers.update', $supplier) }}" class="grid md:grid-cols-3 gap-2">
                                @csrf
                                @method('PUT')
                                <input name="supplier_code" class="psis-input" value="{{ $supplier->supplier_code }}" required>
                                <input name="name" class="psis-input" value="{{ $supplier->name }}" required>
                                <input name="contact_person" class="psis-input" value="{{ $supplier->contact_person }}">
                                <input name="phone" class="psis-input" value="{{ $supplier->phone }}">
                                <input name="email" class="psis-input" value="{{ $supplier->email }}">
                                <input name="address" class="psis-input" value="{{ $supplier->address }}">
                                <div class="flex gap-2">
                                    <button class="psis-btn-primary">Save</button>
                                    <button type="button" class="psis-btn-outline" @click="editing = false">Cancel</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-slate-500">No suppliers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $suppliers->links() }}
</div>
@endsection
