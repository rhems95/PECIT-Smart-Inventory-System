@extends('layouts.psis')
@section('page-title', 'Units of Measurement')
@section('content')
<div class="space-y-4">
    <form method="POST" action="{{ route('admin.units.store') }}" class="psis-card p-4 flex flex-wrap gap-2">
        @csrf
        <input name="name" class="psis-input" placeholder="Name (e.g. Piece)" required>
        <input name="symbol" class="psis-input max-w-xs" placeholder="Symbol (e.g. pcs)" required>
        <input name="description" class="psis-input" placeholder="Description">
        <button class="psis-btn-primary">Add unit</button>
    </form>
    <p class="text-xs text-slate-500">Units are labels only — there is no conversion between units in this version.</p>
    <div class="psis-card overflow-hidden divide-y divide-[var(--psis-border)]">
        @foreach ($units as $unit)
            <div class="px-4 py-3" x-data="{ editing: false }">
                <div class="flex flex-wrap items-center justify-between gap-3" x-show="!editing">
                    <div>
                        <span class="font-medium">{{ $unit->name }}</span>
                        <span class="text-xs text-slate-500">({{ $unit->symbol }})</span>
                        @if ($unit->description)
                            <span class="text-xs text-slate-400 ml-2">{{ $unit->description }}</span>
                        @endif
                    </div>
                    <button type="button" class="psis-btn-outline text-sm" @click="editing = true">Edit</button>
                </div>
                <form method="POST" action="{{ route('admin.units.update', $unit) }}" class="grid md:grid-cols-4 gap-2" x-show="editing" x-cloak>
                    @csrf
                    @method('PUT')
                    <input name="name" class="psis-input" value="{{ $unit->name }}" required>
                    <input name="symbol" class="psis-input" value="{{ $unit->symbol }}" required>
                    <input name="description" class="psis-input" value="{{ $unit->description }}">
                    <div class="flex gap-2">
                        <button class="psis-btn-primary">Save</button>
                        <button type="button" class="psis-btn-outline" @click="editing = false">Cancel</button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
    {{ $units->links() }}
</div>
@endsection
