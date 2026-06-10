<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EstadoReservaCambiado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $clienteId;
    public $reservaId;
    public $nuevoEstado;

    /**
     * @param int $clienteId El ID del usuario (cliente) que reservó
     * @param int $reservaId El ID de la reserva que cambió
     * @param string $nuevoEstado (ej: 'confirmada', 'cancelada')
     */
    public function __construct(int $clienteId, int $reservaId, string $nuevoEstado)
    {
        $this->clienteId = $clienteId;
        $this->reservaId = $reservaId;
        $this->nuevoEstado = $nuevoEstado;
    }

    public function broadcastOn(): array
    {
        // Emitimos a la radio privada del cliente
        return [
            new PrivateChannel('usuario.' . $this->clienteId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'reserva.estado.cambiado';
    }
}