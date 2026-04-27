<?php
namespace App\Services\Logging;

class LineBuffer {
    private string $buffer = '';

    /** @return string[] complete lines (without trailing \n) */
    public function push(string $chunk): array {
        $this->buffer .= $chunk;
        $lines = explode("\n", $this->buffer);
        $this->buffer = array_pop($lines); // last element may be incomplete
        return array_filter($lines, fn($l) => $l !== '');
    }

    public function flush(): array {
        $remaining = $this->buffer;
        $this->buffer = '';
        return $remaining !== '' ? [$remaining] : [];
    }
}
