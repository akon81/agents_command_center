<?php
namespace App\Services\Progress;

interface ProgressStrategy {
    public function parse(string $line): ?ProgressSignal;
}
