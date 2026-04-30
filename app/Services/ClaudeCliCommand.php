<?php
namespace App\Services;

class ClaudeCliCommand {
    public const AGENTS_WORKSPACE = 'C:\\Herd\\claude\\automatyzacja';

    public function build(string $agentSlug, string $userWorkspace): array {
        return [
            'claude',
            '-p',
            '--agent', $agentSlug,
            '--output-format', 'stream-json',
            '--verbose',
            '--include-partial-messages',
            '--dangerously-skip-permissions',
            '--add-dir', $userWorkspace,
        ];
    }

    public function workingDirectory(): string {
        return self::AGENTS_WORKSPACE;
    }
}
