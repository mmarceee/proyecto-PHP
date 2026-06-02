<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificacionService{

    public function listarParaUsuario(User $user)
    {
        return Notificacion::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(20)
            ->get();
    }

    public function contarNoLeidasParaUsuario(User $user): int
    {
        return Notificacion::query()
            ->where('user_id', $user->id)
            ->where('leida', false)
            ->count();
    }

    public function marcarComoLeida(Notificacion $notificacion, User $user): Notificacion
    {
        if ($notificacion->user_id !== $user->id) {
            throw new \Exception('No hay permiso para modificar esta notificación.');
        }

        $notificacion->update([
            'leida' => true,
        ]);

        return $notificacion;
    }

    public function marcarTodasComoLeidas(User $user): int
    {
        return Notificacion::query()
            ->where('user_id', $user->id)
            ->where('leida', false)
            ->update([
                'leida' => true,
            ]);
    }
}