<?php

namespace App\Services;

use App\Models\User;

class ProfileService
{
    /**
     * Contiene la regla estricta de negocio para actualizar un perfil.
     */
    public function updateInformation(User $user, array $datosValidados): User
    {
        $user->fill($datosValidados);

        // Si el usuario cambia su email, le quitamos la verificación por seguridad
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }
}