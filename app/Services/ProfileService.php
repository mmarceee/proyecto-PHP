<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // <- ESTO ES VITAL PARA LA TRANSACCIÓN

class ProfileService
{
    public function updateInformation(User $user, array $datosValidados): User
    {
        $user->fill($datosValidados);

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

    public function deleteAccount(User $user): void
    {
        DB::transaction(function () use ($user) {
            
            // 1. SI ES CLIENTE, lo borramos lógicamente
            if ($user->esCliente()) { 
                $user->cliente->delete(); 
            }

            // 2. SI ES PROFESIONAL, lo borramos lógicamente
            if ($user->profesional()->exists()) { 
                $user->profesional->delete();
            }

            // 3. CAMBIAMOS EL ESTADO VISUAL
            $user->estado_usuario = 'eliminado';
            $user->save();

            // 4. ELIMINAMOS AL USUARIO
            $user->delete();
        });
    }
}