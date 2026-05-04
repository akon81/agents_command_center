<div>
    {{-- Backdrop --}}
    <div
        x-show="$wire.open"
        x-transition:enter="transition-opacity ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[80]"
        style="background: rgba(0,0,0,0.8);"
        x-cloak
    ></div>

    {{-- Editor modal --}}
    <div
        x-show="$wire.open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-data="{
            lineCount: 0,
            charCount: 0,
            updateCounts() {
                const v = this.$refs.editor?.value ?? '';
                this.lineCount = v ? v.split('\n').length : 0;
                this.charCount = v.length;
            },
            handleTab(e) {
                e.preventDefault();
                const el = e.target;
                const start = el.selectionStart;
                const end   = el.selectionEnd;
                el.value = el.value.slice(0, start) + '    ' + el.value.slice(end);
                el.selectionStart = el.selectionEnd = start + 4;
                el.dispatchEvent(new Event('input'));
            },
            handleSave(e) {
                if ((e.metaKey || e.ctrlKey) && e.key === 's') {
                    e.preventDefault();
                    $wire.save();
                }
            }
        }"
        x-init="updateCounts(); $watch('$wire.content', () => $nextTick(() => updateCounts()))"
        @keydown.window="handleSave($event)"
        class="fixed z-[90] flex flex-col rounded-xl overflow-hidden"
        style="
            top: 4vh; left: 5vw; right: 5vw; bottom: 4vh;
            background: #0a0a0b;
            border: 1px solid #1f1f23;
        "
        x-cloak
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-3 flex-shrink-0" style="border-bottom: 1px solid #1f1f23; background: #0f0f11;">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-3 h-3 rounded flex-shrink-0" style="background-color: #5e6ad2;"></div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold" style="color: #e8e8ea; font-family: var(--font-mono, monospace);">
                            {{ $agentSlug }}
                        </span>
                        @if ($dirty)
                            <span class="text-[10px] px-1.5 py-0.5 rounded" style="background-color: #2a1f00; color: #f59e0b; border: 1px solid #3d2f00;">
                                unsaved
                            </span>
                        @elseif ($savedMsg)
                            <span class="text-[10px]" style="color: #10b981;">✓ {{ $savedMsg }}</span>
                        @endif
                    </div>
                    <div class="text-[10px] truncate mt-px" style="color: #3f3f46; font-family: var(--font-mono, monospace);">
                        {{ $filePath ?: 'no file path set' }}
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 flex-shrink-0">
                {{-- Stats --}}
                <span class="text-[10px] tabular-nums" style="color: #3f3f46;">
                    <span x-text="lineCount"></span>L · <span x-text="charCount"></span>C
                </span>

                {{-- Keyboard hint --}}
                <span class="text-[10px]" style="color: #3f3f46;">⌘S to save</span>

                {{-- Close --}}
                <button
                    wire:click="close"
                    class="flex items-center justify-center w-7 h-7 rounded-md text-sm transition-colors"
                    style="color: #52525b;"
                    onmouseover="this.style.backgroundColor='#141416';this.style.color='#a1a1aa'"
                    onmouseout="this.style.backgroundColor='transparent';this.style.color='#52525b'"
                >✕</button>
            </div>
        </div>

        {{-- No file path warning --}}
        @if (!$filePath)
        <div class="px-5 py-3 flex-shrink-0 flex items-center gap-2 text-xs" style="background-color: #1a1000; border-bottom: 1px solid #3d2f00; color: #f59e0b;">
            <span>⚠</span>
            <span>No file path configured. Set <strong>File path</strong> in Edit Agent (✎) first, then reopen this editor.</span>
        </div>
        @endif

        {{-- Error --}}
        @if ($errorMsg)
        <div class="px-5 py-2 flex-shrink-0 text-xs" style="background-color: #1a0505; border-bottom: 1px solid #3f1010; color: #ef4444;">
            ✗ {{ $errorMsg }}
        </div>
        @endif

        {{-- Editor area --}}
        <div class="flex flex-1 min-h-0">
            {{-- Line numbers --}}
            <div
                class="flex-shrink-0 text-right pr-4 pl-3 py-4 select-none overflow-hidden"
                style="
                    width: 56px;
                    background: #080809;
                    border-right: 1px solid #141416;
                    font-family: var(--font-mono, monospace);
                    font-size: 12px;
                    line-height: 20px;
                    color: #2a2a2e;
                "
                x-data="{ nums: [] }"
                x-init="$watch('$wire.content', v => { const n = v ? v.split('\n').length : 1; nums = Array.from({length: n}, (_, i) => i+1); })"
            >
                <template x-for="n in nums" :key="n">
                    <div x-text="n"></div>
                </template>
            </div>

            {{-- Textarea --}}
            <textarea
                x-ref="editor"
                wire:model="content"
                @keydown.tab.prevent="handleTab($event)"
                @input="updateCounts()"
                spellcheck="false"
                @disabled(!$filePath)
                class="flex-1 resize-none outline-none py-4 px-4 w-full"
                style="
                    background: #0a0a0b;
                    color: #d4d4d8;
                    font-family: var(--font-mono, monospace);
                    font-size: 13px;
                    line-height: 20px;
                    caret-color: #5e6ad2;
                    tab-size: 4;
                "
                placeholder="{{ $filePath ? 'Start typing…' : 'Set file path first' }}"
            ></textarea>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between px-5 py-3 flex-shrink-0" style="border-top: 1px solid #141416; background: #0f0f11;">
            <div class="text-[10px]" style="color: #3f3f46;">
                @if ($fileExists)
                    Markdown · UTF-8
                @elseif ($filePath)
                    <span style="color: #f59e0b;">File does not exist yet — will be created on Save</span>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <button
                    wire:click="close"
                    type="button"
                    class="px-4 py-1.5 rounded-lg text-sm transition-colors"
                    style="background-color: #141416; color: #71717a; border: 1px solid #1f1f23;"
                    onmouseover="this.style.backgroundColor='#1f1f23'"
                    onmouseout="this.style.backgroundColor='#141416'"
                >Close</button>

                <button
                    wire:click="save"
                    type="button"
                    @disabled(!$filePath)
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors"
                    style="background-color: {{ $filePath ? '#5e6ad2' : '#1f1f23' }}; color: {{ $filePath ? '#fff' : '#3f3f46' }}; cursor: {{ $filePath ? 'pointer' : 'not-allowed' }};"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50"
                    wire:target="save"
                >
                    <span wire:loading.remove wire:target="save">Save</span>
                    <span wire:loading wire:target="save" class="animate-pulse">Saving…</span>
                </button>
            </div>
        </div>
    </div>
</div>
