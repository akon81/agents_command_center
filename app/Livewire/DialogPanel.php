<?php

namespace App\Livewire;

use App\Models\Agent;
use App\Models\Dialog;
use App\Services\AgentRunService;
use App\Services\ClaudeCliCommand;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class DialogPanel extends Component
{
    public bool $open = false;
    public int $agentId = 0;
    public bool $isRunning = false;
    public ?string $currentAction = null;
    public ?int $activeRunId = null;

    #[Validate('required|max:4000')]
    public string $prompt = '';

    #[On('open-dialog')]
    public function openFor(int $agentId): void
    {
        if ($this->agentId !== $agentId) {
            $this->agentId      = $agentId;
            $this->prompt       = '';
            $this->currentAction = null;
            $this->resetErrorBag();

            $activeRun          = \App\Models\Run::where('agent_id', $agentId)
                ->whereIn('status', ['running', 'pending'])
                ->latest()
                ->first();
            $this->isRunning    = $activeRun !== null;
            $this->activeRunId  = $activeRun?->id;
            $this->currentAction = $activeRun?->current_action;
        }
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function openLogs(): void
    {
        if (!$this->activeRunId) return;
        $agent = Agent::find($this->agentId);
        $this->dispatch('open-logs', runId: $this->activeRunId, agentSlug: $agent?->slug ?? '');
    }

    public function submit(): void
    {
        $this->validate();
        if (!$this->agentId) return;

        $agent = Agent::findOrFail($this->agentId);
        $run   = app(AgentRunService::class)->createAndDispatch(
            $agent,
            $this->prompt,
            ClaudeCliCommand::AGENTS_WORKSPACE,
        );

        $this->activeRunId  = $run->id;
        $this->isRunning    = true;
        $this->currentAction = null;
        $this->prompt       = '';
        $this->resetErrorBag();
    }

    #[On('panel-action-changed')]
    public function onActionChanged(int $agentId, string $current_action): void
    {
        if ($agentId !== $this->agentId || !$this->isRunning) return;
        $this->currentAction = $current_action;
    }

    #[On('panel-run-finished')]
    public function onRunFinished(int $agentId): void
    {
        if ($agentId !== $this->agentId) return;
        $this->isRunning     = false;
        $this->currentAction = null;
    }

    public function render()
    {
        $agent   = $this->agentId ? Agent::find($this->agentId) : null;
        $dialogs = $agent
            ? Dialog::where('agent_id', $this->agentId)->orderBy('created_at')->get()
            : collect();

        return view('livewire.dialog-panel', ['agent' => $agent, 'dialogs' => $dialogs]);
    }
}
