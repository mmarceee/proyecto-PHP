<?php

use Illuminate\Support\Facades\Route;
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

Route::view('admin/profesionales', 'livewire.admin.solicitudes-profesionales')
    ->middleware(['auth', 'verified', 'admin'])
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

Route::middleware(['auth'])->group(function () {
        Route::get('/reserva/{reserva}/sala', function (Reserva $reserva) {
            return view('videollamada', compact('reserva'));
        })->name('videollamada.sala');
    });

Route::view('admin/usuarios', 'livewire.admin.usuarios')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.usuarios');

Route::view('admin/categorias', 'livewire.admin.categorias')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.categorias');

require __DIR__.'/auth.php';
