@extends('layouts.psis')
@section('title', 'Supplies issuance — PSIS')
@section('page-title', 'Supplies issuance log')
@section('content')
@php
    $query = [
        'period' => $report['period'],
        'source' => $report['source'],
        'department_id' => $report['department_id'],
        'date' => $report['date'],
        'month' => $report['month'],
        'year' => $report['year'],
        'semester' => $report['semester'],
    ];
@endphp
<div class="space-y-6">
    <div class="psis-card p-5">
        <p class="text-sm text-slate-500 mb-4">Released faculty requests and student purchases — the same kind of issuance log as the Supply office spreadsheet. Filter by day, week, month, semester, year, or all; split Faculty vs Students; and limit to one department. Cancelled and rejected are not included.</p>
        <form method="get" action="{{ route('reports.supplies-issuance') }}" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label class="psis-label" for="period">Period</label>
                <select id="period" name="period" class="psis-input" onchange="this.form.submit()">
                    <option value="day" @selected($report['period'] === 'day')>Day</option>
                    <option value="week" @selected($report['period'] === 'week')>Week</option>
                    <option value="month" @selected($report['period'] === 'month')>Month</option>
                    <option value="semester" @selected($report['period'] === 'semester')>Semester</option>
                    <option value="year" @selected($report['period'] === 'year')>Year</option>
                    <option value="all" @selected($report['period'] === 'all')>All</option>
                </select>
            </div>
            @if (in_array($report['period'], ['day', 'week'], true))
                <div>
                    <label class="psis-label" for="date">{{ $report['period'] === 'week' ? 'Week of' : 'Date' }}</label>
                    <input id="date" type="date" name="date" value="{{ $report['date'] }}" class="psis-input">
                </div>
            @endif
            @if ($report['period'] === 'month')
                <div>
                    <label class="psis-label" for="month">Month</label>
                    <select id="month" name="month" class="psis-input">
                        @foreach ($monthOptions as $option)
                            <option value="{{ $option['value'] }}" @selected($report['month'] === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if ($report['period'] === 'semester')
                <div>
                    <label class="psis-label" for="semester">Semester</label>
                    <select id="semester" name="semester" class="psis-input">
                        @foreach ($semesterOptions as $option)
                            <option value="{{ $option['value'] }}" @selected($report['semester'] === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if ($report['period'] === 'year')
                <div>
                    <label class="psis-label" for="year">Year</label>
                    <select id="year" name="year" class="psis-input">
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}" @selected($report['year'] === $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="psis-label" for="source">Source</label>
                <select id="source" name="source" class="psis-input">
                    <option value="all" @selected($report['source'] === 'all')>Faculty and students</option>
                    <option value="faculty" @selected($report['source'] === 'faculty')>Faculty only</option>
                    <option value="student" @selected($report['source'] === 'student')>Students only</option>
                </select>
            </div>
            <div>
                <label class="psis-label" for="department_id">Department</label>
                <select id="department_id" name="department_id" class="psis-input">
                    <option value="">All departments</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((int) $report['department_id'] === (int) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="psis-btn-primary">Apply</button>
                <a href="{{ route('reports.supplies-issuance') }}" class="psis-btn-outline">Reset</a>
            </div>
        </form>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">Showing <strong>{{ $report['range_label'] }}</strong>@if ($report['faculty']->count() + $report['student']->count() > 100) <span>(this page lists 100 lines at a time; PDF and Excel include the full log)</span>@endif</p>
        <div class="flex flex-wrap gap-2">
            <a class="psis-btn-outline text-sm" href="{{ route('reports.supplies-issuance.pdf', $query) }}">Export PDF</a>
            <a class="psis-btn-outline text-sm" href="{{ route('reports.supplies-issuance.excel', $query) }}">Export Excel</a>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="psis-card p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500">Faculty lines</p>
            <p class="text-2xl font-bold mt-1 text-pecit-blue dark:text-pecit-gold">{{ number_format($report['totals']['faculty_lines']) }}</p>
        </div>
        <div class="psis-card p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500">Student lines</p>
            <p class="text-2xl font-bold mt-1 text-pecit-blue dark:text-pecit-gold">{{ number_format($report['totals']['student_lines']) }}</p>
        </div>
        <div class="psis-card p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500">Faculty amount</p>
            <p class="text-2xl font-bold mt-1 text-pecit-blue dark:text-pecit-gold">₱{{ number_format($report['totals']['faculty_amount'], 2) }}</p>
        </div>
        <div class="psis-card p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500">Student amount</p>
            <p class="text-2xl font-bold mt-1 text-pecit-blue dark:text-pecit-gold">₱{{ number_format($report['totals']['student_amount'], 2) }}</p>
        </div>
    </div>

    <div class="psis-card p-5">
        <h3 class="font-semibold mb-4">Per department</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="px-4 py-3">Department</th>
                        <th class="px-4 py-3">Faculty qty</th>
                        <th class="px-4 py-3">Student qty</th>
                        <th class="px-4 py-3">Faculty amount</th>
                        <th class="px-4 py-3">Student amount</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($report['departments'] as $row)
                    <tr class="border-t border-[var(--psis-border)]">
                        <td class="px-4 py-3">{{ $row['department'] }}</td>
                        <td class="px-4 py-3">{{ \App\Support\Qty::format($row['faculty_qty']) }}</td>
                        <td class="px-4 py-3">{{ \App\Support\Qty::format($row['student_qty']) }}</td>
                        <td class="px-4 py-3">₱{{ number_format($row['faculty_amount'], 2) }}</td>
                        <td class="px-4 py-3">₱{{ number_format($row['student_amount'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No released items in this period.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($report['source'] !== 'student')
    <div class="psis-card p-5">
        <h3 class="font-semibold mb-4">Faculty</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Articles/Items</th>
                        <th class="px-4 py-3">QTY</th>
                        <th class="px-4 py-3">UNIT</th>
                        <th class="px-4 py-3">UNIT PRICE</th>
                        <th class="px-4 py-3">TOTAL AMOUNT</th>
                        <th class="px-4 py-3">SEMESTER</th>
                        <th class="px-4 py-3">DEPARTMENT</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($facultyPage as $row)
                    <tr class="border-t border-[var(--psis-border)]">
                        <td class="px-4 py-3 whitespace-nowrap">{{ $row['date']?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $row['item'] }}</td>
                        <td class="px-4 py-3">{{ \App\Support\Qty::format($row['qty']) }}</td>
                        <td class="px-4 py-3">{{ $row['unit'] }}</td>
                        <td class="px-4 py-3">₱{{ number_format($row['unit_price'], 2) }}</td>
                        <td class="px-4 py-3">₱{{ number_format($row['total_amount'], 2) }}</td>
                        <td class="px-4 py-3">{{ $row['semester'] }}</td>
                        <td class="px-4 py-3">{{ $row['department'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-slate-500">No faculty releases in this period.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($facultyPage->hasPages())
            <div class="flex flex-wrap items-center gap-3 mt-4 text-sm">
                @if ($facultyPage->previousPageUrl())
                    <a class="psis-btn-outline text-sm" href="{{ $facultyPage->previousPageUrl() }}">Previous</a>
                @endif
                <span class="text-slate-500">{{ $facultyPage->firstItem() }}–{{ $facultyPage->lastItem() }} of {{ number_format($facultyPage->total()) }}</span>
                @if ($facultyPage->nextPageUrl())
                    <a class="psis-btn-outline text-sm" href="{{ $facultyPage->nextPageUrl() }}">Next</a>
                @endif
            </div>
        @endif
    </div>
    @endif

    @if ($report['source'] !== 'faculty')
    <div class="psis-card p-5">
        <h3 class="font-semibold mb-4">Students</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Articles/Items</th>
                        <th class="px-4 py-3">QTY</th>
                        <th class="px-4 py-3">UNIT</th>
                        <th class="px-4 py-3">UNIT PRICE</th>
                        <th class="px-4 py-3">TOTAL AMOUNT</th>
                        <th class="px-4 py-3">SEMESTER</th>
                        <th class="px-4 py-3">DEPARTMENT</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($studentPage as $row)
                    <tr class="border-t border-[var(--psis-border)]">
                        <td class="px-4 py-3 whitespace-nowrap">{{ $row['date']?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $row['item'] }}</td>
                        <td class="px-4 py-3">{{ \App\Support\Qty::format($row['qty']) }}</td>
                        <td class="px-4 py-3">{{ $row['unit'] }}</td>
                        <td class="px-4 py-3">₱{{ number_format($row['unit_price'], 2) }}</td>
                        <td class="px-4 py-3">₱{{ number_format($row['total_amount'], 2) }}</td>
                        <td class="px-4 py-3">{{ $row['semester'] }}</td>
                        <td class="px-4 py-3">{{ $row['department'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-slate-500">No student releases in this period.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($studentPage->hasPages())
            <div class="flex flex-wrap items-center gap-3 mt-4 text-sm">
                @if ($studentPage->previousPageUrl())
                    <a class="psis-btn-outline text-sm" href="{{ $studentPage->previousPageUrl() }}">Previous</a>
                @endif
                <span class="text-slate-500">{{ $studentPage->firstItem() }}–{{ $studentPage->lastItem() }} of {{ number_format($studentPage->total()) }}</span>
                @if ($studentPage->nextPageUrl())
                    <a class="psis-btn-outline text-sm" href="{{ $studentPage->nextPageUrl() }}">Next</a>
                @endif
            </div>
        @endif
    </div>
    @endif
</div>
@endsection
