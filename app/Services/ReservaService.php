<?php

namespace App\Services;

use App\Models\Reserva;
use App\Jobs\EnviarNotificacionReserva;
use App\Jobs\EnviarSolicitudResenaJob;
use App\Models\CompraPaquete;

class ReservaService
{
    public function __construct(
        private NotificacionService $notificacionService
    ) {}

    /**
     * Valida que no existan superposiciones horarias para el mismo profesional
     */
    public function verificarChoqueHorario($profesionalId, $fecha, $horaInicio, $horaFin, $excluirReservaId = null)
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
            $datos['hora_fin']
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

        $this->notificacionService->notificarNuevaReserva($reserva);

        return $reserva;
        
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
            $paquete = $usoSesion->compra_paquete;
            if ($datos['servicio_id'] != $paquete->paquete_servicio_id) {
                throw new \Exception('El nuevo servicio seleccionado no coincide con el paquete contratado para esta reserva.');
            }
        }

        $reserva->update($datos);
        return $reserva;
    }

    /**
     * Cancelar una reserva de forma ultra limpia
     */
    public function cancelar(Reserva $reserva, string $motivo)
    {
        // El servicio solo altera el estado del recurso en la BD
        $reserva->update([
            'estado_reserva'     => 'cancelada',
            'motivo_cancelacion' => $motivo,
        ]);

        $this->notificacionService->notificarReservaCancelada($reserva);

        //Despachamos a la cola de Redis
        EnviarNotificacionReserva::dispatch($reserva, 'Cancelada');

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
}