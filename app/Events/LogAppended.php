<?php
namespace App\Events;

use App\Models\Run;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class LogAppended implements ShouldBroadcastNow {
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public readonly Run $run,
        public readonly array $lines,
    ) {}

    public function broadcastOn(): array {
        return [new Channel('runs.' . $this->run->id)];
    }

    public function broadcastAs(): string {
        return 'LogAppended';
    }

    public function broadcastWith(): array {
        return [
            'run_id' => $this->run->id,
            'lines'  => $this->lines,
        ];
    }
}
