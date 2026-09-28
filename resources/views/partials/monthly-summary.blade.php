@php
    $report = $summaryReport ?? [];
    $counts = $report['counts'] ?? ['faculty_requests' => 0, 'student_purchases' => 0, 'low_stock' => 0, 'out_of_stock' => 0];
    $monthLabel = $report['month_label'] ?? now()->format('F Y');
    $monthValue = $report['month'] ?? now()->format('Y-m');
    $top = $report['top_overall'] ?? null;
    $faculty = $report['faculty'] ?? [];
    $student = $report['student'] ?? [];
    $restock = $report['restock'] ?? [];
    $canExport = $canExport ?? false;
@endphp
<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
    <div>
        <h3 class="font-semibold mb-1">{{ $title ?? 'Monthly summary' }}</h3>
        <p class="text-sm text-slate-500">{{ $monthLabel }} — faculty requests, student purchases, and restock needs. Cancelled and rejected are not counted.</p>
    </div>
    @if ($canExport)
        <div class="flex flex-wrap gap-2">
            <a class="psis-btn-outline text-sm" href="{{ route('reports.summary.pdf', ['month' => $monthValue]) }}">Export PDF</a>
            <a class="psis-btn-outline text-sm" href="{{ route('reports.summary.excel', ['month' => $monthValue]) }}">Export Excel</a>
        </div>
    @endif
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    <div class="rounded-lg border border-[var(--psis-border)] p-3">
        <p class="text-xs uppercase tracking-wide text-slate-500">Faculty requests</p>
        <p class="text-xl font-semibold mt-1">{{ number_format($counts['faculty_requests']) }}</p>
    </div>
    <div class="rounded-lg border border-[var(--psis-border)] p-3">
        <p class="text-xs uppercase tracking-wide text-slate-500">Student purchases</p>
        <p class="text-xl font-semibold mt-1">{{ number_format($counts['student_purchases']) }}</p>
    </div>
    <div class="rounded-lg border border-[var(--psis-border)] p-3">
        <p class="text-xs uppercase tracking-wide text-slate-500">Low stock</p>
        <p class="text-xl font-semibold mt-1">{{ number_format($counts['low_stock']) }}</p>
    </div>
    <div class="rounded-lg border border-[var(--psis-border)] p-3">
        <p class="text-xs uppercase tracking-wide text-slate-500">Out of stock</p>
        <p class="text-xl font-semibold mt-1">{{ number_format($counts['out_of_stock']) }}</p>
    </div>
</div>

<p class="text-sm mb-4">
    <span class="text-slate-500">Most requested overall:</span>
    <span class="font-medium">{{ $top ? $top['item'].' ('.number_format($top['total']).')' : 'None' }}</span>
</p>

<div class="grid md:grid-cols-2 gap-4 mb-4">
    <div>
        <h4 class="font-semibold mb-2">Most requested (faculty)</h4>
        @if (count($faculty))
            <ul class="text-sm space-y-1">
                @foreach ($faculty as $row)
                    <li class="flex justify-between gap-3 border-b border-[var(--psis-border)] last:border-0 py-1">
                        <span>{{ $row['item'] }}</span>
                        <span class="font-medium">{{ number_format($row['faculty_qty']) }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-slate-500">No faculty requests in {{ $monthLabel }}.</p>
        @endif
    </div>
    <div>
        <h4 class="font-semibold mb-2">Most purchased (students)</h4>
        @if (count($student))
            <ul class="text-sm space-y-1">
                @foreach ($student as $row)
                    <li class="flex justify-between gap-3 border-b border-[var(--psis-border)] last:border-0 py-1">
                        <span>{{ $row['item'] }}</span>
                        <span class="font-medium">{{ number_format($row['student_qty']) }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-slate-500">No student purchases in {{ $monthLabel }}.</p>
        @endif
    </div>
</div>

<h4 class="font-semibold mb-2">Need restock this month</h4>
@if (count($restock))
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-slate-500">
                    <th class="px-4 py-3">Item</th>
                    <th class="px-4 py-3">Faculty</th>
                    <th class="px-4 py-3">Student</th>
                    <th class="px-4 py-3">Demand</th>
                    <th class="px-4 py-3">Available</th>
                    <th class="px-4 py-3">Suggest restock</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($restock as $row)
                    <tr class="border-t border-[var(--psis-border)]">
                        <td class="px-4 py-3">{{ $row['item'] }}</td>
                        <td class="px-4 py-3">{{ number_format($row['faculty_qty']) }}</td>
                        <td class="px-4 py-3">{{ number_format($row['student_qty']) }}</td>
                        <td class="px-4 py-3">{{ number_format($row['demand']) }}</td>
                        <td class="px-4 py-3">{{ number_format($row['available']) }}</td>
                        <td class="px-4 py-3 font-medium">{{ number_format($row['recommended_reorder']) }} {{ $row['unit'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <p class="text-sm text-slate-500">No demanded items need restock in {{ $monthLabel }}.</p>
@endif
