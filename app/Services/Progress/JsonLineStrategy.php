<?php
namespace App\Services\Progress;

class JsonLineStrategy implements ProgressStrategy {
    public function parse(string $line): ?ProgressSignal {
        $line = trim($line);
        if (!str_starts_with($line, '{')) return null;
        $data = json_decode($line, true);
        if (json_last_error() !== JSON_ERROR_NONE) return null;
        if (($data['type'] ?? '') !== 'progress') return null;
        if (!isset($data['step'], $data['total'])) return null;
        return new ProgressSignal((int)$data['step'], (int)$data['total'], $data['label'] ?? null);
    }
}
