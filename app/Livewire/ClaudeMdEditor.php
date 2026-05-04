<?php

namespace App\Livewire;

use App\Models\Agent;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ClaudeMdEditor extends Component
{
    public bool $open = false;
    public ?int $agentId = null;
    public string $agentSlug = '';
    public string $filePath = '';
    public string $content = '';
    public bool $fileExists = false;
    public bool $dirty = false;
    public ?string $errorMsg = null;
    public ?string $savedMsg = null;

    #[On('open-claude-md')]
    public function openFor(int $agentId): void
    {
        $agent = Agent::findOrFail($agentId);

        $this->agentId   = $agentId;
        $this->agentSlug = $agent->slug;
        $this->filePath  = $agent->file_path ?? '';
        $this->errorMsg  = null;
        $this->savedMsg  = null;
        $this->dirty     = false;

        if (!$this->filePath) {
            $this->content    = '';
            $this->fileExists = false;
            $this->open       = true;
            return;
        }

        if (!file_exists($this->filePath)) {
            $this->content    = '';
            $this->fileExists = false;
        } else {
            $this->content    = file_get_contents($this->filePath);
            $this->fileExists = true;
        }

        $this->open = true;
    }

    public function updatedContent(): void
    {
        $this->dirty = true;
    }

    public function save(): void
    {
        if (!$this->filePath) {
            $this->errorMsg = 'No file path set. Configure it in Edit Agent first.';
            return;
        }

        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                $this->errorMsg = "Cannot create directory: {$dir}";
                return;
            }
        }

        if (file_put_contents($this->filePath, $this->content) === false) {
            $this->errorMsg = "Cannot write to: {$this->filePath}";
            return;
        }

        $this->fileExists = true;
        $this->dirty      = false;
        $this->errorMsg   = null;
        $this->savedMsg   = 'Saved ' . now()->format('H:i:s');
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function render()
    {
        return view('livewire.claude-md-editor');
    }
}
