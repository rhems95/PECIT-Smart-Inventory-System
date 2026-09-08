<div class="psis-card overflow-x-auto">
    <div class="flex items-center justify-between gap-3 p-5 pb-0">
        <h3 class="font-semibold">{{ $title ?? 'Recent verified payments' }}</h3>
        @isset($actionHref)
            <a href="{{ $actionHref }}" class="psis-btn-outline text-sm">{{ $actionLabel ?? 'Verify Payments' }}</a>
        @endisset
    </div>
    <table class="min-w-full text-sm">
        <thead>
            <tr class="text-left text-slate-500">
                <th class="px-4 py-3">Verified</th>
                <th class="px-4 py-3">Student</th>
                <th class="px-4 py-3">Department</th>
                <th class="px-4 py-3">Purchase #</th>
                <th class="px-4 py-3">Amount</th>
                <th class="px-4 py-3">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchases as $purchase)
                <tr class="border-t border-[var(--psis-border)]">
                    <td class="px-4 py-3 whitespace-nowrap">{{ $purchase->verified_at?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $purchase->user?->name ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $purchase->user?->department?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('accounting.payments.show', $purchase) }}" class="text-pecit-blue dark:text-pecit-gold hover:underline">{{ $purchase->purchase_number }}</a>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">₱{{ number_format($purchase->total_amount, 2) }}</td>
                    <td class="px-4 py-3">{{ str_replace('_', ' ', $purchase->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-slate-500">No verified payments yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
