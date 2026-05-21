<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProfesionalApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\Admin\ProfesionalAdminApiController;
use App\Http\Controllers\Api\AgendaApiController;
use App\Http\Controllers\Api\BusquedaApiController;
use App\Http\Controllers\Api\ServicioApiController;

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

Route::get('/profesional/servicios', function () {
    return view('profesional.servicios');
})->middleware(['auth'])->name('profesional.servicios');

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

            Route::post('/paciente/agenda/reservar', [AgendaApiController::class, 'agendarTurno'])
            ->name('api.paciente.agenda.reservar');

            Route::get('/servicios/buscar', [BusquedaApiController::class, 'buscar'])
            ->name('api.servicios.buscar');

            Route::get('/servicios/profesionales/{id}/agenda', [BusquedaApiController::class, 'obtenerAgendaProfesional'])
            ->name('api.servicios.agenda');

            Route::apiResource('/profesional/servicios', ServicioApiController::class)
            ->names('api.profesional.servicios');

            Route::post('/clientes/registro', [\App\Http\Controllers\Api\ClienteApiController::class, 'store'])
            ->name('api.clientes.registro');

            Route::post('/reservas', [\App\Http\Controllers\Api\ReservaApiController::class, 'store'])
            ->name('api.reservas.store');

            Route::put('/reservas/{id}', [\App\Http\Controllers\Api\ReservaApiController::class, 'update'])
            ->name('api.reservas.update');

            Route::delete('/reservas/{id}', [\App\Http\Controllers\Api\ReservaApiController::class, 'destroy'])
            ->name('api.reservas.destroy');

            Route::post('/reservas/{id}/avanzar-estado', [\App\Http\Controllers\Api\ReservaApiController::class, 'avanzarEstado'])
            ->name('api.reservas.avanzar-estado');
    });



require __DIR__.'/auth.php';
