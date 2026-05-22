<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProfesionalApiController;
use App\Http\Controllers\Api\Admin\ProfesionalAdminApiController;
use App\Models\Reserva;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

//Ruta de postulacion en la carpeta 'pages'
Route::view('postularse', 'livewire.pages.postularse-profesional')
    ->middleware(['auth', 'verified'])
    ->name('profesional.postularse');

Route::view('admin/solicitudes', 'livewire.admin.solicitudes-profesionales')
    ->middleware(['auth'])
    ->name('admin.solicitudes');

Route::view('admin/profesionales', 'livewire.admin.solicitudes-profesionales')
    ->middleware(['auth'])
    ->name('admin.profesionales');

Route::view('/prototipo/busqueda', 'prototipos.busqueda')
    ->middleware(['auth'])
    ->name('prototipo.busqueda');

Route::view('/prototipo/agenda', 'prototipos.agenda')
    ->middleware(['auth'])
    ->name('prototipo.agenda');

Route::view('/prototipo/perfil', 'prototipos.perfil')
    ->middleware(['auth'])
    ->name('prototipo.perfil');

Route::middleware(['auth', 'verified'])
    ->prefix('api')
    ->group(function () {

            Route::patch('/profesionales/{profesional}/aprobar', [ProfesionalAdminApiController::class, 'aprobar'])
            ->name('api.profesionales.aprobar');

            Route::patch('/profesionales/{profesional}/rechazar', [ProfesionalAdminApiController::class, 'rechazar'])
            ->name('api.profesionales.rechazar');

            Route::post('/profesionales/postularse', [ProfesionalApiController::class, 'postularse'])
            ->name('api.profesionales.postularse');

            Route::put('/profile/info', [\App\Http\Controllers\Api\ProfileApiController::class, 'updateInfo'])->name('api.profile.update');

            Route::put('/profile/password', [\App\Http\Controllers\Api\ProfileApiController::class, 'updatePassword'])->name('api.profile.password');
            
            Route::delete('/profile', [\App\Http\Controllers\Api\ProfileApiController::class, 'destroy'])->name('api.profile.destroy');
    });

    Route::middleware(['auth'])->group(function () {
        Route::get('/reserva/{reserva}/sala', function (Reserva $reserva) {
            return view('videollamada', compact('reserva'));
        })->name('videollamada.sala');
    });

require __DIR__.'/auth.php';
