<?php
namespace App\Services;

use Symfony\Component\Process\Process;

class ProcessKiller {
    public static function killTree(int $pid): void {
        $process = new Process(['taskkill', '/PID', (string)$pid, '/T', '/F']);
        $process->run();
    }
}
