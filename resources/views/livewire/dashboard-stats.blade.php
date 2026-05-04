@php
    $fmtTokens = function(int $n): string {
        if ($n >= 1_000_000) return round($n / 1_000_000, 1) . 'M';
        if ($n >= 1_000)     return round($n / 1_000, 1) . 'k';
        return (string) $n;
    };
    $fmtDuration = function(int $ms): string {
        if ($ms === 0)    return '—';
        if ($ms < 60_000) return round($ms / 1000, 1) . 's';
        return floor($ms / 60_000) . 'm ' . round(($ms % 60_000) / 1000) . 's';
    };
    $maxCount = max(1, max(array_column($chart, 'count')));
@endphp

<div wire:poll.30s class="px-5 py-3 flex items-center gap-6 flex-wrap" style="border-bottom: 1px solid #141416;">

    {{-- Metric: Total runs --}}
    <div class="flex items-baseline gap-1.5">
        <span class="text-lg font-bold tabular-nums" style="color: #e8e8ea; font-variant-numeric: tabular-nums;">
            {{ number_format($stats['total']) }}
        </span>
        <span class="text-[11px] uppercase tracking-wider" style="color: #52525b;">runs</span>
    </div>

    <div class="w-px h-6" style="background-color: #1f1f23;"></div>

    {{-- Metric: Today --}}
    <div class="flex items-baseline gap-1.5">
        <span class="text-lg font-bold tabular-nums" style="color: {{ $stats['today'] > 0 ? '#e8e8ea' : '#3f3f46' }};">
            {{ $stats['today'] }}
        </span>
        <span class="text-[11px] uppercase tracking-wider" style="color: #52525b;">today</span>
    </div>

    <div class="w-px h-6" style="background-color: #1f1f23;"></div>

    {{-- Metric: Success rate --}}
    <div class="flex items-baseline gap-1.5">
        @if ($stats['success_rate'] !== null)
            <span class="text-lg font-bold tabular-nums"
                  style="color: {{ $stats['success_rate'] >= 90 ? '#10b981' : ($stats['success_rate'] >= 70 ? '#f59e0b' : '#ef4444') }};">
                {{ $stats['success_rate'] }}%
            </span>
        @else
            <span class="text-lg font-bold" style="color: #3f3f46;">—</span>
        @endif
        <span class="text-[11px] uppercase tracking-wider" style="color: #52525b;">success</span>
    </div>

    <div class="w-px h-6" style="background-color: #1f1f23;"></div>

    {{-- Metric: Tokens --}}
    <div class="flex items-baseline gap-1.5">
        <span class="text-lg font-bold tabular-nums" style="color: #e8e8ea;">
            {{ $fmtTokens($stats['tokens']) }}
        </span>
        <span class="text-[11px] uppercase tracking-wider" style="color: #52525b;">tokens</span>
    </div>

    <div class="w-px h-6" style="background-color: #1f1f23;"></div>

    {{-- Metric: Avg duration --}}
    <div class="flex items-baseline gap-1.5">
        <span class="text-lg font-bold tabular-nums" style="color: #e8e8ea;">
            {{ $fmtDuration($stats['avg_ms']) }}
        </span>
        <span class="text-[11px] uppercase tracking-wider" style="color: #52525b;">avg</span>
    </div>

    <div class="w-px h-6" style="background-color: #1f1f23;"></div>

    {{-- 7-day sparkline --}}
    <div class="flex items-end gap-1" style="height: 28px;">
        @foreach ($chart as $day)
            @php $pct = $maxCount > 0 ? $day['count'] / $maxCount : 0; $h = max(3, (int)($pct * 24)); @endphp
            <div class="flex flex-col items-center gap-0.5" title="{{ $day['day'] }}: {{ $day['count'] }} runs">
                <div
                    class="w-4 rounded-sm transition-all duration-300"
                    style="height: {{ $h }}px; background-color: {{ $day['count'] > 0 ? '#5e6ad2' : '#1f1f23' }};"
                ></div>
                <span class="text-[8px] leading-none" style="color: #3f3f46;">{{ $day['label'] }}</span>
            </div>
        @endforeach
    </div>

    {{-- Spacer --}}
    <div class="flex-1"></div>

    {{-- Currently running --}}
    @if ($stats['running'] > 0)
    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full" style="background-color: #0f1a2e; border: 1px solid #1e3a5f;">
        <span class="inline-block w-1.5 h-1.5 rounded-full animate-pulse" style="background-color: #3b82f6;"></span>
        <span class="text-[11px] font-medium tabular-nums" style="color: #3b82f6;">
            {{ $stats['running'] }} running
        </span>
    </div>
    @endif
</div>
