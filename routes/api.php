<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProfileApiController;

// Cambiamos sanctum por web para que comparta la sesión con Blade
Route::middleware('web')->group(function () {
    Route::put('/profile/info', [ProfileApiController::class, 'updateInfo']);
});