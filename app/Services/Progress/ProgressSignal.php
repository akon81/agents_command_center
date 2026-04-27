<?php
namespace App\Services\Progress;

readonly class ProgressSignal {
    public function __construct(
        public int $step,
        public int $total,
        public ?string $label = null,
    ) {}

    public function toPercent(): int {
        if ($this->total === 0) return 0;
        return (int) round(($this->step / $this->total) * 100);
    }
}
