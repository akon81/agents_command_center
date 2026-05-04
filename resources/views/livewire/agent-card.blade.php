@php
    $statusStyles = [
        'idle'      => ['pill' => 'status-pill-idle',      'dot' => '#52525b', 'label' => 'idle'],
        'busy'      => ['pill' => 'status-pill-busy',      'dot' => '#3b82f6', 'label' => 'busy'],
        'completed' => ['pill' => 'status-pill-completed', 'dot' => '#10b981', 'label' => 'done'],
        'failed'    => ['pill' => 'status-pill-failed',    'dot' => '#ef4444', 'label' => 'error'],
    ];
    $style = $statusStyles[$status] ?? $statusStyles['idle'];

    $layerIcons = [
        'orchestration' => '⚙',
        'planning'      => '🗺',
        'execution'     => '⚡',
        'review'        => '🔍',
        'discovery'     => '💡',
        'utility'       => '🔧',
    ];
    $icon = $layerIcons[$agent->layer] ?? '•';
@endphp

<div
    x-data
    x-init="initAgentCard({{ $agent->id }}, '{{ $agent->slug }}')"
    wire:click="openDialog"
    class="group relative flex flex-col gap-3 rounded-xl p-4 cursor-pointer transition-colors duration-150"
    style="background-color: #141416; border: 1px solid #1f1f23;"
    onmouseover="this.style.backgroundColor='#1a1a1f'"
    onmouseout="this.style.backgroundColor='#141416'"
>
    {{-- Top row: dot + slug + status pill --}}
    <div class="flex items-center justify-between gap-2 min-w-0">
        <div class="flex items-center gap-2 min-w-0">
            {{-- Colored dot --}}
            <span
                class="inline-block w-2 h-2 rounded-full flex-shrink-0"
                style="background-color: {{ $agent->color ?? '#5e6ad2' }};"
            ></span>

            {{-- Slug --}}
            <span
                class="text-sm font-medium truncate"
                style="color: #e8e8ea; font-family: var(--font-mono, monospace);"
                title="{{ $agent->slug }}"
            >
                {{ $agent->slug }}
            </span>
        </div>

        {{-- Status pill + kill button --}}
        <div class="flex items-center gap-1.5 flex-shrink-0">
            @if ($status === 'busy')
                <button
                    wire:click.stop="killRun"
                    class="text-[10px] px-1.5 py-0.5 rounded leading-none transition-colors"
                    style="background-color: #3f1010; color: #ef4444; border: 1px solid #7f1d1d;"
                    onmouseover="this.style.backgroundColor='#7f1d1d'"
                    onmouseout="this.style.backgroundColor='#3f1010'"
                    title="Stop agent"
                >■</button>
            @endif
            <span class="status-pill {{ $style['pill'] }} {{ $status === 'busy' ? 'animate-pulse' : '' }}">
                <span
                    class="inline-block w-1.5 h-1.5 rounded-full"
                    style="background-color: {{ $style['dot'] }};"
                ></span>
                {{ $style['label'] }}
            </span>
        </div>
    </div>

    {{-- Layer + Model --}}
    <div class="flex items-center gap-1.5 -mt-1">
        <span class="text-[10px] font-semibold uppercase tracking-widest" style="color: #71717a;">
            {{ $icon }} {{ $agent->layer }}
        </span>
        @if ($agent->model)
            <span class="text-[10px]" style="color: #52525b;">·</span>
            <span class="text-[10px] truncate" style="color: #52525b; font-family: var(--font-mono, monospace);">
                {{ $agent->model }}
            </span>
        @endif
    </div>

    {{-- Progress bar --}}
    @if ($progress !== null)
        <div class="space-y-1">
            <div class="h-1 rounded-full overflow-hidden" style="background-color: #1f1f23;">
                <div
                    class="h-full rounded-full transition-all duration-300"
                    style="width: {{ $progress }}%; background-color: {{ $agent->color ?? '#5e6ad2' }};"
                ></div>
            </div>
            <span class="text-[10px]" style="color: #71717a;">{{ $progress }}%</span>
        </div>
    @endif

    {{-- Current action --}}
    @if ($currentAction)
        <div
            class="text-xs truncate px-2 py-1 rounded-md"
            style="background-color: #0a0a0b; color: #71717a;"
            title="{{ $currentAction }}"
        >
            {{ $currentAction }}
        </div>
    @endif

    {{-- Bottom meta --}}
    <div class="flex items-center justify-between mt-auto pt-1">
        <span class="text-[10px]" style="color: #52525b;">
            {{ $style['label'] }} · {{ $lastActivity ?? 'never run' }}
        </span>
        <div class="flex items-center gap-2">
            <button
                wire:click.stop="openHistory"
                class="text-[10px] leading-none transition-colors"
                style="color: #3f3f46;"
                onmouseover="this.style.color='#71717a'"
                onmouseout="this.style.color='#3f3f46'"
                title="Run history"
            >history</button>
            @if ($agent->is_active)
                <span class="inline-block w-1.5 h-1.5 rounded-full" style="background-color: #10b981;" title="active"></span>
            @else
                <span class="inline-block w-1.5 h-1.5 rounded-full" style="background-color: #52525b;" title="inactive"></span>
            @endif
        </div>
    </div>
</div>
