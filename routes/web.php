<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProfesionalApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\Admin\ProfesionalAdminApiController;
use App\Http\Controllers\Api\AgendaApiController;

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
        Route::get('/dashboard', [DashboardApiController::class, 'index'])
            ->name('api.dashboard');

            Route::patch('/profesionales/{profesional}/aprobar', [ProfesionalAdminApiController::class, 'aprobar'])
            ->name('api.profesionales.aprobar');

            Route::patch('/profesionales/{profesional}/rechazar', [ProfesionalAdminApiController::class, 'rechazar'])
            ->name('api.profesionales.rechazar');

            Route::post('/profesionales/postularse', [ProfesionalApiController::class, 'postularse'])
            ->name('api.profesionales.postularse');

            Route::get('/profesional/agenda', [AgendaApiController::class, 'obtenerAgenda'])
            ->name('api.profesional.agenda');

            Route::post('/profesional/agenda/reglas', [AgendaApiController::class, 'guardarReglas'])
            ->name('api.profesional.agenda.reglas');

            Route::post('/profesional/agenda/excepciones', [AgendaApiController::class, 'guardarExcepcion'])
            ->name('api.profesional.agenda.excepciones');

            Route::delete('/profesional/agenda/excepciones', [AgendaApiController::class, 'desbloquearDia'])
            ->name('api.profesional.agenda.desbloquear');
    });



require __DIR__.'/auth.php';
