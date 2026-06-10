<?php

namespace App\Services;

use App\Models\Reserva;
use App\Models\Cliente;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Obtener las consultas agendadas para el día de hoy de un profesional
     */
    public function obtenerConsultasHoy($profesionalId)
    {
        $hoy = Carbon::today()->toDateString();

        // Traemos las reservas de hoy que no estén canceladas
        $reservas = Reserva::with('cliente.user')
            ->where('profesional_id', $profesionalId)
            ->where('fecha', $hoy)
            ->whereNotIn('estado_reserva', ['cancelada', 'no_asistida'])
            ->orderBy('hora_inicio', 'asc')
            ->get();

        return $reservas->map(function ($reserva) {
            $horaCarbon = Carbon::parse($reserva->hora_inicio);
            $userCliente = $reserva->cliente?->user;
            $nombreCliente = trim(($userCliente?->name ?? '') . ' ' . ($userCliente?->apellido ?? ''));

            return [
                'id' => $reserva->id,
                'time' => $horaCarbon->format('H:i'), // Formato "14:30"
                'period' => $horaCarbon->format('A'), // "AM" o "PM"
                'client_name' => $nombreCliente ?: 'Paciente Anónimo',
                'client_email' => $userCliente?->email ?? '',
                'reason' => 'Consulta de control general', // Puedes cambiarlo si agregás columna motivo
                'status'       => ucfirst(str_replace('_', ' ', $reserva->estado_reserva)), // Ej: "Pendiente", "Confirmada", "En curso"
                'status_raw'   => $reserva->estado_reserva, // Nos sirve para evaluar en el JS de Alpine sin formatear
                
                //BOTÓN DE ACCIÓN INTELIGENTE BASADO EN LA LISTA DE ENUMS
                'action_label' => match ($reserva->estado_reserva) {
                    'pendiente'             => 'Confirmar',
                    'confirmada', 'pagada'  => 'Iniciar',
                    'en_curso'              => 'Finalizar',
                    default                 => null, // Para finalizada, cancelada o no_asistida no hay acción
                },
                'packages' => [] // Estructura reservada para futuras sesiones de paquetes
            ];
        })->toArray();
    }

    /**
     * Obtener reservas pendientes del profesional desde hoy en adelante
     */
    public function obtenerReservasPendientesProfesional($profesionalId)
    {
        $hoy = Carbon::today()->toDateString();

        $reservas = Reserva::with(['cliente.user', 'servicio'])
            ->where('profesional_id', $profesionalId)
            ->where('fecha', '>=', $hoy)
            ->where('estado_reserva', 'pendiente')
            ->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        return $reservas->map(function ($reserva) {
            $fechaReserva = Carbon::parse($reserva->fecha);
            $horaInicio = Carbon::parse($reserva->hora_inicio);

            if ($fechaReserva->isToday()) {
                $dateLabel = 'Hoy';
            } elseif ($fechaReserva->isTomorrow()) {
                $dateLabel = 'Mañana';
            } else {
                $dateLabel = ucfirst($fechaReserva->isoFormat('dddd D/M'));
            }

            $userCliente = $reserva->cliente?->user;

            $nombreCliente = trim(($userCliente?->name ?? '') . ' ' . ($userCliente?->apellido ?? ''));

            return [
                'id' => $reserva->id,
                'date_label' => $dateLabel,
                'date' => $fechaReserva->format('d/m/Y'),
                'time' => $horaInicio->format('H:i'),
                'client_name' => $nombreCliente ?: 'Paciente Anónimo',
                'client_email' => $userCliente?->email ?? '',
                'service_name' => $reserva->servicio?->nombre ?? 'Servicio',
                'status' => ucfirst(str_replace('_', ' ', $reserva->estado_reserva)),
                'status_raw' => $reserva->estado_reserva,
                'action_label' => 'Confirmar',
            ];
        })->toArray();
    }

    /**
     * Obtener las próximas sesiones de un cliente (de hoy en adelante)
     */
    public function obtenerProximasSesiones($userId)
    {
        // Primero buscamos el perfil de cliente del usuario logueado
        $cliente = Cliente::where('user_id', $userId)->first();

        if (!$cliente) {
            return [];
        }

        $hoy = Carbon::today();

        $reservas = Reserva::with(['profesional.user', 'servicio'])
            ->where('cliente_id', $cliente->id)
            ->where('fecha', '>=', $hoy->toDateString())
            ->whereNotIn('estado_reserva', ['cancelada', 'no_asistida'])
            ->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        return $reservas->map(function ($reserva) {
            $fechaReserva = Carbon::parse($reserva->fecha);
            $horaCarbon = Carbon::parse($reserva->hora_inicio);

            // Calculamos la etiqueta dinámica del día (date_label)
            if ($fechaReserva->isToday()) {
                $dateLabel = 'Hoy';
            } elseif ($fechaReserva->isTomorrow()) {
                $dateLabel = 'Mañana';
            } else {
                $dateLabel = ucfirst($fechaReserva->isoFormat('dddd')); // Ej: "Lunes", "Viernes"
            }

            return [
                'id' => $reserva->id,
                'date_label' => $dateLabel,
                'time' => $horaCarbon->format('H:i'),
                'professional_name' => $reserva->profesional?->nombre_comercial
                    ?: trim(
                        ($reserva->profesional?->user?->name ?? '') . ' ' .
                        ($reserva->profesional?->user?->apellido ?? '')
                    )
                    ?: 'Profesional',
                'professional_email' => $reserva->profesional?->user?->email ?? '',
                'specialty' => $reserva->servicio?->nombre ?? 'Especialidad',
                'status' => ucfirst(str_replace('_', ' ', $reserva->estado_reserva)),
                'packages' => [] // Estructura reservada para mantener reactividad en Alpine
            ];
        })->toArray();
    }
}