<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\VideoLlamadaApiController;


Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardApiController::class, 'index'])
        ->name('api.dashboard');

    Route::put('/profile/info', [ProfileApiController::class, 'updateInfo'])
        ->name('api.profile.update');

    Route::get('/reserva/{reserva}/videollamada/token', [VideoLlamadaApiController::class, 'obtenerToken'])
        ->name('api.videollamada.token');

    Route::put('/profile/password', [\App\Http\Controllers\Api\ProfileApiController::class, 'updatePassword'])
        ->name('api.profile.password');
        
    Route::delete('/profile', [\App\Http\Controllers\Api\ProfileApiController::class, 'destroy'])
        ->name('api.profile.destroy');
});