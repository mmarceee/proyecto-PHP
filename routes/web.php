<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\Admin\ProfesionalAdminApiController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

//Ruta de postulacion en la carpeta 'pages'
Volt::route('postularse', 'pages.postularse-profesional')
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
        Route::get('/dashboard', [DashboardApiController::class, 'index'])
            ->name('api.dashboard');

            Route::patch('/profesionales/{profesional}/aprobar', [ProfesionalAdminApiController::class, 'aprobar'])
            ->name('api.profesionales.aprobar');

            Route::patch('/profesionales/{profesional}/rechazar', [ProfesionalAdminApiController::class, 'rechazar'])
            ->name('api.profesionales.rechazar');
    });



require __DIR__.'/auth.php';
