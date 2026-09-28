@php
    $semesterForecast = $semesterForecast ?? ['items' => [], 'summary' => '', 'remaining_labels' => [], 'complete' => true, 'label' => ''];
    $upcoming = implode(', ', $semesterForecast['remaining_labels'] ?? []);
@endphp
@if (! ($semesterForecast['complete'] ?? true))
<div class="{{ $wrapClass ?? 'rounded-lg border border-[var(--psis-border)] p-4 mt-4' }}">
    <p class="text-xs uppercase tracking-wide text-slate-500 mb-1">This semester forecast</p>
    <h4 class="font-semibold mb-1">Items likely to trend next</h4>
    <p class="text-sm text-slate-500 mb-3">{{ $semesterForecast['summary'] }}</p>
    @if (count($semesterForecast['items'] ?? []) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3">So far</th>
                        <th class="px-4 py-3">Predicted remaining ({{ $upcoming }})</th>
                        <th class="px-4 py-3">Available</th>
                        <th class="px-4 py-3">Suggest restock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($semesterForecast['items'] as $row)
                        <tr class="border-t border-[var(--psis-border)]">
                            <td class="px-4 py-3">
                                {{ $row['item'] }}
                                <div class="text-xs text-slate-400">{{ $row['basis'] }}</div>
                            </td>
                            <td class="px-4 py-3">{{ number_format($row['so_far']) }}</td>
                            <td class="px-4 py-3">{{ number_format($row['predicted_remaining']) }}</td>
                            <td class="px-4 py-3">{{ number_format($row['available']) }}</td>
                            <td class="px-4 py-3 font-medium text-pecit-blue dark:text-pecit-gold">
                                @if ($row['recommended_reorder'] > 0)
                                    {{ number_format($row['recommended_reorder']) }} {{ $row['unit'] }}
                                @else
                                    Stock covers forecast
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endif
