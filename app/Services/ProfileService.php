<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ProfileService
{
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