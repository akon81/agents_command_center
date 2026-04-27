<?php
namespace App\Events;

use App\Models\Run;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class ActionChanged implements ShouldBroadcastNow {
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public readonly Run $run,
        public readonly string $action,
    ) {}

    public function broadcastOn(): array {
        return [new Channel('agent.' . $this->run->agent->slug)];
    }

    public function broadcastAs(): string {
        return 'ActionChanged';
    }

    public function broadcastWith(): array {
        return [
            'run_id'         => $this->run->id,
            'agent_id'       => $this->run->agent_id,
            'current_action' => $this->action,
        ];
    }
}
