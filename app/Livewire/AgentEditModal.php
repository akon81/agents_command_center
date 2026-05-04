<?php

namespace App\Livewire;

use App\Models\Agent;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AgentEditModal extends Component
{
    public bool $open = false;
    public ?int $agentId = null;

    #[Validate('required|max:100')]
    public string $name = '';

    #[Validate('nullable|max:500')]
    public string $description = '';

    #[Validate('nullable|max:100')]
    public string $model = '';

    #[Validate('required|in:orchestration,planning,execution,review,discovery,utility')]
    public string $layer = 'utility';

    #[Validate('required|regex:/^#[0-9a-fA-F]{6}$/')]
    public string $color = '#5e6ad2';

    #[Validate('boolean')]
    public bool $is_active = true;

    #[Validate('nullable|max:500')]
    public string $file_path = '';

    #[On('open-edit')]
    public function openFor(int $agentId): void
    {
        $agent = Agent::findOrFail($agentId);

        $this->agentId     = $agentId;
        $this->name        = $agent->name;
        $this->description = $agent->description ?? '';
        $this->model       = $agent->model ?? '';
        $this->layer       = $agent->layer;
        $this->color       = $agent->color;
        $this->is_active   = $agent->is_active;
        $this->file_path   = $agent->file_path ?? '';

        $this->resetErrorBag();
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function save(): void
    {
        $this->validate();

        Agent::findOrFail($this->agentId)->update([
            'name'        => $this->name,
            'description' => $this->description ?: null,
            'model'       => $this->model ?: null,
            'layer'       => $this->layer,
            'color'       => $this->color,
            'is_active'   => $this->is_active,
            'file_path'   => $this->file_path ?: null,
        ]);

        $this->open = false;
        $this->dispatch('agent-updated', agentId: $this->agentId);
    }

    public function render()
    {
        $agent = $this->agentId ? Agent::find($this->agentId) : null;
        return view('livewire.agent-edit-modal', ['agent' => $agent]);
    }
}
