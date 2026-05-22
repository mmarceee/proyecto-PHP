<?php

namespace App\Services;

use App\Models\User;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClienteService
{
    /**
     * Registra un nuevo usuario y su perfil de cliente de forma atómica
     */
    public function registrarCliente(array $datos)
    {
        return DB::transaction(function () use ($datos) {
            $user = User::create([
                'name'           => $datos['name'],
                'apellido'       => $datos['apellido'],
                'email'          => $datos['email'],
                'password'       => Hash::make($datos['password']),
                'telefono'       => $datos['telefono'],
                'estado_usuario' => 'activo',
            ]);

            Cliente::create([
                'user_id' => $user->id,
            ]);

            return $user;
        });
    }
}