<?php
use Illuminate\Support\Facades\Broadcast;

// Dashboard channel — all authenticated (single-user desktop, allow all)
Broadcast::channel('dashboard', fn() => true);

// Per-agent channel
Broadcast::channel('agent.{slug}', fn($user, $slug) => true);

// Per-run log stream
Broadcast::channel('runs.{runId}', fn($user, $runId) => true);
