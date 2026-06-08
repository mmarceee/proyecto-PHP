<?php

namespace App\Services;

use App\Events\EstadoReservaCambiado;
use App\Events\AgendaActualizada;
use App\Models\Reserva;
use App\Jobs\EnviarNotificacionReserva;
use App\Jobs\EnviarSolicitudResenaJob;
use App\Models\CompraPaquete;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ReservaService
{
    public function __construct(
        private NotificacionService $notificacionService
    ) {}

    /**
     * Valida que no existan superposiciones horarias para el mismo profesional
     */
    public function verificarChoqueHorario($profesionalId, $fecha, $horaInicio, $horaFin, $excluirReservaId = null, $clienteId = null)
    {
        $query = Reserva::where('profesional_id', $profesionalId)
            ->where('fecha', $fecha)
            ->where('estado_reserva', '!=', 'cancelada')
            ->where('hora_inicio', '<', $horaFin)
            ->where('hora_fin', '>', $horaInicio);

        if ($excluirReservaId) {
            $query->where('id', '!=', $excluirReservaId);
        }

        if ($query->exists()) {
            throw new \Exception('El profesional ya tiene una reserva activa en ese rango horario.');
        }

        // Validar si existe un bloqueo temporal en caché
        $horaInicioFormateada = Carbon::parse($horaInicio)->format('H:i:s');
        $llaveCache = "lock_turno_{$profesionalId}_{$fecha}_{$horaInicioFormateada}";
        $lockOwner = Cache::get($llaveCache);

        if ($lockOwner && $lockOwner != $clienteId) {
            throw new \Exception('El turno se encuentra temporalmente reservado por otro cliente en este momento.');
        }
    }

    /**
     * Crear una nueva reserva desde el backend verificando choques
     */
    public function crear(array $datos)
    {
        $compraPaqueteId = $datos['compra_paquete_id'] ?? null;
        unset($datos['compra_paquete_id']);

        $this->verificarChoqueHorario(
            $datos['profesional_id'], 
            $datos['fecha'], 
            $datos['hora_inicio'], 
            $datos['hora_fin'],
            null,
            $datos['cliente_id'] ?? null
        );

        $reserva = \Illuminate\Support\Facades\DB::transaction(function () use ($datos, $compraPaqueteId) {
            
            $reserva = Reserva::create([
                'cliente_id'     => $datos['cliente_id'],
                'profesional_id' => $datos['profesional_id'],
                'servicio_id'    => $datos['servicio_id'],
                'fecha'          => $datos['fecha'],
                'hora_inicio'    => $datos['hora_inicio'],
                'hora_fin'       => $datos['hora_fin'],
                'estado_reserva' => $datos['estado_reserva'] ?? 'pendiente',
            ]);

            if ($compraPaqueteId) {
                $compra = CompraPaquete::find($compraPaqueteId);
                
                if ($compra && $compra->sesiones_disponibles > 0) {
                    // Invocamos a nuestro PaqueteService para registrar el consumo
                    $paqueteService = app(PaqueteService::class);
                    $paqueteService->consumirSesion($compra, $reserva->id);
                }
            }

            return $reserva;
        });

        // Liberar el bloqueo temporal ya que la reserva real fue creada
        $horaInicioFormateada = Carbon::parse($datos['hora_inicio'])->format('H:i:s');
        $llaveCache = "lock_turno_{$datos['profesional_id']}_{$datos['fecha']}_{$horaInicioFormateada}";
        Cache::forget($llaveCache);

        // Disparar actualización por WebSocket en tiempo real
        $this->despacharCambioAgenda($reserva->profesional_id, $reserva->fecha);

        
        $this->notificacionService->notificarNuevaReserva($reserva);

        // REGISTRO DE AUDITORÍA NOSQL
        app(\App\Services\EventLogService::class)->log('reserva_creada', [
            'reserva_id'     => $reserva->id,
            'cliente_id'     => $reserva->cliente_id,
            'profesional_id' => $reserva->profesional_id,
            'servicio_id'    => $reserva->servicio_id,
            'fecha'          => $reserva->fecha->format('Y-m-d'),
            'hora_inicio'    => $reserva->hora_inicio,
            'hora_fin'       => $reserva->hora_fin,
        ], $reserva->cliente?->user_id);

        return $reserva;
        
    }

    /**
     * Bloquea temporalmente un turno en caché para dar tiempo de pago al cliente.
     */
    public function bloquearTurnoTemporal($profesionalId, $fecha, $horaInicio, $clienteId, $minutos = 1)
    {
        $horaInicioFormateada = Carbon::parse($horaInicio)->format('H:i:s');
        $llaveCache = "lock_turno_{$profesionalId}_{$fecha}_{$horaInicioFormateada}";

        // Usamos add() para asegurar atomicidad. Si ya existe, retorna false.
        $bloqueado = Cache::add($llaveCache, $clienteId, now()->addMinutes($minutos));

        if (!$bloqueado) {
            $lockOwner = Cache::get($llaveCache);
            if ($lockOwner != $clienteId) {
                 throw new \Exception('El turno acaba de ser tomado por otro cliente.');
            }
        }

        return true;
    }

    /**
     * Reprogramar una reserva validando choques y restricciones de paquetes
     */
    public function actualizar(Reserva $reserva, array $datos)
    {
        // 1. Validar choque de horarios (excluyendo la reserva actual)
        $this->verificarChoqueHorario(
            $reserva->profesional_id, 
            $datos['fecha'], 
            $datos['hora_inicio'], 
            $datos['hora_fin'], 
            $reserva->id
        );

        // 2. Validar consistencia de servicios si pertenece a un paquete contratado
        $usoSesion = $reserva->uso_sesion_paquete;
        if ($usoSesion) {
            $paquete = $usoSesion->compraPaquete;
            if ($datos['servicio_id'] != $paquete->paquete_servicio_id) {
                throw new \Exception('El nuevo servicio seleccionado no coincide con el paquete contratado para esta reserva.');
            }
        }

        // Guardamos la fecha vieja antes de actualizar por si se cambia de día la reserva
        $fechaOriginal = $reserva->fecha;

        $reserva->update($datos);

        // Notificar el cambio al día asignado
        $this->despacharCambioAgenda($reserva->profesional_id, $reserva->fecha);
        
        // Si se cambió de fecha, actualizamos también la vieja para liberar el hueco en el front
        if ($fechaOriginal !== $reserva->fecha) {
            $this->despacharCambioAgenda($reserva->profesional_id, $fechaOriginal);
        }

        return $reserva;
    }

    /**
     * Cancelar una reserva de forma limpia y con reintegro automático de paquetes
     */
    public function cancelar(Reserva $reserva, string $motivo)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($reserva, $motivo) {
            // 1. Cancelamos la reserva normalmente
            $reserva->update([
                'estado_reserva'     => 'cancelada',
                'motivo_cancelacion' => $motivo,
            ]);

            // 2. LÓGICA DE REINTEGRO: Verificamos si esta reserva usó una sesión de un paquete
            $usoSesion = $reserva->uso_sesion_paquete;

            if ($usoSesion) {
                $compra = $usoSesion->compraPaquete;
                
                // Devolvemos la sesión al contador
                $compra->increment('sesiones_disponibles');
                $compra->decrement('sesiones_consumidas');

                // Si el paquete se había quedado sin saldo ("completado"), lo "revivimos"
                if ($compra->estado_paquete === 'completado' && $compra->sesiones_disponibles > 0) {
                    $compra->update(['estado_paquete' => 'activo']);
                }
                // Eliminamos el registro del consumo para que no aparezca en el historial
                // como una sesión "gastada"
                $usoSesion->delete();
            }

            // Disparar WebSocket para liberar el horario en las pantallas de los demás
            $this->despacharCambioAgenda($reserva->profesional_id, $reserva->fecha);

            // Disparamos usando el user_id real del cliente para que coincida con el frontend
            \Log::info("[WS BACKEND] Disparando cancelación al cliente (User ID): " . $reserva->cliente->user_id);
            broadcast(new EstadoReservaCambiado($reserva->cliente->user_id, $reserva->id, 'cancelada'));
            
            // 3. Despachamos las notificaciones
            $this->notificacionService->notificarReservaCancelada($reserva);
            EnviarNotificacionReserva::dispatch($reserva, 'Cancelada');
            
            return $reserva;
        });
         // 2. Registramos el evento de auditoría NoSQL fuera de la transacción relacional
        try {
            app(\App\Services\EventLogService::class)->log('reserva_cancelada', [
                'reserva_id' => $reserva->id,
                'cliente_id' => $reserva->cliente_id,
                'motivo'     => $motivo,
            ], $reserva->cliente?->user_id);
        } catch (\Exception $e) {
            \Log::error("Fallo al registrar auditoría NoSQL para reserva cancelada: " . $e->getMessage());
        }
        return $reserva;
    }

    /**
     * Hace avanzar el estado de la reserva de forma inteligente
     */
    public function avanzarEstado(Reserva $reserva)
    {
        $estadoAnterior = $reserva->estado_reserva;

        $nuevoEstado = match ($reserva->estado_reserva) {
            'pendiente'             => 'confirmada',
            'confirmada', 'pagada'  => 'en_curso',
            'en_curso'              => 'finalizada',
            default                 => $reserva->estado_reserva,
        };

        $reserva->update([
            'estado_reserva' => $nuevoEstado
        ]);

        //Despachamos a la cola
        EnviarNotificacionReserva::dispatch($reserva, $nuevoEstado);

        if ($estadoAnterior === 'pendiente' && $nuevoEstado === 'confirmada') {
            $this->notificacionService->notificarReservaConfirmada($reserva);
        }
            
        if($nuevoEstado === 'finalizada') {
            EnviarSolicitudResenaJob::dispatch($reserva)->delay(now()->addHour());
        }

        // Disparar WebSocket por si cambia la visualización del bloque según el estado
        $this->despacharCambioAgenda($reserva->profesional_id, $reserva->fecha);

        // Disparamos usando el user_id real del cliente para que coincida con el frontend
        \Log::info("[WS BACKEND] Disparando evento de estado al cliente (User ID): " . $reserva->cliente->user_id); // es para ver si funciona, sale en la consola f12
        broadcast(new EstadoReservaCambiado($reserva->cliente->user_id, $reserva->id, $nuevoEstado));

        if ($estadoAnterior === 'confirmada' && $nuevoEstado === 'en_curso') {
            $this->notificacionService->notificarReservaEnCurso($reserva);
        }

        if ($estadoAnterior === 'pagada' && $nuevoEstado === 'en_curso') {
            $this->notificacionService->notificarReservaEnCurso($reserva);
        }

        if ($estadoAnterior === 'en_curso' && $nuevoEstado === 'finalizada') {
            $this->notificacionService->notificarReservaFinalizada($reserva);
        }

        return $reserva;
    }

    /**
     * Método Auxiliar Privado: Obtiene los bloques ocupados del día y emite el WebSocket.
     */
    private function despacharCambioAgenda($profesionalId, $fecha)
    {
        $bloquesOcupados = Reserva::where('profesional_id', $profesionalId)
            ->where('fecha', $fecha)
            ->where('estado_reserva', '!=', 'cancelada')
            ->get(['hora_inicio', 'hora_fin', 'estado_reserva'])
            ->toArray();

        \Log::info("[WS BACKEND] Disparando broadcast para el profesional ID: " . $profesionalId); //lo mismo consola f12

        
        broadcast(new AgendaActualizada($profesionalId, $bloquesOcupados));
    }
}