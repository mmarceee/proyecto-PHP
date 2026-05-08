<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('/prototipo/busqueda', 'prototipos.busqueda')->middleware(['auth'])->name('prototipo.busqueda');
Route::view('/prototipo/agenda', 'prototipos.agenda')->middleware(['auth'])->name('prototipo.agenda');
Route::view('/prototipo/perfil-profesional', 'prototipos.perfil-profesional')->middleware(['auth'])->name('prototipo.perfil-profesional');
Route::view('/prototipo/perfil', 'prototipos.perfil')->middleware(['auth'])->name('prototipo.perfil');

require __DIR__.'/auth.php';
