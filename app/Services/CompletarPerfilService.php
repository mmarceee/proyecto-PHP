<?php

namespace App\Services;

use App\Models\Profesional;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CompletarPerfilService
{
    public function completar(User $user, array $datos): User
    {
        return DB::transaction(function () use ($user, $datos) {
            $user->update([
                'name' => $datos['name'],
                'apellido' => $datos['apellido'],
                'telefono' => $datos['telefono'],
            ]);

            $user->cliente()->firstOrCreate([]);

            if ($datos['tipo_registro'] === 'profesional') {
                $user->profesional()->firstOrCreate([], [
                    'descripcion' => $datos['descripcion'],
                    'especialidad' => $datos['especialidad'],
                    'reputacion_promedio' => 0,
                    'nombre_comercial' => $datos['nombre_comercial'] ?? null,
                    'estado' => Profesional::ESTADO_PENDIENTE,
                ]);
            }

            return $user->fresh(['cliente', 'profesional']);
        });
    }
}