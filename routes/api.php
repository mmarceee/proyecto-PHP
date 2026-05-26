<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgendaApiController;
use App\Http\Controllers\Api\BusquedaApiController;
use App\Http\Controllers\Api\CalificacionApiController;
use App\Http\Controllers\Api\ClienteApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\ProfesionalApiController;
use App\Http\Controllers\Api\Admin\ProfesionalAdminApiController;
use App\Http\Controllers\Api\ReservaApiController;
use App\Http\Controllers\Api\ServicioApiController;
use App\Http\Controllers\Api\VideoLlamadaApiController;
use App\Http\Controllers\Api\Admin\UsuarioAdminApiController;
use App\Http\Controllers\Api\MapaApiController;
use App\Http\Controllers\Api\Admin\CategoriaApiController;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardApiController::class, 'index'])
        ->name('api.dashboard');

    Route::put('/profile/info', [ProfileApiController::class, 'updateInfo'])
        ->name('api.profile.update');

    Route::get('/reserva/{reserva}/videollamada/token', [VideoLlamadaApiController::class, 'obtenerToken'])
        ->name('api.videollamada.token');

    Route::put('/profile/password', [ProfileApiController::class, 'updatePassword'])
        ->name('api.profile.password');
        
    Route::delete('/profile', [ProfileApiController::class, 'destroy'])
        ->name('api.profile.destroy');

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

    Route::get('/lugares-atencion', [MapaApiController::class, 'index'])
        ->name('api.mapa.lugares');

    Route::apiResource('/profesional/servicios', ServicioApiController::class)
        ->names('api.profesional.servicios');

    Route::post('/clientes/registro', [ClienteApiController::class, 'store'])
        ->name('api.clientes.registro');
        
    Route::get('/reservas/historial', [ReservaApiController::class, 'historial'])
    ->name('api.reservas.historial');
    
    Route::post('/reservas', [ReservaApiController::class, 'store'])
        ->name('api.reservas.store');

    Route::put('/reservas/{id}', [ReservaApiController::class, 'update'])
        ->name('api.reservas.update');

    Route::delete('/reservas/{id}', [ReservaApiController::class, 'destroy'])
        ->name('api.reservas.destroy');

    Route::post('/reservas/{id}/avanzar-estado', [ReservaApiController::class, 'avanzarEstado'])
        ->name('api.reservas.avanzar-estado');
        
    Route::post('/reservas/{id}/calificar', [CalificacionApiController::class, 'store'])
    ->name('api.reservas.calificar');
    
    Route::middleware(['admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/usuarios', [UsuarioAdminApiController::class, 'index'])
            ->name('api.admin.usuarios.index');

        Route::patch('/usuarios/{user}/bloquear', [UsuarioAdminApiController::class, 'bloquear'])
            ->name('api.admin.usuarios.bloquear');

        Route::patch('/usuarios/{user}/desbloquear', [UsuarioAdminApiController::class, 'desbloquear'])
            ->name('api.admin.usuarios.desbloquear');

        Route::patch('/usuarios/{user}/hacer-admin', [UsuarioAdminApiController::class, 'hacerAdmin'])
            ->name('api.admin.usuarios.hacer-admin');

        Route::patch('/profesionales/{profesional}/aprobar', [ProfesionalAdminApiController::class, 'aprobar'])
            ->name('api.admin.profesionales.aprobar');

        Route::patch('/profesionales/{profesional}/rechazar', [ProfesionalAdminApiController::class, 'rechazar'])
            ->name('api.admin.profesionales.rechazar');

        Route::get('/categorias', [CategoriaApiController::class, 'listarCategorias'])
            ->name('api.admin.categorias.listar');

        Route::post('/categorias', [CategoriaApiController::class, 'crearCategoria'])
            ->name('api.admin.categorias.crear');

        Route::put('/categorias/{id}', [CategoriaApiController::class, 'actualizarCategoria'])
            ->name('api.admin.categorias.actualizar');
            
        Route::patch('/categorias/{id}/desactivar', [CategoriaApiController::class, 'desactivarCategoria'])
            ->name('api.admin.categorias.desactivar');

        Route::patch('/categorias/{id}/activar', [CategoriaApiController::class, 'activarCategoria'])
            ->name('api.admin.categorias.activar');
    });

});