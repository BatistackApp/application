<?php

use App\Http\Controllers\MonitoringController;
use Illuminate\Support\Facades\Route;

Route::get('/monitoring/queues', [MonitoringController::class, 'getQueueStats'])->name('api.monitoring.queues');
