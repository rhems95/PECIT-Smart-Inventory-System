<div class="psis-card overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/50 text-left text-slate-500 dark:text-slate-400">
                <th class="px-4 py-3 font-medium">Number</th>
                <th class="px-4 py-3 font-medium">Requester</th>
                <th class="px-4 py-3 font-medium">Department</th>
                <th class="px-4 py-3 font-medium">Status</th>
                <th class="px-4 py-3 font-medium">Date</th>
                <th class="px-4 py-3 font-medium text-right">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($requests as $req)
                <tr class="border-t border-[var(--psis-border)] hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                    <td class="px-4 py-3 whitespace-nowrap font-medium">{{ $req->request_number }}</td>
                    <td class="px-4 py-3">{{ $req->user?->name ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $req->department?->name ?? $req->user?->department?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs bg-slate-100 dark:bg-slate-700">
                            {{ str_replace('_', ' ', $req->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ $req->created_at?->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route($showRoute, $req) }}" class="text-pecit-blue dark:text-pecit-gold hover:underline font-medium">
                            {{ $actionLabel ?? 'Open' }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No requests found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if ($requests instanceof \Illuminate\Contracts\Pagination\Paginator)
    <div class="mt-4">{{ $requests->links() }}</div>
@endif
