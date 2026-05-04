<?php

namespace App\Livewire;

use App\Models\Run;
use Livewire\Attributes\On;
use Livewire\Component;

class RunningCounter extends Component
{
    public int $running = 0;

    public function mount(): void
    {
        $this->running = Run::where('status', 'running')->count();
    }

    #[On('panel-run-finished')]
    #[On('panel-run-started')]
    public function refresh(): void
    {
        $this->running = Run::where('status', 'running')->count();
    }

    public function render()
    {
        return view('livewire.running-counter');
    }
}
