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

    {{-- Slide-over panel --}}
    <div
        x-show="$wire.open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed top-0 right-0 z-[70] h-full flex flex-col"
        style="width: 460px; background: #0f0f11; border-left: 1px solid #1f1f23;"
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
                        {{ $agent->layer }} · {{ $agent->model }}
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

        {{-- Messages --}}
        <div
            class="flex-1 overflow-y-auto px-5 py-5 space-y-4"
            x-data
            x-init="$el.scrollTop = $el.scrollHeight"
            x-on:livewire:update.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
        >
            @forelse ($dialogs as $msg)
                @if ($msg->role === 'user')
                <div class="flex justify-end">
                    <div class="max-w-[75%] px-4 py-2.5 rounded-2xl rounded-tr-sm text-sm leading-relaxed"
                         style="background-color: #5e6ad2; color: #fff;">
                        {{ $msg->content }}
                    </div>
                </div>
                @else
                <div class="flex justify-start">
                    <div class="max-w-[75%] px-4 py-2.5 rounded-2xl rounded-tl-sm text-sm leading-relaxed whitespace-pre-wrap"
                         style="background-color: #141416; color: #a1a1aa; border: 1px solid #1f1f23;">
                        {{ $msg->content }}
                    </div>
                </div>
                @endif
            @empty
                <div class="flex flex-col items-center justify-center h-full text-center" style="color: #3f3f46;">
                    <div class="text-3xl mb-3 opacity-50">💬</div>
                    <div class="text-sm font-medium mb-1" style="color: #52525b;">No conversation yet</div>
                    <div class="text-xs" style="color: #3f3f46;">Send a message to start</div>
                </div>
            @endforelse

            {{-- Running indicator --}}
            @if ($isRunning)
            <div class="flex justify-start items-end gap-2">
                <div class="px-4 py-2.5 rounded-2xl rounded-tl-sm text-xs"
                     style="background-color: #141416; color: #71717a; border: 1px solid #1f1f23;">
                    @if ($currentAction)
                        <span class="animate-pulse">⚡ {{ $currentAction }}</span>
                    @else
                        <span class="flex items-center gap-2">
                            <span class="animate-pulse" style="color: #5e6ad2;">●</span>
                            Agent is working…
                        </span>
                    @endif
                </div>
                <button
                    wire:click="openLogs"
                    class="text-[10px] px-2 py-1 rounded-md mb-0.5 transition-colors flex-shrink-0"
                    style="background-color: #0a0a0b; color: #52525b; border: 1px solid #1a1a1e;"
                    onmouseover="this.style.color='#a1a1aa';this.style.borderColor='#2a2a3a'"
                    onmouseout="this.style.color='#52525b';this.style.borderColor='#1a1a1e'"
                    title="Open live log stream"
                >logs ↗</button>
            </div>
            @endif
        </div>

        {{-- Input --}}
        <div class="px-5 py-4 flex-shrink-0" style="border-top: 1px solid #1f1f23;">
            <form wire:submit="submit">
                <textarea
                    wire:model="prompt"
                    rows="3"
                    placeholder="Type a prompt…"
                    @disabled($isRunning)
                    class="w-full resize-none rounded-xl px-4 py-3 text-sm outline-none transition-colors"
                    style="background-color: #141416; color: #e8e8ea; border: 1px solid #1f1f23; font-family: inherit;"
                    onfocus="this.style.borderColor='#5e6ad2'"
                    onblur="this.style.borderColor='#1f1f23'"
                    x-on:keydown.meta.enter.prevent="$el.closest('form').requestSubmit()"
                    x-on:keydown.ctrl.enter.prevent="$el.closest('form').requestSubmit()"
                ></textarea>

                @error('prompt')
                    <div class="mt-1 text-xs" style="color: #ef4444;">{{ $message }}</div>
                @enderror

                <div class="flex items-center justify-between mt-2">
                    <span class="text-[10px]" style="color: #3f3f46;">⌘↵ to send</span>
                    <button
                        type="submit"
                        @disabled($isRunning)
                        class="px-4 py-1.5 rounded-lg text-sm font-medium transition-opacity"
                        style="background-color: #5e6ad2; color: #fff;"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50"
                        @class(['opacity-50' => $isRunning])
                    >
                        @if ($isRunning)
                            <span class="animate-pulse">Running…</span>
                        @else
                            Send
                        @endif
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>
