<?php
namespace App\Services\Progress;

class ToolUseExtractor {
    public function parse(string $line): ?ActionSignal {
        $line = trim($line);
        if (!str_starts_with($line, '{')) return null;
        $data = json_decode($line, true);
        if (json_last_error() !== JSON_ERROR_NONE) return null;
        if (($data['type'] ?? '') !== 'assistant') return null;

        $content = $data['message']['content'] ?? [];
        foreach ($content as $block) {
            if (($block['type'] ?? '') !== 'tool_use') continue;
            $name  = $block['name'] ?? 'Tool';
            $input = $block['input'] ?? [];
            $arg   = $this->summarizeInput($input);
            $action = $arg !== '' ? "{$name}({$arg})" : $name;
            // Truncate to fit the DB column (255)
            if (mb_strlen($action) > 240) $action = mb_substr($action, 0, 240) . '…';
            return new ActionSignal($action);
        }

        return null;
    }

    private function summarizeInput(array $input): string {
        // Prefer common keys
        foreach (['file_path', 'path', 'pattern', 'command', 'url', 'query'] as $key) {
            if (isset($input[$key]) && is_string($input[$key])) {
                return $input[$key];
            }
        }
        // Fallback to short JSON
        $json = json_encode($input, JSON_UNESCAPED_SLASHES);
        return $json !== false && strlen($json) <= 100 ? $json : '';
    }
}
