<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardApiController;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardApiController::class, 'index'])
        ->name('api.dashboard');
});