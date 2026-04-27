<?php
namespace App\Jobs;

use App\Events\RunFinished;
use App\Events\RunStarted;
use App\Models\Run;
use App\Services\Logging\LineBuffer;
use App\Services\Logging\LogIngester;
use App\Services\ProcessLauncher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LaunchAgentJob implements ShouldQueue {
    use Queueable;

    public int $tries = 1;
    public int $timeout = 0;

    public function __construct(
        public readonly int $runId,
        public readonly string $workspacePath,
    ) {}

    public function handle(ProcessLauncher $launcher): void {
        $run = Run::with('agent')->findOrFail($this->runId);

        // PHASE 2: fake command — will be replaced with real claude CLI in Phase 4
        $command = ['ping', '-n', '12', '127.0.0.1'];

        $process = $launcher->build($command, $this->workspacePath);
        $process->start();

        $run->update([
            'status'     => 'running',
            'pid'        => $process->getPid(),
            'started_at' => now(),
        ]);
        $run->task->update(['status' => 'running', 'started_at' => now()]);

        broadcast(new RunStarted($run->fresh(['agent', 'task'])));

        $ingester  = new LogIngester($run);
        $stdoutBuf = new LineBuffer();
        $stderrBuf = new LineBuffer();

        // Emit fake JSON progress events every ~4 pings to simulate agent steps
        $pingCount = 0;
        $totalSteps = 6;

        while ($process->isRunning()) {
            $out = $process->getIncrementalOutput();
            $err = $process->getIncrementalErrorOutput();

            if ($out !== '') {
                $lines = $stdoutBuf->push($out);
                if (!empty($lines)) {
                    // Simulate progress JSON every 2 lines
                    $pingCount += count($lines);
                    $step = min((int)ceil($pingCount / 2), $totalSteps);
                    $fakeProgress = json_encode([
                        'type'  => 'progress',
                        'step'  => $step,
                        'total' => $totalSteps,
                        'label' => "Step {$step} of {$totalSteps}",
                    ]);
                    $ingester->ingest('stdout', array_merge($lines, [$fakeProgress]));
                }
            }

            if ($err !== '') {
                $lines = $stderrBuf->push($err);
                if (!empty($lines)) $ingester->ingest('stderr', $lines);
            }

            usleep(100_000); // 100ms tick
        }

        // Flush remaining
        $ingester->ingest('stdout', $stdoutBuf->flush());
        $ingester->ingest('stderr', $stderrBuf->flush());
        $ingester->flush();

        $exitCode  = $process->getExitCode();
        $startedAt = $run->fresh()->started_at;
        $durationMs = $startedAt ? (int)($startedAt->diffInMilliseconds(now())) : null;

        $finalStatus = ($exitCode === 0) ? 'completed' : 'failed';

        $run->update([
            'status'      => $finalStatus,
            'exit_code'   => $exitCode,
            'progress'    => $exitCode === 0 ? 100 : $run->progress,
            'finished_at' => now(),
            'duration_ms' => $durationMs,
            'pid'         => null,
        ]);
        $run->task->update(['status' => $finalStatus, 'finished_at' => now()]);

        broadcast(new RunFinished($run->fresh('agent')));
    }

    public function failed(\Throwable $e): void {
        $run = Run::find($this->runId);
        if ($run) {
            $run->update(['status' => 'failed', 'finished_at' => now()]);
            $run->task->update(['status' => 'failed']);
        }
    }
}
