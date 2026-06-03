<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('profesional.{id}', function ($user, $id) {
    // Verificamos que el ID de profesional del usuario logueado sea el mismo que el del canal
    return (int) $user->profesional?->id === (int) $id; 
});


Broadcast::channel('usuario.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});