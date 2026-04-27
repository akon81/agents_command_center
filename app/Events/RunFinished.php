<?php
namespace App\Events;

use App\Models\Run;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class RunFinished implements ShouldBroadcastNow {
    use Dispatchable, InteractsWithSockets;

    public function __construct(public readonly Run $run) {}

    public function broadcastOn(): array {
        return [
            new Channel('agent.' . $this->run->agent->slug),
            new Channel('dashboard'),
        ];
    }

    public function broadcastAs(): string {
        return 'RunFinished';
    }

    public function broadcastWith(): array {
        return [
            'run_id'      => $this->run->id,
            'agent_id'    => $this->run->agent_id,
            'agent_slug'  => $this->run->agent->slug,
            'status'      => $this->run->status,
            'duration_ms' => $this->run->duration_ms,
            'exit_code'   => $this->run->exit_code,
        ];
    }
}
