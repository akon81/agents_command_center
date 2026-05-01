<?php

namespace App\Providers;

use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Window;
use Native\Laravel\Menu\MenuBuilder;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    public function boot(): void
    {
        $this->ensureDatabaseReady();
        $this->startReverb();

        Window::open()
            ->title('Agent Command Center')
            ->width(1400)
            ->height(900)
            ->minWidth(1200)
            ->minHeight(700)
            ->rememberState();

        $builder = app(MenuBuilder::class);

        MenuBar::create()
            ->icon(public_path('favicon.ico'))
            ->tooltip('Agent Command Center')
            ->withContextMenu(
                $builder->make(
                    $builder->label('Agent Command Center'),
                    $builder->separator(),
                    $builder->link(config('app.url'), 'Open Dashboard'),
                    $builder->separator(),
                    $builder->quit('Quit'),
                )
            );
    }

    public function phpIni(): array
    {
        return [
            'memory_limit' => '512M',
            // Suppress vendor deprecation warnings (symfony/http-foundation 7.4 etc.)
            'error_reporting' => E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED,
        ];
    }

    private function startReverb(): void
    {
        $log = storage_path('logs/reverb-native.log');

        // Already running check
        $socket = @fsockopen('127.0.0.1', 8080, $errno, $errstr, 0.5);
        if ($socket !== false) {
            fclose($socket);
            $this->reverbLog($log, 'Reverb already running on :8080 — skipping');
            return;
        }

        $php     = PHP_BINARY;
        $artisan = base_path('artisan');
        $artisanExists = file_exists($artisan) ? 'YES' : 'NO';
        $phpExists     = file_exists($php) ? 'YES' : 'NO';

        $this->reverbLog($log, "php={$php} [exists={$phpExists}]");
        $this->reverbLog($log, "artisan={$artisan} [exists={$artisanExists}]");
        $this->reverbLog($log, 'OS=' . PHP_OS_FAMILY . '  cwd=' . getcwd());

        if (!file_exists($artisan)) {
            $this->reverbLog($log, 'ERROR: artisan not found — cannot start Reverb');
            return;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            // start "" /B creates a fully independent Windows process — not killed when PHP GC runs
            // Empty title "" is required when the command path is quoted
            $cmd = "start \"\" /B \"{$php}\" \"{$artisan}\" reverb:start --host=127.0.0.1 --port=8080 > NUL 2>&1";
            $this->reverbLog($log, "CMD={$cmd}");
            pclose(popen($cmd, 'r'));
        } else {
            shell_exec("\"{$php}\" \"{$artisan}\" reverb:start --host=127.0.0.1 --port=8080 > /dev/null 2>&1 &");
        }

        // Poll until port is bound (max 5 s)
        for ($i = 0; $i < 25; $i++) {
            usleep(200_000);
            $s = @fsockopen('127.0.0.1', 8080, $e, $m, 0.1);
            if ($s !== false) {
                fclose($s);
                $this->reverbLog($log, 'Reverb bound on :8080 after ' . round(($i + 1) * 0.2, 1) . 's');
                return;
            }
        }

        $this->reverbLog($log, 'WARNING: Reverb did not bind within 5 s — live updates unavailable');
    }

    private function reverbLog(string $path, string $msg): void
    {
        file_put_contents($path, date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND);
    }

    private function ensureDatabaseReady(): void
    {
        $log = storage_path('logs/reverb-native.log');

        try {
            \Artisan::call('migrate', ['--force' => true]);
            $this->reverbLog($log, 'Migrate: ' . trim(\Artisan::output()));

            if (\App\Models\Agent::count() === 0) {
                \Artisan::call('db:seed', ['--class' => 'AgentSeeder', '--force' => true]);
                $this->reverbLog($log, 'Seed AgentSeeder: ' . trim(\Artisan::output()));
            } else {
                $this->reverbLog($log, 'Agents already seeded (' . \App\Models\Agent::count() . ')');
            }
        } catch (\Throwable $e) {
            $this->reverbLog($log, 'DB init error: ' . $e->getMessage());
        }
    }
}
