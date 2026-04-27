<div>
    {{-- Section header --}}
    <div class="flex items-center gap-3 px-6 pt-6 pb-4">
        <h1 class="text-base font-semibold" style="color: #e8e8ea;">Agents</h1>
        <span
            class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium"
            style="background-color: #1f1f23; color: #71717a;"
        >
            {{ $this->agents->count() }}
        </span>
    </div>

    {{-- Grouped by layer --}}
    @foreach ($layerOrder as $layer => $priority)
        @if ($grouped->has($layer))
            @php $layerAgents = $grouped->get($layer); @endphp

            {{-- Sticky layer label --}}
            <div
                class="sticky top-12 z-10 px-6 py-2 flex items-center gap-2"
                style="background-color: rgba(10,10,11,0.9); backdrop-filter: blur(8px);"
            >
                <span class="text-base leading-none">{{ $layerIcons[$layer] ?? '•' }}</span>
                <span
                    class="text-[10px] font-semibold tracking-widest uppercase"
                    style="color: #71717a;"
                >
                    {{ $layer }}
                </span>
                <div class="flex-1 h-px ml-2" style="background-color: #1f1f23;"></div>
                <span class="text-[10px]" style="color: #52525b;">{{ $layerAgents->count() }}</span>
            </div>

            {{-- Grid for this layer --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 px-6 pb-6">
                @foreach ($layerAgents as $agent)
                    <livewire:agent-card :agent="$agent" :key="$agent->id" />
                @endforeach
            </div>
        @endif
    @endforeach
</div>
