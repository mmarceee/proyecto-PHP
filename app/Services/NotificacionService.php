<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\Reserva;
use App\Models\User;
use App\Events\NotificacionCreada;
use App\Models\CompraPaquete;

class NotificacionService
{

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

    public function notificarNuevaReserva(Reserva $reserva): Notificacion
    {
        $reserva->loadMissing(['profesional.user', 'cliente.user', 'servicio']); //Se le dice a Laravel que si todavía no cargo profesional, cliente o servicio, cargalos ahora.

        return $this->crearYEmitir([
            'user_id' => $reserva->profesional->user_id,
            'reserva_id' => $reserva->id,
            'titulo' => 'Nueva reserva',
            'mensaje' => 'Hay una nueva reserva pendiente.',
            'tipo_not' => 'confirmacion_reserva',
            'canal_not' => 'sistema',
            'estado_not' => 'pendiente',
            'leida' => false,
            'fechaCreacion' => now()->toDateString(),
        ]);
    }

    public function notificarReservaConfirmada(Reserva $reserva): Notificacion
    {
        $reserva->loadMissing(['cliente.user', 'profesional.user', 'servicio']);

        return $this->crearYEmitir([
            'user_id' => $reserva->cliente->user_id,
            'reserva_id' => $reserva->id,
            'titulo' => 'Reserva confirmada',
            'mensaje' => 'Tu reserva fue confirmada por el profesional.',
            'tipo_not' => 'confirmacion_reserva',
            'canal_not' => 'sistema',
            'estado_not' => 'pendiente',
            'leida' => false,
            'fechaCreacion' => now()->toDateString(),
        ]);
    }

    public function notificarReservaCancelada(Reserva $reserva): void
    {
        $reserva->loadMissing(['cliente.user', 'profesional.user', 'servicio']);

        foreach ([
            $reserva->cliente->user_id,
            $reserva->profesional->user_id,
        ] as $userId) {
            $this->crearYEmitir([
                'user_id' => $userId,
                'reserva_id' => $reserva->id,
                'titulo' => 'Reserva cancelada',
                'mensaje' => 'La reserva fue cancelada.',
                'tipo_not' => 'cancelacion',
                'canal_not' => 'sistema',
                'estado_not' => 'pendiente',
                'leida' => false,
                'fechaCreacion' => now()->toDateString(),
            ]);
        }
    }

    public function notificarReservaEnCurso(Reserva $reserva): Notificacion
    {
        $reserva->loadMissing(['cliente.user', 'profesional.user', 'servicio']);

        return $this->crearYEmitir([
            'user_id' => $reserva->cliente->user_id,
            'reserva_id' => $reserva->id,
            'titulo' => 'Reserva en curso',
            'mensaje' => 'Tu reserva está en curso.',
            'tipo_not' => 'mensaje_relevante',
            'canal_not' => 'sistema',
            'estado_not' => 'pendiente',
            'leida' => false,
            'fechaCreacion' => now()->toDateString(),
        ]);
    }

    public function notificarReservaFinalizada(Reserva $reserva): Notificacion
    {
        $reserva->loadMissing(['cliente.user', 'profesional.user', 'servicio']);

        return $this->crearYEmitir([
            'user_id' => $reserva->cliente->user_id,
            'reserva_id' => $reserva->id,
            'titulo' => 'Sesión finalizada',
            'mensaje' => 'Tu sesión ha finalizado.',
            'tipo_not' => 'mensaje_relevante',
            'canal_not' => 'sistema',
            'estado_not' => 'pendiente',
            'leida' => false,
            'fechaCreacion' => now()->toDateString(),
        ]);
    }
    
    public function notificarCompraPaquete(CompraPaquete $compra): Notificacion
    {
        $compra->loadMissing([
            'cliente.user',
            'paqueteServicio.profesional.user',
            'paqueteServicio.servicio',
        ]);

        return $this->crearYEmitir([
            'user_id' => $compra->paqueteServicio->profesional->user_id,
            'reserva_id' => null, //Esta notificación no está asociada a una reserva específica, por eso se deja null
            'compra_paquete_id' => $compra->id, //Para que quede asociado a la compra real
            'titulo' => 'Nuevo paquete vendido',
            'mensaje' => 'Un cliente compró el paquete ' . $compra->paqueteServicio->nombre . '.',
            'tipo_not' => 'pago_aprobado', // La compra se registra cuando el pago ya fue aprobado.
            'canal_not' => 'sistema',
            'estado_not' => 'pendiente',
            'leida' => false,
            'fechaCreacion' => now()->toDateString(),
        ]);
    }
    
    private function crearYEmitir(array $datos): Notificacion
    {
        $notificacion = Notificacion::create($datos);

        broadcast(new NotificacionCreada($notificacion));

        return $notificacion;
    }

}