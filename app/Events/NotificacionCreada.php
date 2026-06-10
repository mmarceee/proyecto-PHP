<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use App\Models\Notificacion;

class NotificacionCreada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Notificacion $notificacion;

    public function __construct(Notificacion $notificacion)

    {
        $this->notificacion = $notificacion;
    }

    public function broadcastOn(): array //Emite al canal privado del usuario dueño de la notificación.
    {
        return [
            new PrivateChannel('usuario.' . $this->notificacion->user_id),
        ];
    }

    public function broadcastAs(): string //Define el nombre con el que JS va a escuchar
    {
        return 'notificacion.creada';
    }
}
