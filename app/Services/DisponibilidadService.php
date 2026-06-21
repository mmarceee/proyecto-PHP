<?php

namespace App\Services;

use App\Models\Profesional;
use App\Models\Reserva;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DisponibilidadService
{
    /**
     * Calcula los bloques horarios disponibles para un profesional en una fecha específica.
     */
    public function obtenerHorariosDisponibles(Profesional $profesional, Servicio $servicio, string $fechaCandidata): array
    {
        $fecha = Carbon::parse($fechaCandidata);
        
        // Verificamos si hay excepciones de DÍA COMPLETO (Feriados o Licencias)
        if ($this->tieneExcepcionDiaCompleto($profesional, $fecha)) {
            return []; // Retorna array vacío, no hay turnos este día.
        }

        // Obtenemos el horario base del profesional para ese día de la semana
        $diaSemana = $fecha->dayOfWeek; // 0 = Domingo, 6 = Sabado
        $reglasBase = $profesional->reglasDisponibilidad()
                                  ->where('dia_semana', $diaSemana)
                                  ->get();

        if ($reglasBase->isEmpty()) {
            return []; // El profesional no atiende este día de la semana.
        }

        return $this->calcularSlotsLibres($reglasBase, $profesional, $servicio, $fecha);
    }

    /**
     * Verifica si existe un bloqueo total para la fecha.
     */
    private function tieneExcepcionDiaCompleto(Profesional $profesional, Carbon $fecha): bool
    {
        return $profesional->excepcionDisponibilidad()
            ->where('fecha', $fecha->toDateString())
            ->whereIn('tipo', ['feriado', 'licencia', 'no_disponible'])
            ->whereNull('horaInicio') // Si horaInicio es nulo, aplica a todo el día
            ->exists();
    }

    /**
     * Motor matemático para generar slots y filtrar ocupados
     */
    private function calcularSlotsLibres($reglasBase, Profesional $profesional, Servicio $servicio, Carbon $fecha): array
    {
        $slots = [];
        
        // Obtenemos la duración real del servicio en minutos (por defecto 30 si no tuviera)
        $duracion = $servicio->duracion ?? 30; 

        // Buscamos las reservas ACTIVAS de ese día para no encimar turnos
        $reservasDelDia = Reserva::where('profesional_id', $profesional->id)
            ->where('fecha', $fecha->toDateString())
            ->where('estado_reserva', '!=', 'cancelada') // Filtramos las canceladas
            ->get();

        // Iteramos sobre cada regla de horario (ej: de 08:00 a 16:00)
        foreach ($reglasBase as $regla) {
            $inicioBloque = Carbon::parse($fecha->toDateString() . ' ' . $regla->hora_inicio);
            $finBloque = Carbon::parse($fecha->toDateString() . ' ' . $regla->hora_fin);

            $slotActual = $inicioBloque->copy();

            // Mientras el slot actual + la duración no supere la hora de cierre
            while ($slotActual->copy()->addMinutes($duracion)->lte($finBloque)) {
                $slotFin = $slotActual->copy()->addMinutes($duracion);

                // Comprobar si el bloque choca con alguna reserva en la base de datos
                $ocupado = $reservasDelDia->contains(function ($reserva) use ($slotActual, $slotFin, $fecha) {
                    $reservaInicio = Carbon::parse($fecha->toDateString() . ' ' . $reserva->hora_inicio);
                    $reservaFin = Carbon::parse($fecha->toDateString() . ' ' . $reserva->hora_fin);

                    // Existe solapamiento si nuestro slot empieza antes de que termine la reserva 
                    // Y termina después de que empiece la reserva
                    return $slotActual->lt($reservaFin) && $slotFin->gt($reservaInicio);
                });

                // Evitar mostrar turnos del pasado si el cliente está buscando turnos para "Hoy"
                $esPasado = $fecha->isToday() && $slotActual->isPast();

                // Verificar si el turno está bloqueado temporalmente en Caché (Hold Pattern)
                $llaveCache = "lock_turno_{$profesional->id}_{$fecha->toDateString()}_{$slotActual->format('H:i:s')}";
                $bloqueadoTemporalmente = Cache::has($llaveCache);

                // Si está totalmente libre, lo agregamos a la lista final
                if (!$ocupado && !$esPasado && !$bloqueadoTemporalmente) {
                    $slots[] = $slotActual->format('H:i');
                }

                // Avanzamos el puntero para armar el siguiente turno (Ej: de 12:00 saltamos a 12:30)
                $slotActual->addMinutes($duracion);
            }
        }

        return $slots;
    }
}