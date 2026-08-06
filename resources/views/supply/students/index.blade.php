@extends('layouts.psis')

@section('title', 'Students — PSIS')
@section('page-title', 'Students')

@section('content')
<div class="mb-4 flex flex-wrap gap-2">
    <a href="{{ route('supply.students.create') }}" class="psis-btn-primary inline-flex">Add Student</a>
    <a href="{{ route('supply.students.import') }}" class="psis-btn-outline inline-flex">Import CSV</a>
</div>

<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input name="search" value="{{ request('search') }}" class="psis-input max-w-sm" placeholder="Search name, ID, or email...">
    <button class="psis-btn-primary">Search</button>
</form>

<div class="psis-card overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/50 text-left text-slate-500 dark:text-slate-400">
                <th class="px-4 py-3 font-medium">Student ID</th>
                <th class="px-4 py-3 font-medium">Name</th>
                <th class="px-4 py-3 font-medium">Last Name</th>
                <th class="px-4 py-3 font-medium">Email</th>
                <th class="px-4 py-3 font-medium">Department</th>
                <th class="px-4 py-3 font-medium">Active</th>
                <th class="px-4 py-3 font-medium text-right">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
                <tr class="border-t border-[var(--psis-border)] hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                    <td class="px-4 py-3 font-medium">{{ $student->employee_id }}</td>
                    <td class="px-4 py-3">{{ $student->name }}</td>
                    <td class="px-4 py-3">{{ $student->last_name }}</td>
                    <td class="px-4 py-3">{{ $student->email }}</td>
                    <td class="px-4 py-3">{{ $student->department?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if ($student->is_active)
                            <span class="text-green-600 dark:text-green-400">Yes</span>
                        @else
                            <span class="text-red-600 dark:text-red-400">No</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('supply.students.edit', $student) }}" class="text-pecit-blue dark:text-pecit-gold hover:underline font-medium">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">No students found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $students->links() }}</div>
@endsection
