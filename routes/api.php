


<?php
/*
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProfileApiController;

// Cambiamos sanctum por web para que comparta la sesión con Blade
Route::middleware('web')->group(function () {
    Route::put('/profile/info', [ProfileApiController::class, 'updateInfo']);
});
*/

Route::middleware('auth:sanctum')->group(function () {
    // ... tus otras rutas ...
    
    // Ruta para pedir el token de videollamada para una reserva específica
    Route::get('/reserva/{reserva}/videollamada/token', [App\Http\Controllers\VideoLlamadaController::class, 'obtenerToken']);
});