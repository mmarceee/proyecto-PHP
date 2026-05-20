<?php

namespace App\Services;

use Carbon\Carbon;

class AgendaService
{
    /**
     * Generar la estructura de la semana actual para un profesional
     */
    public function obtenerAgendaSemana($profesional)
    {
        $semana = [];
        
        // Esto alinea $i: 0 = Domingo, 1 = Lunes, ..., 6 = Sábado
        $inicioSemana = Carbon::now()->startOfWeek(Carbon::SUNDAY);

        for ($i = 0; $i < 7; $i++) {
            $fechaActual = $inicioSemana->copy()->addDays($i);

            // Ejemplo de lógica: Simulamos un turno ocupado el lunes (índice 1)
            $bloques = [
                ['hora' => '08:00', 'es_pausa' => false, 'ocupado' => false],
                ['hora' => '09:00', 'es_pausa' => false, 'ocupado' => ($i === 1)], //Dia ocupado
                ['hora' => '10:00', 'es_pausa' => false, 'ocupado' => false],
                ['hora' => '11:00', 'es_pausa' => false, 'ocupado' => false],
                ['hora' => '12:00', 'es_pausa' => true, 'etiqueta' => 'ALMUERZO / PAUSA'],
                ['hora' => '14:00', 'es_pausa' => false, 'ocupado' => false],
                ['hora' => '15:00', 'es_pausa' => false, 'ocupado' => false],
                ['hora' => '16:00', 'es_pausa' => false, 'ocupado' => false],
            ];

            $semana[] = [
                'num_dia_semana' => $i, // 0 = Domingo, 6 = Sábado
                'nombre_dia' => ucfirst($fechaActual->isoFormat('dddd')),
                'fecha_formateada' => $fechaActual->format('d') . ' de ' . ucfirst($fechaActual->isoFormat('MMMM')),
                'fecha' => $fechaActual->toDateString(),
                'bloques' => $bloques
            ];
        }

        return $semana;
    }
}