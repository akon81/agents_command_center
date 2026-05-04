<div>
    {{-- Backdrop --}}
    <div
        x-show="$wire.open"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[60]"
        style="background: rgba(0,0,0,0.6);"
        wire:click="close"
        x-cloak
    ></div>

    {{-- Slide-over panel (from LEFT) --}}
    <div
        x-show="$wire.open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed top-0 left-0 z-[70] h-full flex flex-col"
        style="width: 500px; background: #0f0f11; border-right: 1px solid #1f1f23;"
        x-cloak
    >
        @if ($agent)
        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 flex-shrink-0" style="border-bottom: 1px solid #1f1f23;">
            <div class="flex items-center gap-3">
                <span class="inline-block w-2.5 h-2.5 rounded-full flex-shrink-0"
                      style="background-color: {{ $agent->color ?? '#5e6ad2' }};"></span>
                <div>
                    <div class="text-sm font-semibold" style="color: #e8e8ea; font-family: var(--font-mono, monospace);">
                        {{ $agent->slug }}
                    </div>
                    <div class="text-[10px] uppercase tracking-widest mt-px" style="color: #52525b;">
                        run history · {{ $runs->count() }} runs
                    </div>
                </div>
            </div>
            <button wire:click="close"
                    class="flex items-center justify-center w-7 h-7 rounded-md text-sm transition-colors"
                    style="color: #52525b;"
                    onmouseover="this.style.backgroundColor='#141416';this.style.color='#a1a1aa'"
                    onmouseout="this.style.backgroundColor='transparent';this.style.color='#52525b'">
                ✕
            </button>
        </div>

        {{-- Run list --}}
        <div class="flex-1 overflow-y-auto">
            @forelse ($runs as $run)
                @php
                    $statusColor = match($run->status) {
                        'completed' => '#10b981',
                        'failed'    => '#ef4444',
                        'running'   => '#3b82f6',
                        'pending'   => '#71717a',
                        default     => '#52525b',
                    };
                    $statusIcon = match($run->status) {
                        'completed' => '✓',
                        'failed'    => '✗',
                        'running'   => '⟳',
                        'pending'   => '…',
                        default     => '·',
                    };
                    $duration = $run->duration_ms
                        ? ($run->duration_ms < 60000
                            ? round($run->duration_ms / 1000, 1) . 's'
                            : floor($run->duration_ms / 60000) . 'm ' . round(($run->duration_ms % 60000) / 1000) . 's')
                        : null;
                    $isExpanded = $expandedRunId === $run->id;
                @endphp

                <div style="border-bottom: 1px solid #141416;">
                    {{-- Run row --}}
                    <button
                        wire:click="toggleRun({{ $run->id }})"
                        class="w-full text-left px-5 py-3 flex items-start gap-3 transition-colors"
                        style="background-color: {{ $isExpanded ? '#141416' : 'transparent' }};"
                        onmouseover="this.style.backgroundColor='#141416'"
                        onmouseout="this.style.backgroundColor='{{ $isExpanded ? '#141416' : 'transparent' }}'"
                    >
                        {{-- Status icon --}}
                        <span class="text-sm font-bold flex-shrink-0 mt-px w-4 text-center"
                              style="color: {{ $statusColor }};">{{ $statusIcon }}</span>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-0.5">
                                <span class="text-[10px] uppercase tracking-wider font-semibold"
                                      style="color: {{ $statusColor }};">{{ $run->status }}</span>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    @if ($duration)
                                        <span class="text-[10px]" style="color: #3f3f46;">{{ $duration }}</span>
                                    @endif
                                    <span class="text-[10px]" style="color: #3f3f46;">
                                        {{ $run->started_at?->format('d.m H:i') ?? $run->created_at->format('d.m H:i') }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-xs truncate" style="color: #71717a;">
                                {{ str($run->task->prompt ?? '—')->limit(80) }}
                            </div>
                        </div>

                        {{-- Expand indicator --}}
                        <span class="text-xs flex-shrink-0 mt-px" style="color: #3f3f46;">
                            {{ $isExpanded ? '▲' : '▼' }}
                        </span>
                    </button>

                    {{-- Expanded: dialogs --}}
                    @if ($isExpanded)
                    <div class="px-5 pb-4 space-y-3" style="background-color: #0a0a0b;">
                        @forelse ($expandedDialogs as $msg)
                            @if ($msg->role === 'user')
                            <div class="flex justify-end">
                                <div class="max-w-[80%] px-3 py-2 rounded-xl rounded-tr-sm text-xs leading-relaxed"
                                     style="background-color: #1e1e4a; color: #a5b4fc; border: 1px solid #2e2e6a;">
                                    {{ $msg->content }}
                                </div>
                            </div>
                            @else
                            <div class="flex justify-start gap-2">
                                <div class="max-w-[80%] px-3 py-2 rounded-xl rounded-tl-sm text-xs leading-relaxed whitespace-pre-wrap"
                                     style="background-color: #141416; color: #a1a1aa; border: 1px solid #1f1f23;">
                                    {{ $msg->content }}
                                </div>
                            </div>
                            @if ($msg->tokens)
                            <div class="flex justify-start">
                                <span class="text-[10px] px-2 py-0.5 rounded"
                                      style="background-color: #141416; color: #3f3f46; border: 1px solid #1a1a1e;">
                                    {{ number_format($msg->tokens) }} tokens
                                </span>
                            </div>
                            @endif
                            @endif
                        @empty
                            <div class="text-xs text-center py-2" style="color: #3f3f46;">no dialog recorded</div>
                        @endforelse

                        @if ($run->exit_code !== null && $run->exit_code !== 0)
                        <div class="text-[10px] px-2 py-1 rounded" style="background-color: #1a0a0a; color: #ef4444; border: 1px solid #3f1010;">
                            exit code {{ $run->exit_code }}
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-full text-center" style="color: #3f3f46;">
                    <div class="text-3xl mb-3 opacity-40">📋</div>
                    <div class="text-sm font-medium mb-1" style="color: #52525b;">No runs yet</div>
                    <div class="text-xs">Start the agent to record history</div>
                </div>
            @endforelse
        </div>
        @endif
    </div>
</div>
