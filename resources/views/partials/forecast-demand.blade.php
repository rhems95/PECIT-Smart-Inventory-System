@php
    $facultyDemand = $demandMemory['faculty'] ?? [];
    $studentDemand = $demandMemory['student'] ?? [];
    $demandRestock = $demandMemory['restock'] ?? [];
    $demandMonth = $demandMemory['month_label'] ?? now()->format('F Y');
@endphp
<div class="grid md:grid-cols-2 gap-4">
    <div class="{{ $cardClass ?? '' }}">
        <h4 class="font-semibold mb-2">Most requested (faculty)</h4>
        <p class="text-xs text-slate-500 mb-2">{{ $demandMonth }} — cancelled and rejected are not counted.</p>
        @if (count($facultyDemand))
            <ul class="text-sm space-y-1">
                @foreach ($facultyDemand as $row)
                    <li class="flex justify-between gap-3 border-b border-[var(--psis-border)] last:border-0 py-1">
                        <span>{{ $row['item'] }}</span>
                        <span class="font-medium text-pecit-blue dark:text-pecit-gold">{{ number_format($row['faculty_qty']) }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-slate-500">No faculty requests in {{ $demandMonth }}.</p>
        @endif
    </div>
    <div class="{{ $cardClass ?? '' }}">
        <h4 class="font-semibold mb-2">Most purchased (students)</h4>
        <p class="text-xs text-slate-500 mb-2">{{ $demandMonth }} — cancelled and rejected are not counted.</p>
        @if (count($studentDemand))
            <ul class="text-sm space-y-1">
                @foreach ($studentDemand as $row)
                    <li class="flex justify-between gap-3 border-b border-[var(--psis-border)] last:border-0 py-1">
                        <span>{{ $row['item'] }}</span>
                        <span class="font-medium text-pecit-blue dark:text-pecit-gold">{{ number_format($row['student_qty']) }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-slate-500">No student purchases in {{ $demandMonth }}.</p>
        @endif
    </div>
</div>

<div class="{{ $cardClass ?? 'mt-4' }}">
    <h4 class="font-semibold mb-2">Predicted restock this month</h4>
    <p class="text-xs text-slate-500 mb-2">AI remembers this month’s top demand and flags items whose available stock cannot cover it.</p>
    @if (count($demandRestock))
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
                    @foreach ($demandRestock as $row)
                        <tr class="border-t border-[var(--psis-border)]">
                            <td class="px-4 py-3">{{ $row['item'] }}</td>
                            <td class="px-4 py-3">{{ number_format($row['faculty_qty']) }}</td>
                            <td class="px-4 py-3">{{ number_format($row['student_qty']) }}</td>
                            <td class="px-4 py-3">{{ number_format($row['demand']) }}</td>
                            <td class="px-4 py-3">{{ number_format($row['available']) }}</td>
                            <td class="px-4 py-3 font-medium text-pecit-blue dark:text-pecit-gold">{{ number_format($row['recommended_reorder']) }} {{ $row['unit'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-sm text-slate-500">No demanded items need restock in {{ $demandMonth }}.</p>
    @endif
</div>
