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

Volt::route('admin/solicitudes', 'admin.solicitudes-profesionales')
    ->middleware(['auth'])
    ->name('admin.solicitudes');

Volt::route('admin/profesionales', 'admin.solicitudes-profesionales')
    ->middleware(['auth'])
    ->name('admin.profesionales');

Route::post('/admin/dashboard/profesionales/{id}/aceptar', function ($id) {
    $profesional = Profesional::findOrFail($id);
    $profesional->update(['estado' => 'aprobado']);
    return back()->with('success', 'Profesional aprobado con éxito.');
})->middleware(['auth'])->name('admin.dashboard.aceptar');

Route::post('/admin/dashboard/profesionales/{id}/rechazar', function ($id) {
    $profesional = Profesional::findOrFail($id);
    $profesional->delete(); // Se elimina la solicitud para permitirle re-postularse
    return back()->with('error', 'La postulación fue rechazada.');
})->middleware(['auth'])->name('admin.dashboard.rechazar');

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
