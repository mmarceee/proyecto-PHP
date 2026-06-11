<?php

namespace App\Services;

use App\Models\MongoEventLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log; // Añadido para el log por defecto

class EventLogService
{
    /**
     * Registra un evento de auditoría en MongoDB.
     *
     * @param string $eventType Tipo de evento (ej: usuario_registrado)
     * @param array $payload Datos específicos del evento
     * @param int|null $userId ID del usuario (opcional, si no se envía toma el autenticado)
     * @return MongoEventLog|null
     */
    public function log(string $eventType, array $payload, ?int $userId = null): ?MongoEventLog
    {
        try {
            return MongoEventLog::create([
                'event_type' => $eventType,
                'payload'    => $payload,
                'user_id'    => $userId ?? Auth::id(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Exception $e) {
            // Resiliencia: Si MongoDB no está disponible en producción, 
            // no rompemos la app (Error 500). Solo enviamos el error al log del sistema.
            Log::error('Fallo al auditar en MongoDB: ' . $e->getMessage());
            return null;
        }
    }
}