<?php
namespace App\Events;

use App\Models\Run;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class RunStarted implements ShouldBroadcastNow {
    use Dispatchable, InteractsWithSockets;

    public function __construct(public readonly Run $run) {}

    public function broadcastOn(): array {
        return [
            new Channel('agent.' . $this->run->agent->slug),
            new Channel('dashboard'),
        ];
    }

    public function broadcastAs(): string {
        return 'RunStarted';
    }

    public function broadcastWith(): array {
        return [
            'run_id'      => $this->run->id,
            'agent_slug'  => $this->run->agent->slug,
            'agent_id'    => $this->run->agent_id,
            'task_title'  => $this->run->task->title,
            'started_at'  => $this->run->started_at?->toIso8601String(),
        ];
    }
}
