<?php

namespace App\Services;

use App\Models\Profesional;
use App\Models\Servicio;
use Carbon\Carbon;

class DisponibilidadService
{
    /**
     * Calcula los bloques horarios disponibles para un profesional en una fecha específica.
     */
    public function obtenerHorariosDisponibles(Profesional $profesional, Servicio $servicio, string $fechaCandidata): array
    {
        $fecha = Carbon::parse($fechaCandidata);
        
        // 1. Verificamos si hay excepciones de DÍA COMPLETO (Feriados o Licencias)
        if ($this->tieneExcepcionDiaCompleto($profesional, $fecha)) {
            return []; // Retorna array vacío, no hay turnos este día.
        }

        // 2. Obtenemos el horario base del profesional para ese día de la semana
        $diaSemana = $fecha->dayOfWeekIso; // 1 = Lunes, 7 = Domingo
        $reglasBase = $profesional->reglasDisponibilidad()
                                  ->where('diaSemana', $diaSemana)
                                  ->get();

        if ($reglasBase->isEmpty()) {
            return []; // El profesional no atiende este día de la semana.
        }

        // 3. A partir de aquí, tenemos los rangos base (Ej: 09:00 a 13:00 y 14:00 a 18:00)
        // El próximo paso será dividirlos en "slots" (huecos) y restar los turnos ocupados.
        
        return $this->calcularSlotsLibres($reglasBase, $profesional, $servicio, $fecha);
    }

    /**
     * Verifica si existe un bloqueo total para la fecha.
     */
    private function tieneExcepcionDiaCompleto(Profesional $profesional, Carbon $fecha): bool
    {
        return $profesional->excepcionesDisponibilidad()
            ->where('fecha', $fecha->toDateString())
            ->whereIn('tipo', ['feriado', 'licencia', 'no_disponible'])
            ->whereNull('horaInicio') // Si horaInicio es nulo, aplica a todo el día
            ->exists();
    }

    /**
     * Motor matemático (Pendiente de implementar en el siguiente paso)
     */
    private function calcularSlotsLibres($reglasBase, Profesional $profesional, Servicio $servicio, Carbon $fecha): array
    {
        $slots = [];
        // Aquí aplicaremos la duración del servicio y el bufferEntreTurnos
        return $slots;
    }
}