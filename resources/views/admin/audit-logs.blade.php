@extends('layouts.psis')
@section('page-title', 'Audit Logs')
@section('content')
<div class="space-y-4">
    <p class="text-sm text-slate-500">Who changed what in PSIS. Technical errors stay in the Laravel log file.</p>
    <div class="psis-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-900/50">
                <tr class="text-left">
                    <th class="px-4 py-3">When</th>
                    <th class="px-4 py-3">Who</th>
                    <th class="px-4 py-3">Action</th>
                    <th class="px-4 py-3">Record</th>
                    <th class="px-4 py-3">Details</th>
                    <th class="px-4 py-3">IP</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($logs as $log)
                <tr class="border-t border-[var(--psis-border)]">
                    <td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at?->format('M d, Y g:i A') }}</td>
                    <td class="px-4 py-3">{{ $log->user?->name ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $log->actionLabel() }}</td>
                    <td class="px-4 py-3">{{ $log->recordLabel() }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $log->detailsSummary() }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $log->ip_address ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No audit entries yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</div>
@endsection
