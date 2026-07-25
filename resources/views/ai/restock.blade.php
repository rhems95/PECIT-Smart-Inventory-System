@extends('layouts.psis')

@section('title', 'Restock Recommendations — PSIS')
@section('page-title', 'Restock Recommendations')

@section('content')
<div class="space-y-4">
    <div class="psis-card p-5">
        <p class="text-sm">{{ $summary }}</p>
        <p class="text-xs text-slate-500 mt-2">Based on the last 90 days of releases/stock-outs. Suggested reorder aims to cover about 30 days of usage.</p>
    </div>

    <div class="psis-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900/50 text-left text-slate-500 dark:text-slate-400">
                    <th class="px-4 py-3 font-medium">Urgency</th>
                    <th class="px-4 py-3 font-medium">Item</th>
                    <th class="px-4 py-3 font-medium">Category</th>
                    <th class="px-4 py-3 font-medium">On Hand</th>
                    <th class="px-4 py-3 font-medium">Available</th>
                    <th class="px-4 py-3 font-medium">Days Left</th>
                    <th class="px-4 py-3 font-medium">Daily Use</th>
                    <th class="px-4 py-3 font-medium">Reorder Qty</th>
                    <th class="px-4 py-3 font-medium">Supplier</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recommendations as $row)
                    <tr class="border-t border-[var(--psis-border)]">
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                @if($row['urgency']==='critical') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200
                                @elseif($row['urgency']==='high') bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200
                                @elseif($row['urgency']==='medium') bg-yellow-100 text-yellow-800
                                @else bg-slate-100 dark:bg-slate-700 @endif">
                                {{ ucfirst($row['urgency']) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('inventory.show', $row['inventory_id']) }}" class="font-medium text-pecit-blue dark:text-pecit-gold hover:underline">{{ $row['item'] }}</a>
                            <div class="text-xs text-slate-400">{{ $row['item_code'] }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $row['category'] ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $row['on_hand'] }}</td>
                        <td class="px-4 py-3">{{ $row['available'] }} {{ $row['unit'] }}</td>
                        <td class="px-4 py-3">{{ $row['days_until_depletion'] ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $row['daily_rate'] }}</td>
                        <td class="px-4 py-3 font-semibold text-pecit-blue dark:text-pecit-gold">{{ $row['recommended_reorder'] }} {{ $row['unit'] }}</td>
                        <td class="px-4 py-3">{{ $row['supplier'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-500">No restock recommendations right now.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ route('ai.chat') }}" class="psis-btn-outline inline-flex">Back to AI Assistant</a>
</div>
@endsection
