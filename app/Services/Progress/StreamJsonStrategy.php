<?php
namespace App\Services\Progress;

class StreamJsonStrategy implements ProgressStrategy {
    public function parse(string $line): ?ProgressSignal {
        $line = trim($line);
        if (!str_starts_with($line, '{')) return null;
        $data = json_decode($line, true);
        if (json_last_error() !== JSON_ERROR_NONE) return null;

        // Direct progress event (forward compat)
        if (($data['type'] ?? '') === 'progress' && isset($data['step'], $data['total'])) {
            return new ProgressSignal((int)$data['step'], (int)$data['total'], $data['label'] ?? null);
        }

        // Assistant message — scan text content blocks for embedded progress JSON
        if (($data['type'] ?? '') === 'assistant') {
            $content = $data['message']['content'] ?? [];
            foreach ($content as $block) {
                if (($block['type'] ?? '') !== 'text') continue;
                $text = $block['text'] ?? '';
                // Try parsing the text as a progress JSON
                $textTrim = trim($text);
                if (str_starts_with($textTrim, '{')) {
                    $inner = json_decode($textTrim, true);
                    if (is_array($inner)
                        && ($inner['type'] ?? '') === 'progress'
                        && isset($inner['step'], $inner['total'])) {
                        return new ProgressSignal((int)$inner['step'], (int)$inner['total'], $inner['label'] ?? null);
                    }
                }
            }
        }

        return null;
    }
}
