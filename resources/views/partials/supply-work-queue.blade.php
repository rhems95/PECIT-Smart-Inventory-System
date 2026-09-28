@php
    $readyFacultyReleases = $readyFacultyReleases ?? collect();
    $readyStudentReleases = $readyStudentReleases ?? collect();
    $pendingInspections = $pendingInspections ?? collect();
    $actionableLowStock = $actionableLowStock ?? collect();
    $oldestFaculty = $readyFacultyReleases->first();
    $oldestStudent = $readyStudentReleases->first();
@endphp
<div id="ready-to-release" class="space-y-4 scroll-mt-24">
    <div class="psis-card p-5">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-2">
            <div>
                <h3 class="font-semibold">Ready to release</h3>
                <p class="text-sm text-slate-500 mt-1">Oldest approved faculty requests and verified student purchases wait at the top. Pending requests stay with Accounting or Admin.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('supply.releases') }}" class="psis-btn-outline text-sm">Release Items</a>
                <a href="{{ route('supply.purchases') }}" class="psis-btn-outline text-sm">Student Purchases</a>
            </div>
        </div>
        @if ($oldestFaculty || $oldestStudent)
            <p class="text-xs text-slate-500 mb-4">
                Oldest waiting:
                @if ($oldestFaculty)
                    faculty {{ $oldestFaculty->request_number }}
                    ({{ ($oldestFaculty->approved_at ?? $oldestFaculty->created_at)?->diffForHumans() }})
                @endif
                @if ($oldestFaculty && $oldestStudent)
                    ·
                @endif
                @if ($oldestStudent)
                    student {{ $oldestStudent->purchase_number }}
                    ({{ ($oldestStudent->verified_at ?? $oldestStudent->created_at)?->diffForHumans() }})
                @endif
            </p>
        @endif

        <h4 class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-2">Faculty</h4>
        <div class="overflow-x-auto mb-5">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="px-4 py-3">Number</th>
                        <th class="px-4 py-3">Requester</th>
                        <th class="px-4 py-3">Department</th>
                        <th class="px-4 py-3">Waiting</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($readyFacultyReleases as $req)
                    <tr class="border-t border-[var(--psis-border)]">
                        <td class="px-4 py-3 font-medium">{{ $req->request_number }}</td>
                        <td class="px-4 py-3">{{ $req->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $req->department?->name ?? $req->user?->department?->name ?? '—' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ ($req->approved_at ?? $req->created_at)?->diffForHumans() ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('supply.releases.show', $req) }}" class="text-pecit-blue dark:text-pecit-gold hover:underline font-medium">Release</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No faculty requests waiting to release.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <h4 class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-2">Students</h4>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="px-4 py-3">Number</th>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Department</th>
                        <th class="px-4 py-3">Waiting</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($readyStudentReleases as $purchase)
                    <tr class="border-t border-[var(--psis-border)]">
                        <td class="px-4 py-3 font-medium">{{ $purchase->purchase_number }}</td>
                        <td class="px-4 py-3">{{ $purchase->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $purchase->user?->department?->name ?? '—' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ ($purchase->verified_at ?? $purchase->created_at)?->diffForHumans() ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('supply.purchases.show', $purchase) }}" class="text-pecit-blue dark:text-pecit-gold hover:underline font-medium">Release</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No student purchases waiting to release.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="inspect-today" class="psis-card p-5 scroll-mt-24">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="font-semibold">Inspect deliveries</h3>
                <p class="text-sm text-slate-500 mt-1">Stock-in and purchase deliveries that still need a correct / wrong item check.</p>
            </div>
            <a href="{{ route('supply.purchase-history') }}" class="psis-btn-outline text-sm">Purchase History</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Qty</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($pendingInspections as $row)
                    <tr class="border-t border-[var(--psis-border)]">
                        <td class="px-4 py-3">{{ $row->inventory?->item_name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $row->supplier?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $row->reference_number ?: $row->delivery_receipt_number ?: $row->transaction_number }}</td>
                        <td class="px-4 py-3">{{ $row->quantity_in ?: $row->quantity }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('supply.purchase-history') }}" class="text-pecit-blue dark:text-pecit-gold hover:underline font-medium">Inspect</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No deliveries waiting for inspection.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="psis-card p-5">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="font-semibold">Actionable low stock</h3>
                <p class="text-sm text-slate-500 mt-1">Items at or below the minimum. Stock In to replenish.</p>
            </div>
            <a href="{{ route('inventory.index', ['status' => 'low_stock']) }}" class="psis-btn-outline text-sm">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3">Available</th>
                        <th class="px-4 py-3">Minimum</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($actionableLowStock as $item)
                    <tr class="border-t border-[var(--psis-border)]">
                        <td class="px-4 py-3">{{ $item->item_name }}</td>
                        <td class="px-4 py-3">{{ number_format($item->availableQuantity()) }}</td>
                        <td class="px-4 py-3">{{ number_format($item->minimum_stock) }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('supply.stock.index', ['item' => $item->id]) }}" class="text-pecit-blue dark:text-pecit-gold hover:underline font-medium">Stock In</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-slate-500">No low-stock items right now.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
