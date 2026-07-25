@extends('layouts.psis')

@section('title', 'Dashboard — PSIS')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
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

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ([
            ['Total Items', $stats['total_items']],
            ['Available Stock', number_format($stats['available_stock'])],
            ['Low Stock', $stats['low_stock']],
            ['Out of Stock', $stats['out_of_stock']],
            ['Pending Requests', $stats['pending_requests']],
            ['Approved', $stats['approved_requests']],
            ['Released', $stats['released_requests']],
            ['Monthly Txns', $stats['monthly_transactions']],
        ] as [$label, $value])
            <div class="psis-card p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $label }}</p>
                <p class="text-2xl font-bold mt-1 text-pecit-blue dark:text-pecit-gold">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="psis-card p-5">
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

    @if ($studentPurchases->isNotEmpty())
    <div class="psis-card p-5">
        <h3 class="font-semibold mb-4">My Recent Purchases</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-slate-500"><th class="py-2">Number</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                @foreach ($studentPurchases as $purchase)
                    <tr class="border-t border-[var(--psis-border)]">
                        <td class="py-2"><a href="{{ route('purchases.show', $purchase) }}" class="text-pecit-blue dark:text-pecit-gold hover:underline">{{ $purchase->purchase_number }}</a></td>
                        <td>₱{{ number_format($purchase->total_amount, 2) }}</td>
                        <td><span class="px-2 py-0.5 rounded text-xs bg-slate-100 dark:bg-slate-700">{{ str_replace('_', ' ', $purchase->status) }}</span></td>
                        <td>{{ $purchase->created_at?->format('M d, Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="psis-card p-5">
        <h3 class="font-semibold mb-4">Recent Faculty Requests</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-slate-500"><th class="py-2">Number</th><th>Requester</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                @foreach ($recentRequests as $req)
                    <tr class="border-t border-[var(--psis-border)]">
                        <td class="py-2">{{ $req->request_number }}</td>
                        <td>{{ $req->user?->name }}</td>
                        <td><span class="px-2 py-0.5 rounded text-xs bg-slate-100 dark:bg-slate-700">{{ $req->status }}</span></td>
                        <td>{{ $req->created_at?->format('M d, Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    new Chart(document.getElementById('txnChart'), {
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
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
});
</script>
@endpush
