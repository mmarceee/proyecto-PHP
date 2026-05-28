<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Collection;

class GestionUsuariosService
{
    public function listarUsuarios(User $usuarioActual): Collection
    {
        return User::with(['cliente', 'profesional', 'admin'])
            ->where('id', '!=', $usuarioActual->id)
            ->orderBy('id')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'apellido' => $user->apellido,
                    'email' => $user->email,
                    'telefono' => $user->telefono,
                    'estado_usuario' => $user->estado_usuario,

                    'es_cliente' => $user->cliente !== null,
                    'es_profesional' => $user->profesional !== null,
                    'es_admin' => $user->admin !== null,

                    'estado_profesional' => $user->profesional?->estado,
                ];
            });
    }

    public function bloquear(User $usuarioActual, User $user): User
    {
        $this->validarQueNoSeaElMismoUsuario($usuarioActual, $user);

        $user->update([
            'estado_usuario' => 'bloqueado',
        ]);

        return $user;
    }

    public function desbloquear(User $usuarioActual, User $user): User
    {
        $this->validarQueNoSeaElMismoUsuario($usuarioActual, $user);

        $user->update([
            'estado_usuario' => 'activo',
        ]);

        return $user;
    }

    public function hacerAdmin(User $usuarioActual, User $user): User
    {
        $this->validarQueNoSeaElMismoUsuario($usuarioActual, $user);

        if (!$user->admin()->exists()) {
            Admin::create([
                'user_id' => $user->id,
            ]);
        }

        $user->load(['cliente', 'profesional', 'admin']);

        return $user;
    }

    private function validarQueNoSeaElMismoUsuario(User $usuarioActual, User $user): void
    {
        if ($usuarioActual->id === $user->id) {
            throw new \Exception('No puedes realizar esta acción sobre tu propio usuario.');
        }
    }
}