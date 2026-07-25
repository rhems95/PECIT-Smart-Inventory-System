@extends('layouts.psis')

@section('page-title', 'Users')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.create') }}" class="psis-btn-primary inline-flex">Add User</a>
</div>

<div class="psis-card overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/50 text-left text-slate-500 dark:text-slate-400">
                <th class="px-4 py-3 font-medium">Name</th>
                <th class="px-4 py-3 font-medium">Email</th>
                <th class="px-4 py-3 font-medium">Role</th>
                <th class="px-4 py-3 font-medium">Department</th>
                <th class="px-4 py-3 font-medium">Active</th>
                <th class="px-4 py-3 font-medium text-right">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr class="border-t border-[var(--psis-border)] hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                    <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                    <td class="px-4 py-3">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs bg-slate-100 dark:bg-slate-700">
                            {{ $user->getRoleNames()->first() ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">{{ $user->department?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if ($user->is_active)
                            <span class="text-green-600 dark:text-green-400">Yes</span>
                        @else
                            <span class="text-red-600 dark:text-red-400">No</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-pecit-blue dark:text-pecit-gold hover:underline font-medium">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $users->links() }}</div>
@endsection
