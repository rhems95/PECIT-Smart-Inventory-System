@extends('layouts.psis')

@section('title', 'Dashboard — PSIS')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6" x-data="{ showAvailableStock: false }">
    <div class="psis-card p-5 bg-gradient-to-r from-pecit-blue to-pecit-blue-800 text-white">
        <p class="text-sm text-white/80">Welcome back</p>
        <h2 class="text-2xl font-bold">{{ auth()->user()->name }}</h2>
        <p class="text-sm mt-2 text-pecit-gold">{{ $aiSummary }}</p>
    </div>

    @if ($announcements->isNotEmpty())
    <div class="psis-card p-5">
        <h3 class="font-semibold mb-4">Announcements</h3>
        <div class="space-y-3">
            @foreach ($announcements as $announcement)
                <div class="p-3 rounded-lg border border-[var(--psis-border)] {{ $announcement->priority === 'high' ? 'border-l-4 border-l-pecit-gold' : '' }}">
                    <div class="flex items-start justify-between gap-2">
                        <h4 class="font-medium text-sm">{{ $announcement->title }}</h4>
                        @if ($announcement->priority === 'high')
                            <span class="text-xs px-2 py-0.5 rounded bg-pecit-gold/20 text-pecit-gold">Important</span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $announcement->content }}</p>
                    <p class="text-xs text-slate-400 mt-2">{{ $announcement->published_at?->format('M d, Y') }}</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @if ($isStudent)
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="psis-card p-5">
                <p class="text-xs uppercase tracking-wide text-slate-500">Uniforms for you</p>
                <p class="text-2xl font-bold mt-1 text-pecit-blue dark:text-pecit-gold">{{ $shopCount }}</p>
                <p class="text-sm text-slate-500 mt-2">Department + shared items (P.E., NSTP, ID lanyard)</p>
                <a href="{{ route('shop.index') }}" class="psis-btn-primary inline-flex mt-4">Open Uniform Shop</a>
            </div>
            <div class="psis-card p-5">
                <p class="text-xs uppercase tracking-wide text-slate-500">My purchases</p>
                <p class="text-2xl font-bold mt-1 text-pecit-blue dark:text-pecit-gold">{{ $studentPurchases->count() }}</p>
                <p class="text-sm text-slate-500 mt-2">Recent orders shown below</p>
                <a href="{{ route('purchases.index') }}" class="psis-btn-outline inline-flex mt-4">View all purchases</a>
            </div>
        </div>

        @if ($studentPurchases->isNotEmpty())
        <div class="psis-card p-5">
            <h3 class="font-semibold mb-4">My Recent Purchases</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="px-4 py-3">Number</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($studentPurchases as $purchase)
                        <tr class="border-t border-[var(--psis-border)]">
                            <td class="px-4 py-3"><a href="{{ route('purchases.show', $purchase) }}" class="text-pecit-blue dark:text-pecit-gold hover:underline">{{ $purchase->purchase_number }}</a></td>
                            <td class="px-4 py-3">₱{{ number_format($purchase->total_amount, 2) }}</td>
                            <td class="px-4 py-3 min-w-[11rem]">
                                <x-status-tracker compact :tracker="\App\Support\OrderStatusTracker::forPurchase($purchase)" />
                            </td>
                            <td class="px-4 py-3">{{ $purchase->created_at?->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    @else
        @php
            $canInventory = auth()->user()->can('viewAny', \App\Models\Inventory::class);
            $pendingHref = match (true) {
                auth()->user()->hasAnyRole(['Administrator', 'Admission']) => route('admin.requests'),
                auth()->user()->hasRole('Accounting') => route('accounting.requests'),
                auth()->user()->hasRole('Faculty') => route('requests.index'),
                default => '#recent-requests',
            };
            $approvedHref = auth()->user()->hasAnyRole(['Administrator', 'Supply Personnel'])
                ? route('supply.releases')
                : '#recent-requests';
            $reportsHref = auth()->user()->hasAnyRole(['Administrator', 'Accounting', 'Supply Personnel'])
                ? route('reports.index')
                : '#monthly-transactions';

            $statCards = [
                ['Total Items', $stats['total_items'], $canInventory ? route('inventory.index') : null],
                ['Available Stock', number_format($stats['available_stock']), 'available-stock'],
                ['Low Stock', $stats['low_stock'], $canInventory ? route('inventory.index', ['status' => 'low_stock']) : null],
                ['Out of Stock', $stats['out_of_stock'], $canInventory ? route('inventory.index', ['status' => 'out_of_stock']) : null],
                ['Pending Requests', $stats['pending_requests'], $pendingHref],
                ['Approved', $stats['approved_requests'], $approvedHref],
                ['Released', $stats['released_requests'], '#released-items'],
                ['Monthly Txns', $stats['monthly_transactions'], $reportsHref],
            ];
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($statCards as [$label, $value, $href])
                @if ($href === 'available-stock')
                    <button
                        type="button"
                        class="psis-card p-4 w-full text-left hover:border-pecit-blue hover:shadow-md transition-shadow focus:outline-none focus:ring-2 focus:ring-pecit-blue/40"
                        :class="showAvailableStock ? 'border-pecit-blue shadow-md' : ''"
                        @click="showAvailableStock = !showAvailableStock; if (showAvailableStock) { $nextTick(() => document.getElementById('available-stock')?.scrollIntoView({ behavior: 'smooth', block: 'start' })) }"
                        :aria-expanded="showAvailableStock.toString()"
                        aria-controls="available-stock"
                    >
                        <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $label }}</p>
                        <p class="text-2xl font-bold mt-1 text-pecit-blue dark:text-pecit-gold">{{ $value }}</p>
                    </button>
                @elseif ($href)
                    <a href="{{ $href }}" class="psis-card p-4 block hover:border-pecit-blue hover:shadow-md transition-shadow focus:outline-none focus:ring-2 focus:ring-pecit-blue/40">
                        <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $label }}</p>
                        <p class="text-2xl font-bold mt-1 text-pecit-blue dark:text-pecit-gold">{{ $value }}</p>
                    </a>
                @else
                    <div class="psis-card p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $label }}</p>
                        <p class="text-2xl font-bold mt-1 text-pecit-blue dark:text-pecit-gold">{{ $value }}</p>
                    </div>
                @endif
            @endforeach
        </div>

        <div
            id="available-stock"
            x-cloak
            x-show="showAvailableStock"
            x-transition
            class="psis-card p-5 scroll-mt-24"
        >
            <h3 class="font-semibold mb-4">Available Stock</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Available</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($availableStockItems as $item)
                        <tr class="border-t border-[var(--psis-border)]">
                            <td class="px-4 py-3">{{ $item['name'] }}</td>
                            <td class="px-4 py-3">{{ number_format($item['available']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-6 text-center text-slate-500">No stock items.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <div id="monthly-transactions" class="psis-card p-5 scroll-mt-24">
                <h3 class="font-semibold mb-4">Monthly Transactions</h3>
                <canvas id="txnChart" height="120"></canvas>
            </div>
            <div class="psis-card p-5">
                <h3 class="font-semibold mb-4">AI Restocking Insights</h3>
                @forelse ($forecasts as $forecast)
                    <div class="py-2 border-b border-[var(--psis-border)] last:border-0 text-sm">
                        <p>{{ $forecast['message'] }}</p>
                        @if ($forecast['recommended_reorder'])
                            <p class="text-pecit-blue dark:text-pecit-gold text-xs mt-1">Recommended reorder: {{ $forecast['recommended_reorder'] }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No urgent forecasts.</p>
                @endforelse
            </div>
        </div>

        <div id="recent-requests" class="psis-card p-5 scroll-mt-24">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h3 class="font-semibold">Recent Faculty Requests</h3>
                @if (auth()->user()->hasAnyRole(['Administrator', 'Admission']))
                    <a href="{{ route('admin.requests') }}" class="psis-btn-outline text-sm">Approve Requests</a>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="px-4 py-3">Number</th>
                            <th class="px-4 py-3">Requester</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($recentRequests as $req)
                        <tr class="border-t border-[var(--psis-border)]">
                            <td class="px-4 py-3">{{ $req->request_number }}</td>
                            <td class="px-4 py-3">{{ $req->user?->name }}</td>
                            <td class="px-4 py-3 min-w-[11rem]">
                                <x-status-tracker compact :tracker="\App\Support\OrderStatusTracker::forSupplyRequest($req)" />
                            </td>
                            <td class="px-4 py-3">{{ $req->created_at?->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">No recent requests.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if (auth()->user()->hasAnyRole(['Supply Personnel', 'Administrator']))
        <div id="recent-student-purchases" class="psis-card p-5 scroll-mt-24">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h3 class="font-semibold">Recent Student Purchases</h3>
                <a href="{{ route('supply.purchases') }}" class="psis-btn-outline text-sm">Student Purchases</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="px-4 py-3">Number</th>
                            <th class="px-4 py-3">Student</th>
                            <th class="px-4 py-3">Department</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($recentStudentPurchases as $purchase)
                        <tr class="border-t border-[var(--psis-border)]">
                            <td class="px-4 py-3">
                                <a href="{{ route('supply.purchases.show', $purchase) }}" class="text-pecit-blue dark:text-pecit-gold hover:underline">{{ $purchase->purchase_number }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $purchase->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $purchase->user?->department?->name ?? '—' }}</td>
                            <td class="px-4 py-3 min-w-[11rem]">
                                <x-status-tracker compact :tracker="\App\Support\OrderStatusTracker::forPurchase($purchase)" />
                            </td>
                            <td class="px-4 py-3">{{ $purchase->created_at?->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">No student purchases yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if (auth()->user()->hasRole('Accounting'))
            @include('accounting._recent-verified-payments', [
                'purchases' => $recentVerifiedPayments,
                'actionHref' => route('accounting.payments'),
                'actionLabel' => 'Verify Payments',
            ])
        @endif

        <div id="released-items" class="psis-card p-5 scroll-mt-24">
            <h3 class="font-semibold mb-4">Released Items</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="px-4 py-3">Request #</th>
                            <th class="px-4 py-3">Requester</th>
                            <th class="px-4 py-3">Items</th>
                            <th class="px-4 py-3">Released</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($releasedRequests as $req)
                        <tr class="border-t border-[var(--psis-border)]">
                            <td class="px-4 py-3 font-medium">{{ $req->request_number }}</td>
                            <td class="px-4 py-3">{{ $req->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                {{ $req->items->map(fn ($line) => ($line->inventory?->item_name ?? 'Item').' ×'.($line->quantity_released ?: $line->quantity_approved ?: $line->quantity_requested))->implode(', ') ?: '—' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $req->released_at?->format('M d, Y') ?? $req->updated_at?->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">No released items yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
@unless ($isStudent)
<script>
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('txnChart');
    if (! el) return;

    new Chart(el, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Transactions',
                data: @json($chartData),
                borderColor: '#0B3C91',
                backgroundColor: 'rgba(11, 60, 145, 0.1)',
                tension: 0.3,
                fill: true,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
});
</script>
@endunless
@endpush
