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
        }
    }

    #[On('agent-run-started.{agent.id}')]
    public function onRunStarted(array $data): void
    {
        $this->status = 'busy';
        $this->currentAction = null;
        $this->progress = 0;
    }

    #[On('agent-run-finished.{agent.id}')]
    public function onRunFinished(array $data): void
    {
        $this->status = $data['status'] === 'completed' ? 'completed' : 'failed';
        $this->progress = $data['status'] === 'completed' ? 100 : $this->progress;
        $this->currentAction = null;
        $this->lastActivity = now()->diffForHumans();
    }

    #[On('agent-progressed.{agent.id}')]
    public function onProgressed(array $data): void
    {
        $this->progress = $data['progress'];
    }

    #[On('agent-action-changed.{agent.id}')]
    public function onActionChanged(array $data): void
    {
        $this->currentAction = $data['current_action'];
    }

    public function openDialog(): void
    {
        $this->dispatch('open-dialog', agentId: $this->agent->id);
    }

    public function render()
    {
        return view('livewire.agent-card');
    }
}
