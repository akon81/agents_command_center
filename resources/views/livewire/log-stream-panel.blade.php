<div
    x-data="{
        lines: @js($initialLines),
        channel: null,
        autoScroll: true,

        init() {
            this.$watch('$wire.runId', (id) => {
                this.lines = @js($initialLines);
                this.unsubscribe();
                if (id) this.subscribe(id);
            });
            if ($wire.runId) this.subscribe($wire.runId);
        },

        subscribe(runId) {
            if (!window.Echo) return;
            this.channel = window.Echo.channel('runs.' + runId);
            this.channel.listen('.LogAppended', (e) => {
                this.lines.push(...e.lines);
                if (this.lines.length > 2000) this.lines = this.lines.slice(-2000);
                if (this.autoScroll) this.$nextTick(() => this.scrollToBottom());
            });
        },

        unsubscribe() {
            if (this.channel && window.Echo) {
                window.Echo.leave(this.channel.name ?? ('runs.' + $wire.runId));
                this.channel = null;
            }
        },

        scrollToBottom() {
            const el = this.$refs.terminal;
            if (el) el.scrollTop = el.scrollHeight;
        },

        formatLine(raw) {
            try {
                const obj = JSON.parse(raw);
                const t = obj.type ?? '';
                if (t === 'assistant' && obj.message?.content) {
                    const blocks = obj.message.content;
                    return blocks.map(b => {
                        if (b.type === 'text') return '\x1b[0m' + b.text;
                        if (b.type === 'tool_use') return '\x1b[33m[tool] ' + b.name + '(' + JSON.stringify(b.input).slice(0,80) + ')';
                        return '';
                    }).filter(Boolean).join('\n') || null;
                }
                if (t === 'tool_result') return null;
                if (t === 'result') return '\x1b[32m[result] ' + (obj.result ?? '').slice(0, 200);
                if (t === 'system') return '\x1b[36m[system] ' + (obj.subtype ?? '') + (obj.cwd ? ' cwd=' + obj.cwd : '');
                return '\x1b[90m' + raw.slice(0, 200);
            } catch {
                return raw.length ? '\x1b[90m' + raw.slice(0, 200) : null;
            }
        }
    }"
>
    {{-- Backdrop strip (click to close) --}}
    <div
        x-show="$wire.open"
        x-transition:enter="transition-opacity ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[55]"
        wire:click="close"
        x-cloak
    ></div>

    {{-- Bottom drawer --}}
    <div
        x-show="$wire.open"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="fixed bottom-0 left-0 right-0 z-[58] flex flex-col"
        style="height: 300px; background: #060608; border-top: 1px solid #1f1f23;"
        x-cloak
    >
        {{-- Terminal header --}}
        <div class="flex items-center justify-between px-4 py-2 flex-shrink-0" style="border-bottom: 1px solid #141416;">
            <div class="flex items-center gap-3">
                {{-- Dot --}}
                @if (!$finished)
                    <span class="inline-block w-1.5 h-1.5 rounded-full animate-pulse" style="background-color: #3b82f6;"></span>
                @else
                    <span class="inline-block w-1.5 h-1.5 rounded-full" style="background-color: #52525b;"></span>
                @endif

                <span class="text-xs font-mono font-medium" style="color: #a1a1aa;">
                    {{ $agentSlug }} · run #{{ $runId }}
                    @if (!$finished)
                        <span class="animate-pulse" style="color: #52525b;">  streaming…</span>
                    @else
                        <span style="color: #52525b;">  finished</span>
                    @endif
                </span>

                {{-- Line count --}}
                <span class="text-[10px] tabular-nums" style="color: #3f3f46;" x-text="lines.length + ' lines'"></span>
            </div>

            <div class="flex items-center gap-3">
                {{-- Auto-scroll toggle --}}
                <button
                    @click="autoScroll = !autoScroll"
                    class="text-[10px] px-2 py-0.5 rounded transition-colors"
                    :style="autoScroll ? 'background:#141416;color:#5e6ad2;border:1px solid #2a2a5a;' : 'background:#0a0a0b;color:#3f3f46;border:1px solid #1a1a1e;'"
                    title="Toggle auto-scroll"
                >↓ scroll</button>

                {{-- Clear --}}
                <button
                    @click="lines = []"
                    class="text-[10px] transition-colors"
                    style="color: #3f3f46;"
                    onmouseover="this.style.color='#71717a'"
                    onmouseout="this.style.color='#3f3f46'"
                >clear</button>

                {{-- Close --}}
                <button
                    wire:click="close"
                    class="flex items-center justify-center w-6 h-6 rounded text-xs transition-colors"
                    style="color: #52525b;"
                    onmouseover="this.style.color='#a1a1aa'"
                    onmouseout="this.style.color='#52525b'"
                >✕</button>
            </div>
        </div>

        {{-- Terminal body --}}
        <div
            x-ref="terminal"
            class="flex-1 overflow-y-auto px-4 py-2 font-mono text-xs leading-5 select-text"
            style="color: #71717a;"
            @scroll="autoScroll = ($el.scrollTop + $el.clientHeight >= $el.scrollHeight - 20)"
        >
            <template x-if="lines.length === 0">
                <div class="text-[11px] pt-4 text-center" style="color: #1f1f23;">waiting for output…</div>
            </template>

            <template x-for="(line, idx) in lines" :key="idx">
                <div
                    x-show="formatLine(line) !== null"
                    x-html="formatLine(line)
                        ?.replace(/\x1b\[0m/g, '<span style=\'color:#e8e8ea\'>')
                        ?.replace(/\x1b\[33m/g, '<span style=\'color:#f59e0b\'>')
                        ?.replace(/\x1b\[32m/g, '<span style=\'color:#10b981\'>')
                        ?.replace(/\x1b\[36m/g, '<span style=\'color:#38bdf8\'>')
                        ?.replace(/\x1b\[90m/g, '<span style=\'color:#3f3f46\'>')
                        ?? ''"
                    class="whitespace-pre-wrap break-all"
                ></div>
            </template>
        </div>
    </div>
</div>
