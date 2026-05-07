<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/prototipo/busqueda', function () {
    return view('prototipos.busqueda');
})->middleware(['auth'])->name('prototipo.busqueda');

Route::get('/prototipo/agenda', function () {
    return view('prototipos.agenda');
})->middleware(['auth'])->name('prototipo.agenda');

Route::get('/prototipo/perfil-profesional', function () {
    return view('prototipos.perfil-profesional');
})->middleware(['auth'])->name('prototipo.perfil-profesional');
require __DIR__.'/auth.php';
