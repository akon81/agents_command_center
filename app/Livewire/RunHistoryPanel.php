<?php

namespace App\Livewire;

use App\Models\Agent;
use App\Models\Dialog;
use App\Models\Run;
use Livewire\Attributes\On;
use Livewire\Component;

class RunHistoryPanel extends Component
{
    public bool $open = false;
    public int $agentId = 0;
    public ?int $expandedRunId = null;

    #[On('open-history')]
    public function openFor(int $agentId): void
    {
        if ($this->agentId !== $agentId) {
            $this->agentId = $agentId;
            $this->expandedRunId = null;
        }
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function toggleRun(int $runId): void
    {
        $this->expandedRunId = $this->expandedRunId === $runId ? null : $runId;
    }

    public function render()
    {
        $agent = $this->agentId ? Agent::find($this->agentId) : null;

        $runs = $agent
            ? Run::where('agent_id', $this->agentId)
                ->with('task')
                ->latest()
                ->limit(50)
                ->get()
            : collect();

        $expandedDialogs = $this->expandedRunId
            ? Dialog::where('run_id', $this->expandedRunId)->orderBy('created_at')->get()
            : collect();

        $agentStats = null;
        if ($agent) {
            $finished  = $runs->whereIn('status', ['completed', 'failed']);
            $completed = $runs->where('status', 'completed');
            $agentStats = [
                'total'        => $runs->count(),
                'success_rate' => $finished->count() > 0
                    ? round($completed->count() / $finished->count() * 100)
                    : null,
                'avg_ms'       => (int) $runs->whereNotNull('duration_ms')->avg('duration_ms'),
                'tokens'       => Dialog::where('agent_id', $this->agentId)->where('role', 'assistant')->sum('tokens'),
            ];
        }

        return view('livewire.run-history-panel', [
            'agent'           => $agent,
            'runs'            => $runs,
            'expandedDialogs' => $expandedDialogs,
            'agentStats'      => $agentStats,
        ]);
    }
}
