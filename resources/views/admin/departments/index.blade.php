@extends('layouts.psis')
@section('page-title', 'Departments')
@section('content')
<form method="POST" action="{{ route('admin.departments.store') }}" class="psis-card p-4 mb-4 grid md:grid-cols-5 gap-2">
    @csrf
    <input name="name" class="psis-input" placeholder="Name" required>
    <input name="code" class="psis-input" placeholder="Code" required>
    <input name="faculty_budget_limit" type="number" step="0.01" min="0" class="psis-input" placeholder="Budget / semester" value="10000">
    <button class="psis-btn-primary md:col-span-2">Add Department</button>
</form>

<div class="psis-card overflow-hidden divide-y divide-[var(--psis-border)]">
    @forelse ($departments as $dept)
        <div class="px-4 py-3" x-data="{ editing: false }">
            <div class="flex flex-wrap items-center justify-between gap-3" x-show="!editing">
                <div>
                    <span class="font-medium">{{ $dept->name }}</span>
                    <span class="text-xs text-slate-500">({{ $dept->code }})</span>
                    <span class="text-xs text-slate-400 ml-2">{{ $dept->users_count }} user(s) · {{ $dept->supply_requests_count }} request(s)</span>
                    <div class="text-xs text-slate-500 mt-1">
                        Faculty budget {{ $period['period_label'] }}:
                        ₱{{ number_format((float) ($dept->faculty_budget_used ?? 0), 2) }} used
                        of ₱{{ number_format((float) $dept->faculty_budget_limit, 2) }} / semester
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="psis-btn-outline text-sm" @click="editing = true">Edit</button>
                    <form method="POST" action="{{ route('admin.departments.destroy', $dept) }}" onsubmit="return confirm('Delete this department?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="psis-btn-outline text-sm text-red-600">Delete</button>
                    </form>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.departments.update', $dept) }}" class="grid md:grid-cols-5 gap-2" x-show="editing" x-cloak>
                @csrf
                @method('PUT')
                <input name="name" class="psis-input" value="{{ $dept->name }}" required>
                <input name="code" class="psis-input" value="{{ $dept->code }}" required>
                <input name="faculty_budget_limit" type="number" step="0.01" min="0" class="psis-input" value="{{ $dept->faculty_budget_limit }}" required>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="psis-btn-primary">Save</button>
                    <button type="button" class="psis-btn-outline" @click="editing = false">Cancel</button>
                </div>
            </form>
        </div>
    @empty
        <div class="px-4 py-6 text-sm text-slate-500">No departments yet.</div>
    @endforelse
</div>
{{ $departments->links() }}
@endsection
