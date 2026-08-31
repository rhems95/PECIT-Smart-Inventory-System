@props([
    'tracker',
    'compact' => false,
])

@php
    $tracker = $tracker ?? ['headline' => '', 'hint' => '', 'failed' => false, 'steps' => []];
    $steps = $tracker['steps'] ?? [];
    $failed = (bool) ($tracker['failed'] ?? false);
@endphp

@if ($steps)
<div {{ $attributes->class($compact ? 'psis-tracker psis-tracker-compact' : 'psis-tracker') }}>
    @unless ($compact)
        <div class="mb-4">
            <p class="text-sm font-semibold {{ $failed ? 'text-red-600 dark:text-red-400' : 'text-pecit-blue dark:text-pecit-gold' }}">
                {{ $tracker['headline'] }}
            </p>
            @if (! empty($tracker['hint']))
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $tracker['hint'] }}</p>
            @endif
        </div>
    @endunless

    <ol class="psis-tracker-steps" aria-label="Order status">
        @foreach ($steps as $index => $step)
            <li class="psis-tracker-step psis-tracker-step-{{ $step['state'] }}">
                @if ($index > 0)
                    <span class="psis-tracker-line" aria-hidden="true"></span>
                @endif
                <span class="psis-tracker-dot">
                    @if ($step['state'] === 'failed')
                        <x-icon name="x-mark" class="w-4 h-4" />
                    @elseif ($step['state'] === 'done')
                        <x-icon name="check" class="w-4 h-4" />
                    @else
                        <x-icon :name="$step['icon']" class="w-4 h-4" />
                    @endif
                </span>
                <span class="psis-tracker-copy">
                    <span class="psis-tracker-label">{{ $step['label'] }}</span>
                    @unless ($compact)
                        @if ($step['state'] === 'done' && ! empty($step['at']))
                            <span class="psis-tracker-meta">{{ $step['at']->format('M d, Y') }}</span>
                        @elseif ($step['state'] === 'current')
                            <span class="psis-tracker-meta">In progress</span>
                        @elseif ($step['state'] === 'failed')
                            <span class="psis-tracker-meta">Stopped here</span>
                        @endif
                    @endunless
                </span>
            </li>
        @endforeach
    </ol>
</div>
@endif
