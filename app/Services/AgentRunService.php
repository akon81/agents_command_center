<?php
namespace App\Services;

use App\Jobs\LaunchAgentJob;
use App\Models\Agent;
use App\Models\Run;
use App\Models\Task;

class AgentRunService {
    public function createAndDispatch(Agent $agent, string $prompt, string $workspacePath): Run {
        $task = Task::create([
            'agent_id'    => $agent->id,
            'title'       => str($prompt)->limit(80)->toString(),
            'prompt'      => $prompt,
            'status'      => 'pending',
        ]);

        $run = Run::create([
            'task_id'        => $task->id,
            'agent_id'       => $agent->id,
            'status'         => 'pending',
            'workspace_path' => $workspacePath,
        ]);

        LaunchAgentJob::dispatch($run->id, $workspacePath);

        return $run;
    }

    public function cancel(Run $run): void {
        if ($run->pid) {
            ProcessKiller::killTree($run->pid);
        }
        $run->update(['status' => 'cancelled', 'finished_at' => now()]);
        $run->task->update(['status' => 'cancelled']);
    }
}
