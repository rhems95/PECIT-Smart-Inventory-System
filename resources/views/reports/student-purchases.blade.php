@extends('layouts.psis')
@section('page-title', 'Student Purchases Report')
@section('content')
<div class="psis-card p-5 mb-4">
    <form method="GET" class="flex flex-wrap gap-3 text-sm items-end">
        <div>
            <label class="psis-label">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="psis-input">
        </div>
        <div>
            <label class="psis-label">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="psis-input">
        </div>
        <button class="psis-btn-primary">Filter</button>
        <a href="{{ route('reports.student-purchases', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="psis-btn-outline">Export PDF</a>
    </form>
</div>

<div class="psis-card overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/50 text-left text-slate-500 dark:text-slate-400">
                <th class="px-4 py-3 font-medium">Purchase #</th>
                <th class="px-4 py-3 font-medium">Student</th>
                <th class="px-4 py-3 font-medium">Total</th>
                <th class="px-4 py-3 font-medium">Status</th>
                <th class="px-4 py-3 font-medium">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchases as $purchase)
                <tr class="border-t border-[var(--psis-border)]">
                    <td class="px-4 py-3 whitespace-nowrap font-medium">{{ $purchase->purchase_number }}</td>
                    <td class="px-4 py-3">{{ $purchase->user?->name ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">₱{{ number_format($purchase->total_amount, 2) }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs bg-slate-100 dark:bg-slate-700">
                            {{ str_replace('_', ' ', $purchase->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ $purchase->created_at?->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">No purchases found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
