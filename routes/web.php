<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.core.pages.dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::prefix('api')->group(function () {
    Route::get('/monitoring/queues', [\App\Http\Controllers\MonitoringController::class, 'getQueueStats'])->name('api.monitoring.queues');
});

require __DIR__.'/settings.php';
//require __DIR__.'/test.php';
