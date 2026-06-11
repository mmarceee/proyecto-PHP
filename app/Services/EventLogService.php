<?php

namespace App\Services;

use App\Jobs\RegistrarEventLogJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class EventLogService
{
    /**
     * Registra un evento de auditoría en MongoDB.
     *
     * @param string $eventType Tipo de evento (ej: usuario_registrado)
     * @param array $payload Datos específicos del evento
     * @param int|null $userId ID del usuario (opcional, si no se envía toma el autenticado)
     */
    public function log(string $eventType, array $payload, ?int $userId = null): void
    {
        RegistrarEventLogJob::dispatch(
            $eventType,
            $payload,
            $userId ?? Auth::id(),
            Request::ip(),
            Request::userAgent()
        );
    }
}