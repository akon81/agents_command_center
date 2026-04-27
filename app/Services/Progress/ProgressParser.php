<?php
namespace App\Services\Progress;

class ProgressParser {
    private array $strategies;

    public function __construct() {
        $this->strategies = [
            new JsonLineStrategy(),
            new RegexStrategy(),
        ];
    }

    public function parse(string $line): ?ProgressSignal {
        foreach ($this->strategies as $strategy) {
            $signal = $strategy->parse($line);
            if ($signal !== null) return $signal;
        }
        return null;
    }
}
