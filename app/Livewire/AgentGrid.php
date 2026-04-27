<?php

namespace App\Livewire;

use App\Models\Agent;
use Illuminate\Support\Collection;
use Livewire\Component;

class AgentGrid extends Component
{
    public Collection $agents;

    /** Layer display order */
    private const LAYER_ORDER = [
        'orchestration' => 0,
        'planning'      => 1,
        'execution'     => 2,
        'review'        => 3,
        'discovery'     => 4,
        'utility'       => 5,
    ];

    /** Icons per layer */
    public const LAYER_ICONS = [
        'orchestration' => '⚙',
        'planning'      => '🗺',
        'execution'     => '⚡',
        'review'        => '🔍',
        'discovery'     => '💡',
        'utility'       => '🔧',
    ];

    public function mount(): void
    {
        $order = self::LAYER_ORDER;

        $this->agents = Agent::all()
            ->sortBy(fn (Agent $agent) => $order[$agent->layer] ?? 99);
    }

    public function render()
    {
        $grouped = $this->agents->groupBy('layer');

        return view('livewire.agent-grid', [
            'grouped'    => $grouped,
            'layerOrder' => self::LAYER_ORDER,
            'layerIcons' => self::LAYER_ICONS,
        ]);
    }
}
