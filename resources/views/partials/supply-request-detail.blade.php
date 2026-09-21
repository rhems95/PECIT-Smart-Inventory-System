<div class="space-y-4">
    <div class="psis-card p-5">
        <x-status-tracker :tracker="\App\Support\OrderStatusTracker::forSupplyRequest($supplyRequest)" />
    </div>

    <div class="psis-card p-5 grid sm:grid-cols-2 gap-3 text-sm">
        <p><span class="text-slate-500">Status:</span> <strong>{{ str_replace('_', ' ', $supplyRequest->status) }}</strong></p>
        <p><span class="text-slate-500">Requester:</span> {{ $supplyRequest->user?->name ?? '—' }}</p>
        <p><span class="text-slate-500">Department:</span> {{ $supplyRequest->department?->name ?? $supplyRequest->user?->department?->name ?? '—' }}</p>
        <p><span class="text-slate-500">Total:</span> ₱{{ number_format($supplyRequest->total_amount, 2) }}</p>
        <p class="sm:col-span-2"><span class="text-slate-500">Purpose:</span> {{ $supplyRequest->purpose ?? '—' }}</p>
    </div>

    @include('partials.faculty-budget', ['budget' => $budget ?? null, 'budgetMode' => 'create'])

    <div class="psis-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900/50 text-left text-slate-500 dark:text-slate-400">
                    <th class="px-4 py-3 font-medium">Item</th>
                    <th class="px-4 py-3 font-medium">Requested</th>
                    <th class="px-4 py-3 font-medium">Approved</th>
                    <th class="px-4 py-3 font-medium">Released</th>
                    <th class="px-4 py-3 font-medium text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($supplyRequest->items as $line)
                    <tr class="border-t border-[var(--psis-border)]">
                        <td class="px-4 py-3">{{ $line->inventory?->item_name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $line->quantity_requested }}</td>
                        <td class="px-4 py-3">{{ $line->quantity_approved ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $line->quantity_released ?: '—' }}</td>
                        <td class="px-4 py-3 text-right">₱{{ number_format($line->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
