<?php

namespace App\Services;

use App\Models\MongoEventLog;
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
     * @return MongoEventLog
     */
    public function log(string $eventType, array $payload, ?int $userId = null): MongoEventLog
    {
        return MongoEventLog::create([
            'event_type' => $eventType,
            'payload'    => $payload,
            'user_id'    => $userId ?? Auth::id(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}