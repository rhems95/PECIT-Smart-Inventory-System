@extends('layouts.psis')
@section('title', 'Reports — PSIS')
@section('page-title', 'Reports')
@section('content')
<div class="psis-card p-5 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-4">
        <div>
            <h3 class="font-semibold mb-1">Most requested items</h3>
            <p class="text-sm text-slate-500">{{ $mostRequestedPeriod }} — faculty requests and student purchases. Cancelled and rejected are not counted.</p>
        </div>
        <form method="get" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-2">
            <input type="hidden" name="trend_semester" value="{{ $selectedTrendSemester }}">
            <div>
                <label for="report-period" class="psis-label">Period</label>
                <select id="report-period" name="period" class="psis-input min-w-[9rem]" onchange="this.form.submit()">
                    <option value="month" @selected($selectedPeriod === 'month')>Month</option>
                    <option value="semester" @selected($selectedPeriod === 'semester')>Semester</option>
                    <option value="year" @selected($selectedPeriod === 'year')>Year</option>
                </select>
            </div>
            @if ($selectedPeriod === 'month')
                <div>
                    <label for="report-month" class="psis-label">Month</label>
                    <select id="report-month" name="month" class="psis-input min-w-[10rem]" onchange="this.form.submit()">
                        @foreach ($monthOptions as $option)
                            <option value="{{ $option['value'] }}" @selected($selectedMonth === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if ($selectedPeriod === 'semester')
                <div>
                    <label for="report-semester" class="psis-label">Semester</label>
                    <select id="report-semester" name="semester" class="psis-input min-w-[14rem]" onchange="this.form.submit()">
                        @foreach ($semesterOptions as $option)
                            <option value="{{ $option['value'] }}" @selected($selectedSemester === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if ($selectedPeriod === 'year')
                <div>
                    <label for="report-year" class="psis-label">Year</label>
                    <select id="report-year" name="year" class="psis-input min-w-[8rem]" onchange="this.form.submit()">
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </form>
    </div>
    @if (count($mostRequested))
        <div id="mostRequestedChartWrap" class="w-full" style="height: {{ max(320, 96 + count($mostRequested) * 58) }}px">
            <canvas id="mostRequestedChart"></canvas>
        </div>
        <div class="overflow-x-auto mt-4">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3">Faculty</th>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mostRequested as $row)
                        <tr class="border-t border-[var(--psis-border)]">
                            <td class="px-4 py-3">{{ $row['item'] }}</td>
                            <td class="px-4 py-3">{{ number_format($row['faculty_qty']) }}</td>
                            <td class="px-4 py-3">{{ number_format($row['student_qty']) }}</td>
                            <td class="px-4 py-3 font-medium">{{ number_format($row['total']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-sm text-slate-500">No requested items in {{ $mostRequestedPeriod }}.</p>
    @endif
</div>

<div id="demand-trend" class="psis-card p-5 mb-6 scroll-mt-24">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-4">
        <div>
            <h3 class="font-semibold mb-1">Demand trend</h3>
            <p class="text-sm text-slate-500">{{ $demandTrend['label'] }} — item names on the left; months of this semester along the bottom. Cancelled and rejected are not counted.</p>
        </div>
        <form method="get" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-2" data-keep-section="demand-trend">
            <input type="hidden" name="period" value="{{ $selectedPeriod }}">
            @if ($selectedPeriod === 'month')
                <input type="hidden" name="month" value="{{ $selectedMonth }}">
            @elseif ($selectedPeriod === 'semester')
                <input type="hidden" name="semester" value="{{ $selectedSemester }}">
            @elseif ($selectedPeriod === 'year')
                <input type="hidden" name="year" value="{{ $selectedYear }}">
            @endif
            <div>
                <label for="trend-semester" class="psis-label">Semester</label>
                <select id="trend-semester" name="trend_semester" class="psis-input min-w-[14rem]" onchange="psisKeepReportSection(this.form)">
                    @foreach ($semesterOptions as $option)
                        <option value="{{ $option['value'] }}" @selected($selectedTrendSemester === $option['value'])>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    @if (count($demandTrend['items'] ?? []) > 0)
        <div id="demandTrendChartWrap" class="w-full mb-4" style="height: {{ max(320, 120 + count($demandTrend['items']) * 52) }}px">
            <canvas id="demandTrendChart"></canvas>
        </div>
        <div class="overflow-x-auto mb-4">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="px-4 py-3">Item</th>
                        @foreach ($demandTrend['months'] as $month)
                            <th class="px-4 py-3 whitespace-nowrap">{{ $month['label'] }}</th>
                        @endforeach
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Predicted</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($demandTrend['items'] as $row)
                        <tr class="border-t border-[var(--psis-border)]">
                            <td class="px-4 py-3">{{ $row['item'] }}</td>
                            @foreach ($row['months'] as $qty)
                                <td class="px-4 py-3">{{ number_format($qty) }}</td>
                            @endforeach
                            <td class="px-4 py-3 font-medium">{{ number_format($row['total']) }}</td>
                            <td class="px-4 py-3">{{ number_format($row['predicted']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @php $pred = $demandTrend['prediction'] ?? []; @endphp
        <div class="rounded-lg border border-[var(--psis-border)] p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 mb-1">Predicted trend</p>
            <p class="text-sm">{{ $pred['summary'] ?? '' }}</p>
            @if (($pred['prior_total'] ?? 0) >= 50)
                <p class="text-xs text-slate-500 mt-2">Last year same months: {{ number_format($pred['prior_total']) }}. This {{ $pred['complete'] ? 'period' : 'period (projected)' }}: {{ number_format($pred['complete'] ? $pred['current_total'] : $pred['projected_total']) }}.</p>
            @endif
        </div>
    @else
        <p class="text-sm text-slate-500">No requested items in {{ $demandTrend['label'] ?? $mostRequestedPeriod }}.</p>
    @endif
    @include('partials.semester-trend-forecast', ['semesterForecast' => $semesterForecast])
</div>

<div class="psis-card p-5 mb-4">
    @include('partials.monthly-summary', [
        'summaryReport' => $summaryReport,
        'title' => 'Monthly summary',
        'canExport' => true,
    ])
</div>

<div class="grid md:grid-cols-2 gap-4">
    <div class="psis-card p-5 space-y-2">
        <h3 class="font-semibold">Export</h3>
        <a class="psis-btn-outline block text-center" href="{{ route('reports.summary.pdf', ['month' => $selectedMonth]) }}">Monthly Summary (PDF)</a>
        <a class="psis-btn-outline block text-center" href="{{ route('reports.summary.excel', ['month' => $selectedMonth]) }}">Monthly Summary (Excel)</a>
        <a class="psis-btn-outline block text-center" href="{{ route('reports.low-stock.pdf') }}">Low Stock (PDF)</a>
        <a class="psis-btn-outline block text-center" href="{{ route('reports.out-of-stock.pdf') }}">Out of Stock (PDF)</a>
        <a class="psis-btn-outline block text-center" href="{{ route('reports.valuation.pdf') }}">Inventory Valuation (PDF)</a>
        <a class="psis-btn-outline block text-center" href="{{ route('reports.daily-inventory.pdf') }}">Daily Inventory (PDF)</a>
        <a class="psis-btn-outline block text-center" href="{{ route('reports.monthly-inventory.pdf') }}">Monthly Inventory (PDF)</a>
        <a class="psis-btn-outline block text-center" href="{{ route('reports.supplies-issuance') }}">Supplies issuance log</a>
        <a class="psis-btn-outline block text-center" href="{{ route('reports.faculty-requests') }}">Faculty Requests</a>
        <a class="psis-btn-outline block text-center" href="{{ route('reports.student-purchases') }}">Student Purchases</a>
        <a class="psis-btn-outline block text-center" href="{{ route('reports.audit-trail.pdf') }}">Audit Trail (PDF)</a>
        <a class="psis-btn-outline block text-center" href="{{ route('reports.transactions.excel') }}">Transactions (Excel)</a>
    </div>
</div>

<div class="psis-card p-5 mt-4">
    <h3 class="font-semibold mb-3">Low Stock Items</h3>
    <ul class="text-sm list-disc list-inside">
        @forelse($lowStock as $item)
            <li>{{ $item->item_name }} — {{ $item->availableQuantity() }} left</li>
        @empty
            <li>None</li>
        @endforelse
    </ul>
</div>
@endsection

@push('scripts')
<script>
let psisReportsScrollY = null;

function psisKeepReportSection(form) {
    const hash = form.getAttribute('data-keep-section') || '';
    try {
        sessionStorage.setItem('psis-reports-scroll', String(window.scrollY));
    } catch (e) {}
    const params = new URLSearchParams(new FormData(form));
    const base = (form.getAttribute('action') || window.location.pathname).split('#')[0];
    const query = params.toString();
    window.location.assign(base + (query ? '?' + query : '') + (hash ? '#' + hash : ''));
}

function psisRestoreReportSection() {
    if (psisReportsScrollY === null) {
        try {
            const stored = sessionStorage.getItem('psis-reports-scroll');
            if (stored !== null) {
                sessionStorage.removeItem('psis-reports-scroll');
                const y = Number(stored);
                if (Number.isFinite(y)) {
                    psisReportsScrollY = y;
                }
            }
        } catch (e) {}
    }

    if (psisReportsScrollY !== null) {
        window.scrollTo(0, psisReportsScrollY);
        return;
    }

    const anchor = window.location.hash.replace(/^#/, '');
    if (anchor) {
        document.getElementById(anchor)?.scrollIntoView({ block: 'start' });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    psisRestoreReportSection();
    if (typeof Chart === 'undefined') return;

    const dark = document.documentElement.classList.contains('dark');
    const tick = dark ? '#94a3b8' : '#64748b';
    const grid = dark ? 'rgba(148, 163, 184, 0.12)' : 'rgba(148, 163, 184, 0.22)';
    const legend = {
        display: true,
        position: 'top',
        align: 'end',
        labels: {
            usePointStyle: true,
            pointStyle: 'rectRounded',
            boxWidth: 10,
            boxHeight: 10,
            color: tick,
            font: { size: 12, weight: '600' },
            padding: 16,
        },
    };
    const tooltipTotal = {
        backgroundColor: dark ? '#0f172a' : '#0B3C91',
        titleColor: '#fff',
        bodyColor: '#fff',
        padding: 10,
        cornerRadius: 8,
        callbacks: {
            footer(items) {
                const total = items.reduce((sum, item) => {
                    const value = item.chart.options.indexAxis === 'y' ? item.parsed.x : item.parsed.y;
                    return sum + Number(value ?? 0);
                }, 0);
                return 'Total: ' + total;
            },
        },
    };

    const rank = document.getElementById('mostRequestedChart');
    if (rank) {
        new Chart(rank, {
            type: 'bar',
            data: {
                labels: @json(collect($mostRequested)->pluck('item')),
                datasets: [
                    {
                        label: 'Faculty',
                        data: @json(collect($mostRequested)->pluck('faculty_qty')),
                        backgroundColor: '#0B3C91',
                        borderRadius: 8,
                        borderSkipped: false,
                        barPercentage: 0.62,
                        categoryPercentage: 0.72,
                    },
                    {
                        label: 'Students',
                        data: @json(collect($mostRequested)->pluck('student_qty')),
                        backgroundColor: '#F4B400',
                        borderRadius: 8,
                        borderSkipped: false,
                        barPercentage: 0.62,
                        categoryPercentage: 0.72,
                    },
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 8, right: 16, bottom: 8, left: 4 } },
                plugins: {
                    legend,
                    tooltip: {
                        ...tooltipTotal,
                        callbacks: {
                            ...tooltipTotal.callbacks,
                            title(items) {
                                return items[0]?.label ?? '';
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: { precision: 0, color: tick },
                        grid: { color: grid },
                        border: { display: false },
                    },
                    y: {
                        stacked: true,
                        ticks: {
                            color: tick,
                            autoSkip: false,
                            padding: 10,
                            font: { size: 12 },
                            callback(value) {
                                const label = this.getLabelForValue(value);
                                return label.length > 28 ? label.slice(0, 26) + '…' : label;
                            },
                        },
                        grid: { display: false },
                        border: { display: false },
                    },
                },
            },
        });
    }

    const trend = document.getElementById('demandTrendChart');
    if (trend) {
        const trendPayload = @json($demandTrend['chart'] ?? ['labels' => [], 'datasets' => []]);
        new Chart(trend, {
            type: 'bar',
            data: {
                labels: trendPayload.labels ?? [],
                datasets: (trendPayload.datasets ?? []).map((dataset) => ({
                    ...dataset,
                    borderSkipped: false,
                    maxBarThickness: 22,
                })),
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 8, right: 16, bottom: 8, left: 4 } },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        ...legend,
                        position: 'bottom',
                        align: 'center',
                    },
                    tooltip: tooltipTotal,
                },
                scales: {
                    x: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: { precision: 0, color: tick },
                        grid: { color: grid },
                        border: { display: false },
                    },
                    y: {
                        stacked: true,
                        ticks: {
                            color: tick,
                            autoSkip: false,
                            padding: 10,
                            font: { size: 12 },
                            callback(value) {
                                const label = this.getLabelForValue(value);
                                return label.length > 28 ? label.slice(0, 26) + '…' : label;
                            },
                        },
                        grid: { display: false },
                        border: { display: false },
                    },
                },
            },
        });
    }

    requestAnimationFrame(psisRestoreReportSection);
});
</script>
@endpush
