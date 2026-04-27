<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Services\AgentRunService;
use Illuminate\Console\Command;

class TestAgentRun extends Command
{
    protected $signature = 'agent:test
                            {slug? : Agent slug (default: coder)}
                            {--prompt= : Custom prompt text}';

    protected $description = 'Dispatch a test run for an agent (uses fake ping process)';

    public function handle(AgentRunService $service): int
    {
        $slug = $this->argument('slug') ?? 'coder';
        $agent = Agent::where('slug', $slug)->first();

        if (!$agent) {
            $this->error("Agent '{$slug}' not found.");
            $this->line('Available agents:');
            Agent::pluck('slug')->each(fn($s) => $this->line("  - {$s}"));
            return self::FAILURE;
        }

        $prompt = $this->option('prompt') ?? "Test run for {$slug} at " . now()->format('H:i:s');
        $workspace = base_path();

        $this->info("Dispatching run for agent: {$agent->slug}");
        $run = $service->createAndDispatch($agent, $prompt, $workspace);

        $this->newLine();
        $this->line("  Run ID:    <fg=cyan>{$run->id}</>");
        $this->line("  Task ID:   <fg=cyan>{$run->task_id}</>");
        $this->line("  Workspace: {$workspace}");
        $this->newLine();
        $this->info('Job queued. Make sure `php artisan queue:work` is running.');
        $this->comment('Open the dashboard to watch live updates.');

        return self::SUCCESS;
    }
}
