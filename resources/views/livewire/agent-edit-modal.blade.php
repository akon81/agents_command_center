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
        class="fixed inset-0 z-[60] flex items-center justify-center p-6"
        style="background: rgba(0,0,0,0.7);"
        wire:click.self="close"
        x-cloak
    >
        {{-- Modal --}}
        <div
            x-show="$wire.open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-lg flex flex-col rounded-2xl"
            style="background: #0f0f11; border: 1px solid #1f1f23; max-height: 90vh;"
            x-cloak
        >
            @if ($agent)
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 flex-shrink-0" style="border-bottom: 1px solid #1f1f23;">
                <div class="flex items-center gap-3">
                    <span class="inline-block w-2.5 h-2.5 rounded-full"
                          style="background-color: {{ $agent->color ?? '#5e6ad2' }};"></span>
                    <div>
                        <div class="text-sm font-semibold" style="color: #e8e8ea; font-family: var(--font-mono, monospace);">
                            {{ $agent->slug }}
                        </div>
                        <div class="text-[10px] uppercase tracking-widest mt-px" style="color: #52525b;">
                            edit agent
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

            {{-- Form --}}
            <div class="flex-1 overflow-y-auto px-6 py-5">
                <form wire:submit="save" id="agent-edit-form" class="space-y-4">

                    {{-- Name --}}
                    <div>
                        <label class="block text-[11px] font-medium uppercase tracking-wider mb-1.5" style="color: #71717a;">Name</label>
                        <input
                            wire:model="name"
                            type="text"
                            class="w-full px-3 py-2 rounded-lg text-sm outline-none transition-colors"
                            style="background-color: #141416; color: #e8e8ea; border: 1px solid #1f1f23;"
                            onfocus="this.style.borderColor='#5e6ad2'"
                            onblur="this.style.borderColor='#1f1f23'"
                        />
                        @error('name') <div class="mt-1 text-[11px]" style="color: #ef4444;">{{ $message }}</div> @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-[11px] font-medium uppercase tracking-wider mb-1.5" style="color: #71717a;">Description</label>
                        <textarea
                            wire:model="description"
                            rows="2"
                            class="w-full px-3 py-2 rounded-lg text-sm outline-none resize-none transition-colors"
                            style="background-color: #141416; color: #e8e8ea; border: 1px solid #1f1f23; font-family: inherit;"
                            onfocus="this.style.borderColor='#5e6ad2'"
                            onblur="this.style.borderColor='#1f1f23'"
                        ></textarea>
                        @error('description') <div class="mt-1 text-[11px]" style="color: #ef4444;">{{ $message }}</div> @enderror
                    </div>

                    {{-- Model + Layer row --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium uppercase tracking-wider mb-1.5" style="color: #71717a;">Model</label>
                            <input
                                wire:model="model"
                                type="text"
                                placeholder="e.g. claude-sonnet-4-6"
                                class="w-full px-3 py-2 rounded-lg text-sm outline-none transition-colors"
                                style="background-color: #141416; color: #e8e8ea; border: 1px solid #1f1f23; font-family: var(--font-mono, monospace);"
                                onfocus="this.style.borderColor='#5e6ad2'"
                                onblur="this.style.borderColor='#1f1f23'"
                            />
                            @error('model') <div class="mt-1 text-[11px]" style="color: #ef4444;">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-medium uppercase tracking-wider mb-1.5" style="color: #71717a;">Layer</label>
                            <select
                                wire:model="layer"
                                class="w-full px-3 py-2 rounded-lg text-sm outline-none transition-colors"
                                style="background-color: #141416; color: #e8e8ea; border: 1px solid #1f1f23;"
                                onfocus="this.style.borderColor='#5e6ad2'"
                                onblur="this.style.borderColor='#1f1f23'"
                            >
                                @foreach(['orchestration','planning','execution','review','discovery','utility'] as $l)
                                    <option value="{{ $l }}">{{ ucfirst($l) }}</option>
                                @endforeach
                            </select>
                            @error('layer') <div class="mt-1 text-[11px]" style="color: #ef4444;">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Color + Active row --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium uppercase tracking-wider mb-1.5" style="color: #71717a;">Color</label>
                            <div class="flex items-center gap-2">
                                <input
                                    wire:model="color"
                                    type="color"
                                    class="w-10 h-9 rounded-lg cursor-pointer"
                                    style="background-color: #141416; border: 1px solid #1f1f23; padding: 2px;"
                                />
                                <input
                                    wire:model="color"
                                    type="text"
                                    maxlength="7"
                                    class="flex-1 px-3 py-2 rounded-lg text-sm outline-none transition-colors"
                                    style="background-color: #141416; color: #e8e8ea; border: 1px solid #1f1f23; font-family: var(--font-mono, monospace);"
                                    onfocus="this.style.borderColor='#5e6ad2'"
                                    onblur="this.style.borderColor='#1f1f23'"
                                />
                            </div>
                            @error('color') <div class="mt-1 text-[11px]" style="color: #ef4444;">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-medium uppercase tracking-wider mb-1.5" style="color: #71717a;">Active</label>
                            <button
                                type="button"
                                wire:click="$toggle('is_active')"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors w-full"
                                style="background-color: #141416; border: 1px solid #1f1f23; color: {{ $is_active ? '#10b981' : '#52525b' }};"
                            >
                                <span class="inline-block w-2 h-2 rounded-full flex-shrink-0"
                                      style="background-color: {{ $is_active ? '#10b981' : '#52525b' }};"></span>
                                {{ $is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </div>
                    </div>

                    {{-- File path --}}
                    <div>
                        <label class="block text-[11px] font-medium uppercase tracking-wider mb-1.5" style="color: #71717a;">
                            File path <span style="color: #3f3f46; font-weight: normal; text-transform: none;">(optional CLAUDE.md or config)</span>
                        </label>
                        <input
                            wire:model="file_path"
                            type="text"
                            placeholder="/path/to/CLAUDE.md"
                            class="w-full px-3 py-2 rounded-lg text-sm outline-none transition-colors"
                            style="background-color: #141416; color: #e8e8ea; border: 1px solid #1f1f23; font-family: var(--font-mono, monospace);"
                            onfocus="this.style.borderColor='#5e6ad2'"
                            onblur="this.style.borderColor='#1f1f23'"
                        />
                        @error('file_path') <div class="mt-1 text-[11px]" style="color: #ef4444;">{{ $message }}</div> @enderror
                    </div>

                    {{-- Slug (readonly) --}}
                    <div>
                        <label class="block text-[11px] font-medium uppercase tracking-wider mb-1.5" style="color: #3f3f46;">
                            Slug <span style="color: #3f3f46; font-weight: normal;">(read-only · used in Claude CLI)</span>
                        </label>
                        <div class="px-3 py-2 rounded-lg text-sm"
                             style="background-color: #0a0a0b; color: #3f3f46; border: 1px solid #141416; font-family: var(--font-mono, monospace);">
                            {{ $agent->slug }}
                        </div>
                    </div>

                </form>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4 flex-shrink-0" style="border-top: 1px solid #1f1f23;">
                <button
                    wire:click="close"
                    type="button"
                    class="px-4 py-1.5 rounded-lg text-sm transition-colors"
                    style="background-color: #141416; color: #71717a; border: 1px solid #1f1f23;"
                    onmouseover="this.style.backgroundColor='#1f1f23'"
                    onmouseout="this.style.backgroundColor='#141416'"
                >Cancel</button>
                <button
                    form="agent-edit-form"
                    type="submit"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-opacity"
                    style="background-color: #5e6ad2; color: #fff;"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50"
                >Save</button>
            </div>
            @endif
        </div>
    </div>
</div>
