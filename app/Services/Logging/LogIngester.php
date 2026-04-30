<?php
namespace App\Services\Logging;

use App\Events\ActionChanged;
use App\Events\LogAppended;
use App\Events\TaskProgressed;
use App\Models\Log;
use App\Models\Run;
use App\Services\Progress\ProgressParser;
use App\Services\Progress\ToolUseExtractor;

class LogIngester {
    private ProgressParser $progressParser;
    private ToolUseExtractor $toolUseExtractor;
    private array $buffer = [];
    private int $seq;
    private int $FLUSH_SIZE = 50;
    private ?int $lastBroadcastedProgress = null;
    private ?string $lastAction = null;

    public function __construct(private Run $run) {
        $this->progressParser = new ProgressParser();
        $this->toolUseExtractor = new ToolUseExtractor();
        $this->seq = Log::where('run_id', $run->id)->max('seq') ?? 0;
    }

    public function ingest(string $stream, array $lines): void {
        $now = now()->toDateTimeString();
        foreach ($lines as $line) {
            $this->seq++;
            $this->buffer[] = [
                'run_id'      => $this->run->id,
                'stream'      => $stream,
                'content'     => $line,
                'seq'         => $this->seq,
                'occurred_at' => $now,
                'created_at'  => $now,
            ];

            // Parse progress
            $signal = $this->progressParser->parse($line);
            if ($signal !== null) {
                $percent = $signal->toPercent();
                $this->run->update(['progress' => $percent]);
                if ($percent !== $this->lastBroadcastedProgress) {
                    try { broadcast(new TaskProgressed($this->run, $percent, $signal->label ?? null)); } catch (\Throwable) {}
                    $this->lastBroadcastedProgress = $percent;
                }
            }

            // Try tool_use extraction first (stream-json), then fall back to bullet regex
            $actionSignal = $this->toolUseExtractor->parse($line);
            $action = null;
            if ($actionSignal !== null) {
                $action = $actionSignal->action;
            } elseif (preg_match('/^[●•]\s+(\w+)\((.{0,100})\)/', $line, $m)) {
                $action = $m[1] . '(' . $m[2] . ')';
            }

            if ($action !== null && $action !== $this->lastAction) {
                $this->run->update(['current_action' => $action]);
                try { broadcast(new ActionChanged($this->run, $action)); } catch (\Throwable) {}
                $this->lastAction = $action;
            }

            if (count($this->buffer) >= $this->FLUSH_SIZE) {
                $this->flush();
            }
        }
    }

    public function flush(): void {
        if (empty($this->buffer)) return;
        $lines = array_column($this->buffer, 'content');
        Log::insert($this->buffer);
        $this->buffer = [];
        try { broadcast(new LogAppended($this->run, $lines)); } catch (\Throwable) {}
    }
}
