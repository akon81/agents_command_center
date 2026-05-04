<?php

namespace App\Livewire;

use App\Models\Agent;
use Livewire\Attributes\On;
use Livewire\Component;

class AgentCard extends Component
{
    public Agent $agent;

    public string $status = 'idle';

    public ?string $currentAction = null;

    public ?int $progress = null;

    public ?string $lastActivity = null;

    public ?int $activeRunId = null;

    public function mount(Agent $agent): void
    {
        $this->agent = $agent;

        $latestRun = $agent->runs()->latest('id')->first();

        if ($latestRun !== null) {
            $this->status = match ($latestRun->status) {
                'running', 'pending' => 'busy',
                'completed'          => 'completed',
                'failed', 'cancelled' => 'failed',
                default              => 'idle',
            };
            $this->progress       = $latestRun->progress;
            $this->currentAction  = $latestRun->current_action;
            $this->lastActivity   = ($latestRun->finished_at ?? $latestRun->started_at)?->diffForHumans();

            if (in_array($latestRun->status, ['running', 'pending'])) {
                $this->activeRunId = $latestRun->id;
            }
        }
    }

    #[On('agent-run-started.{agent.id}')]
    public function onRunStarted(?int $run_id = null): void
    {
        $this->status = 'busy';
        $this->currentAction = null;
        $this->progress = 0;
        $this->activeRunId = $run_id;
    }

    #[On('agent-run-finished.{agent.id}')]
    public function onRunFinished(?string $status = null): void
    {
        $this->status = $status === 'completed' ? 'completed' : 'failed';
        $this->progress = $status === 'completed' ? 100 : $this->progress;
        $this->currentAction = null;
        $this->lastActivity = now()->diffForHumans();
        $this->activeRunId = null;
    }

    #[On('agent-progressed.{agent.id}')]
    public function onProgressed(?int $progress = null): void
    {
        if ($progress !== null) {
            $this->progress = $progress;
        }
    }

    #[On('agent-action-changed.{agent.id}')]
    public function onActionChanged(?string $current_action = null): void
    {
        $this->currentAction = $current_action;
    }

    public function openDialog(): void
    {
        $this->dispatch('open-dialog', agentId: $this->agent->id);
    }

    public function openHistory(): void
    {
        $this->dispatch('open-history', agentId: $this->agent->id);
    }

    public function openEdit(): void
    {
        $this->dispatch('open-edit', agentId: $this->agent->id);
    }

    public function openInstructions(): void
    {
        $this->dispatch('open-claude-md', agentId: $this->agent->id);
    }

    #[On('agent-updated.{agent.id}')]
    public function onAgentUpdated(): void
    {
        $this->agent = Agent::findOrFail($this->agent->id);
    }

    public function killRun(): void
    {
        $run = $this->activeRunId
            ? \App\Models\Run::find($this->activeRunId)
            : \App\Models\Run::where('agent_id', $this->agent->id)
                ->whereIn('status', ['running', 'pending'])
                ->latest()
                ->first();

        if ($run === null) {
            return;
        }

        if ($run->pid) {
            if (PHP_OS_FAMILY === 'Windows') {
                exec("taskkill /F /T /PID {$run->pid} 2>&1");
            } else {
                exec("kill -9 {$run->pid} 2>&1");
            }
        }

        $run->update(['status' => 'failed', 'finished_at' => now(), 'pid' => null]);
        $run->task?->update(['status' => 'failed', 'finished_at' => now()]);

        $this->status = 'failed';
        $this->currentAction = null;
        $this->activeRunId = null;
        $this->lastActivity = now()->diffForHumans();
    }

    public function render()
    {
        return view('livewire.agent-card');
    }
}
