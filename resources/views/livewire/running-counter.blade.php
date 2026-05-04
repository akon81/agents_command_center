<div wire:poll.4s class="flex items-center gap-1.5">
    @if ($running > 0)
        <span class="inline-block w-1.5 h-1.5 rounded-full animate-pulse" style="background-color: #3b82f6;"></span>
        <span class="text-xs font-medium tabular-nums" style="color: #3b82f6;">{{ $running }} running</span>
    @else
        <span class="inline-block w-1.5 h-1.5 rounded-full" style="background-color: #10b981;"></span>
        <span class="text-xs font-medium" style="color: #71717a;">idle</span>
    @endif
</div>
