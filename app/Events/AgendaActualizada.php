<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// 'ShouldBroadcast' le dice a Laravel que este evento debe viajar por WebSockets (Reverb)
class AgendaActualizada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Estas variables públicas se convierten automáticamente en el JSON que recibirá el Frontend
    public $profesionalId;
    public $bloquesOcupados;

    /**
     * El constructor recibe los datos desde el Service.
     */
    public function __construct(int $profesionalId, array $bloquesOcupados)
    {
        $this->profesionalId = $profesionalId;
        $this->bloquesOcupados = $bloquesOcupados;
    }

    /**
     * Definimos el canal de comunicación. 
     * Como es información de agenda, usaremos un canal privado por seguridad.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('profesional.' . $this->profesionalId),
        ];
    }

    /**
     * El nombre con el que Alpine/JS va a identificar este evento en el frontend.
     */
    public function broadcastAs(): string
    {
        return 'agenda.modificada';
    }
}