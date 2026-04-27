<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Http\Controllers\DashboardController::class);

Route::post('/runs/{run}/cancel', \App\Http\Controllers\RunController::class)->name('runs.cancel');
