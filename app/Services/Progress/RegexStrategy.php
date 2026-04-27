<?php
namespace App\Services\Progress;

class RegexStrategy implements ProgressStrategy {
    public function parse(string $line): ?ProgressSignal {
        // [3/8] pattern
        if (preg_match('/\[(\d+)\/(\d+)\]/', $line, $m)) {
            return new ProgressSignal((int)$m[1], (int)$m[2]);
        }
        // step N of M
        if (preg_match('/step\s+(\d+)\s+of\s+(\d+)/i', $line, $m)) {
            return new ProgressSignal((int)$m[1], (int)$m[2]);
        }
        // progress: N%
        if (preg_match('/progress[:\s]+(\d+)%/i', $line, $m)) {
            $pct = (int)$m[1];
            return new ProgressSignal($pct, 100);
        }
        return null;
    }
}
