<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ProfileService
{
    /**
     * Contiene la regla estricta de negocio para actualizar un perfil.
     */
    public function updateInformation(User $user, array $datosValidados): User
    {
        return DB::transaction(function () use ($user, $datosValidados) {

            $user->name = $datosValidados['name'];
            $user->apellido = $datosValidados['apellido'] ?? null;
            $user->telefono = $datosValidados['telefono'] ?? null;

            $user->save();

            if ($user->esProfesionalAprobado()) {
                $profesional = $user->profesional;

                if ($profesional) {
                    $profesional->descripcion = $datosValidados['descripcion'] ?? null;
                    $profesional->nombre_comercial = $datosValidados['nombre_comercial'] ?? null;

                    $profesional->save();
                }
            }

            return $user->fresh(['profesional']);
        });
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