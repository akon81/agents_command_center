<?php

namespace App\Livewire;

use App\Models\Dialog;
use App\Models\Run;
use Livewire\Attributes\On;
use Livewire\Component;

class DashboardStats extends Component
{
    public array $stats = [];
    public array $chart = [];

    public function mount(): void
    {
        $this->refresh();
    }

    #[On('panel-run-finished')]
    #[On('panel-run-started')]
    public function refresh(): void
    {
        $total     = Run::count();
        $completed = Run::where('status', 'completed')->count();
        $failed    = Run::where('status', 'failed')->count();
        $finished  = $completed + $failed;

        $this->stats = [
            'total'        => $total,
            'today'        => Run::whereDate('created_at', today())->count(),
            'success_rate' => $finished > 0 ? round($completed / $finished * 100, 1) : null,
            'tokens'       => Dialog::where('role', 'assistant')->sum('tokens') ?? 0,
            'avg_ms'       => (int) (Run::whereNotNull('duration_ms')->avg('duration_ms') ?? 0),
            'running'      => Run::where('status', 'running')->count(),
        ];

        // Last 7 days sparkline
        $days = collect(range(6, 0))->map(fn($i) => now()->subDays($i)->toDateString());

        $counts = Run::selectRaw('DATE(created_at) as day, COUNT(*) as total, SUM(CASE WHEN status="completed" THEN 1 ELSE 0 END) as ok')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('day')
            ->pluck('total', 'day');

        $this->chart = $days->map(fn($d) => [
            'day'   => $d,
            'label' => now()->parse($d)->format('D'),
            'count' => (int) ($counts[$d] ?? 0),
        ])->values()->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard-stats');
    }
}
