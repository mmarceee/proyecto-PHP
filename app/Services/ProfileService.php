<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
    public function updatePassword($user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
    public function deleteAccount($user): void
    {
        // NOTA PARA TUS COMPAÑEROS: 
        // Aquí adentro en el futuro controlaremos si es profesional o cliente
        // para cancelar sus reservas antes de borrarlo.
        
        $user->delete();
    }
}