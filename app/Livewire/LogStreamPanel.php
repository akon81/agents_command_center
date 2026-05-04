<?php

namespace App\Livewire;

use App\Models\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class LogStreamPanel extends Component
{
    public bool $open = false;
    public ?int $runId = null;
    public string $agentSlug = '';
    public array $initialLines = [];
    public bool $finished = false;

    #[On('open-logs')]
    public function openFor(int $runId, string $agentSlug): void
    {
        $this->runId      = $runId;
        $this->agentSlug  = $agentSlug;
        $this->finished   = false;

        // Seed with existing lines so panel shows history even if opened mid-run
        $this->initialLines = Log::where('run_id', $runId)
            ->where('stream', 'stdout')
            ->orderBy('seq')
            ->pluck('content')
            ->toArray();

        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    #[On('panel-run-finished')]
    public function onRunFinished(int $agentId): void
    {
        // Mark as finished so streaming indicator turns off
        // Keep panel open — user may want to review the output
        $this->finished = true;
    }

    public function render()
    {
        return view('livewire.log-stream-panel');
    }
}
