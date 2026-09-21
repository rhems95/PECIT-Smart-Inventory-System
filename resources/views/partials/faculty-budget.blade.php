@if (! empty($budget))
    @php
        $mode = $budgetMode ?? 'create';
        $deptName = $budget['department']?->name ?? 'No department';
    @endphp
    <div class="psis-card p-4 text-sm space-y-1 {{ empty($budget['department']) ? 'border border-red-200' : '' }}">
        @if (empty($budget['department']))
            <p class="text-red-600">Your account has no department. Ask Admin or Supply to assign one — faculty requests need a department budget.</p>
        @elseif ($mode === 'review')
            <p>
                <strong>{{ $deptName }}</strong> faculty supply budget ({{ $budget['period_label'] }}):
                ₱{{ number_format($budget['used'], 2) }} already used by other requests.
                This request may use up to <strong>₱{{ number_format($budget['remaining'], 2) }}</strong>
                of ₱{{ number_format($budget['limit'], 2) }}.
            </p>
        @else
            <p>
                <strong>{{ $deptName }}</strong> faculty supply budget ({{ $budget['period_label'] }}):
                ₱{{ number_format($budget['used'], 2) }} used ·
                <strong>₱{{ number_format($budget['remaining'], 2) }} remaining</strong>
                of ₱{{ number_format($budget['limit'], 2) }}.
            </p>
        @endif
    </div>
@endif
