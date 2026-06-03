<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
// 🛠️ LA LÍNEA CLAVE: Le dice a PHP exactamente de dónde importar la interfaz
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; 
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgendaActualizada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $profesionalId;
    public $bloquesOcupados;

    /**
     * Constructor del Evento
     */
    public function __construct(int $profesionalId, array $bloquesOcupados)
    {
        $this->profesionalId = $profesionalId;
        $this->bloquesOcupados = $bloquesOcupados;
    }

    /**
     * Canal privado seguro
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('profesional.' . $this->profesionalId),
        ];
    }

    /**
     * Alias para el Frontend
     */
    public function broadcastAs(): string
    {
        return 'agenda.modificada';
    }
}