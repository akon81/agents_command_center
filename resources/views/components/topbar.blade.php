<header
    class="fixed top-0 left-0 right-0 z-50 h-12 flex items-center justify-between px-5 border-b"
    style="background-color: rgba(10,10,11,0.85); backdrop-filter: blur(12px); border-color: #1f1f23;"
>
    {{-- Left: logo + title --}}
    <div class="flex items-center gap-3">
        <div class="w-5 h-5 rounded-md flex items-center justify-center flex-shrink-0" style="background-color: #5e6ad2;">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="6" cy="6" r="3" fill="white" fill-opacity="0.9"/>
                <circle cx="2" cy="2" r="1.5" fill="white" fill-opacity="0.5"/>
                <circle cx="10" cy="2" r="1.5" fill="white" fill-opacity="0.5"/>
                <circle cx="2" cy="10" r="1.5" fill="white" fill-opacity="0.5"/>
                <circle cx="10" cy="10" r="1.5" fill="white" fill-opacity="0.5"/>
            </svg>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold tracking-tight" style="color: #5e6ad2;">Agent Command Center</span>
            <span class="text-xs" style="color: #1f1f23;">/</span>
            <span class="text-xs" style="color: #71717a;">Claude Code Orchestration</span>
        </div>
    </div>

    {{-- Right: agent count + clock --}}
    <div
        class="flex items-center gap-4"
        x-data="{
            time: '',
            init() {
                this.updateTime();
                setInterval(() => this.updateTime(), 1000);
            },
            updateTime() {
                this.time = new Date().toLocaleTimeString('pl-PL');
            }
        }"
    >
        {{-- Live running counter --}}
        <livewire:running-counter />

        {{-- Separator --}}
        <div class="w-px h-3.5" style="background-color: #1f1f23;"></div>

        {{-- Clock --}}
        <span class="text-xs tabular-nums font-mono" style="color: #71717a;" x-text="time"></span>
    </div>
</header>
