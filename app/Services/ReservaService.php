<?php

namespace App\Services;

use App\Events\AgendaActualizada;
use App\Models\Reserva;
use App\Jobs\EnviarNotificacionReserva;
use App\Jobs\EnviarSolicitudResenaJob;

class ReservaService
{
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
        $this->verificarChoqueHorario(
            $datos['profesional_id'], 
            $datos['fecha'], 
            $datos['hora_inicio'], 
            $datos['hora_fin']
        );

        $reserva = Reserva::create([
            'cliente_id'     => $datos['cliente_id'],
            'profesional_id' => $datos['profesional_id'],
            'servicio_id'    => $datos['servicio_id'],
            'fecha'          => $datos['fecha'],
            'hora_inicio'    => $datos['hora_inicio'],
            'hora_fin'       => $datos['hora_fin'],
            'estado_reserva' => $datos['estado_reserva'] ?? 'pendiente',
        ]);

        // Disparar actualización por WebSocket en tiempo real
        $this->despacharCambioAgenda($reserva->profesional_id, $reserva->fecha);

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
     * Cancelar una reserva de forma ultra limpia
     */
    public function cancelar(Reserva $reserva, string $motivo)
    {
        // El servicio solo altera el estado del recurso en la BD
        $reserva->update([
            'estado_reserva'     => 'cancelada',
            'motivo_cancelacion' => $motivo,
        ]);

        //Despachamos a la cola de Redis
        EnviarNotificacionReserva::dispatch($reserva, 'Cancelada');

        // Disparar WebSocket para liberar el horario en las pantallas de los demás
        $this->despacharCambioAgenda($reserva->profesional_id, $reserva->fecha);

        return $reserva;
    }

    /**
     * Hace avanzar el estado de la reserva de forma inteligente
     */
    public function avanzarEstado(Reserva $reserva)
    {
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

        if($nuevoEstado === 'finalizada') {
            EnviarSolicitudResenaJob::dispatch($reserva)->delay(now()->addHour());
        }

        // Disparar WebSocket por si cambia la visualización del bloque según el estado
        $this->despacharCambioAgenda($reserva->profesional_id, $reserva->fecha);
       

        return $reserva;
    }

    /**
     * Método Auxiliar Privado: Obtiene los bloques ocupados del día y emite el WebSocket.
     * Mantiene el código limpio y centralizado sin duplicar lógica.
     */
    private function despacharCambioAgenda($profesionalId, $fecha)
    {
        // Obtenemos todos los turnos ocupados de ese día usando tus mismas reglas conceptuales
        $bloquesOcupados = Reserva::where('profesional_id', $profesionalId)
            ->where('fecha', $fecha)
            ->where('estado_reserva', '!=', 'cancelada')
            ->get(['hora_inicio', 'hora_fin', 'estado_reserva'])
            ->toArray();

        // Emitimos el evento a Reverb (excluyendo al usuario que gatilló la petición HTTP original)
        broadcast(new AgendaActualizada($profesionalId, $bloquesOcupados))->toOthers();
    }
}