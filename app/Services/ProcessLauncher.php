<?php
namespace App\Services;

use Symfony\Component\Process\Process;

class ProcessLauncher {
    public function build(array $command, string $workspacePath): Process {
        $process = new Process($command, $workspacePath);
        $process->setTimeout(0);        // no timeout — long-running
        $process->setIdleTimeout(null);
        $process->setEnv([
            'PYTHONIOENCODING' => 'utf-8',
            'TERM'             => 'dumb',
        ]);
        return $process;
    }
}
