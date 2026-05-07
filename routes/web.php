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

require __DIR__.'/auth.php';
