<?php

use Illuminate\Support\Facades\Route;
use App\Models\Reserva;
use Livewire\Volt\Volt;

Route::get('/health', function () {
    return response()->json([
        'ok' => true,
    ]);
})->name('health');

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'perfil.completo'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth',])
    ->name('profile');

//Ruta de postulacion en la carpeta 'pages'
Route::view('postularse', 'livewire.pages.postularse-profesional')
    ->middleware(['auth', 'perfil.completo'])
    ->name('profesional.postularse');

Route::view('admin/profesionales', 'livewire.admin.solicitudes-profesionales')
    ->middleware(['auth', 'admin', 'perfil.completo'])
    ->name('admin.profesionales');

Route::view('/prototipo/busqueda', 'prototipos.busqueda')
    ->middleware(['auth', 'perfil.completo'])
    ->name('prototipo.busqueda');

Route::view('/prototipo/agenda', 'prototipos.agenda')
    ->middleware(['auth', 'perfil.completo'])
    ->name('prototipo.agenda');

Route::view('/prototipo/perfil', 'prototipos.perfil')
    ->middleware(['auth', 'perfil.completo'])
    ->name('prototipo.perfil');

Route::get('/profesional/servicios', function () {
    return view('profesional.servicios');
    })->middleware(['auth', 'perfil.completo'])->name('profesional.servicios');

Route::middleware(['auth'])->group(function () {
    Route::get('/reserva/{reserva}/sala', function (Reserva $reserva, \App\Services\VideoLlamadaService $videoService) {
        
        // VALIDACIÓN DE SEGURIDAD (IDOR)
        if (!$videoService->validarAcceso(auth()->user(), $reserva)) {
            abort(403, 'No tienes autorización para ingresar a esta sala.');
        }
        return view('videollamada', compact('reserva'));
    })->name('videollamada.sala');
});

Route::view('admin/usuarios', 'livewire.admin.usuarios')
    ->middleware(['auth', 'admin', 'perfil.completo'])
    ->name('admin.usuarios');

Route::view('admin/categorias', 'livewire.admin.categorias')
    ->middleware(['auth', 'admin', 'perfil.completo'])
    ->name('admin.categorias');

Route::view('admin/reseñas', 'livewire.admin.reseñas')
    ->middleware(['auth', 'admin', 'perfil.completo'])
    ->name('admin.reseñas');
    
Route::get('/reservas/historial', function () {
        return view('historial'); // Llama al archivo historial.blade.php
    })->name('reservas.historial');

Route::get('/profesional/paquetes', function () {
        return view('profesional.paquetes');
    })->name('profesional.paquetes');

Route::get('/profesional/paquetes-vendidos', function () {
        return view('profesional.paquetes-vendidos');
    })->name('profesional.paquetes.vendidos');    

Route::get('/cliente/mis-paquetes', function () {
        return view('misPaquetes'); 
    })->name('cliente.paquetes.mios');

Route::get('/cliente/paquetes/explorar', function () {
        return view('explorarPaquetes'); 
    })->name('cliente.paquetes.explorar');

Route::get('/profesional/calendario-consultas', function () {
        return view('profesional.calendario');})
        ->middleware(['auth', 'perfil.completo'])
        ->name('profesional.calendario');

Volt::route('completar-perfil', 'pages.completar-perfil')
    ->middleware(['auth'])
    ->name('perfil.completar');



require __DIR__.'/auth.php';
