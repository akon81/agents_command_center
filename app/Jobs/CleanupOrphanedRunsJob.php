<?php
namespace App\Jobs;

use App\Models\Run;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Symfony\Component\Process\Process;

class CleanupOrphanedRunsJob implements ShouldQueue {
    use Queueable;

    public function handle(): void {
        Run::where('status', 'running')->whereNotNull('pid')->each(function (Run $run) {
            $check = new Process(['tasklist', '/FI', "PID eq {$run->pid}", '/NH', '/FO', 'CSV']);
            $check->run();
            $output = $check->getOutput();
            // If PID not found in tasklist output, mark as failed
            if (!str_contains($output, (string)$run->pid)) {
                $run->update(['status' => 'failed', 'finished_at' => now(), 'pid' => null]);
                $run->task->update(['status' => 'failed']);
            }
        });
    }
}
