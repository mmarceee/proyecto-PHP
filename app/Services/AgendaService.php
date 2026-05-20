<?php

namespace App\Services;

use App\Models\ReglaDisponibilidad;
use App\Models\ExcepcionDisponibilidad;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AgendaService
{
    /**
     * Obtener la agenda de 7 días rodantes a partir de una fecha específica
     */
    public function obtenerAgendaSemana($profesional, $fechaInicioString = null)
    {
        $semana = [];
        
        //Si viene una fecha de la URL la usamos, si no, arranca desde HOY
        $inicioSemana = $fechaInicioString ? Carbon::parse($fechaInicioString) : Carbon::now();
        $finSemana = $inicioSemana->copy()->addDays(6);

        // 1. Traemos las reglas base semanales
        $reglasBase = $profesional->reglasDisponibilidad->keyBy('dia_semana');

        // 2. Traemos las excepciones en el rango de estos 7 días flotantes
        $excepciones = ExcepcionDisponibilidad::where('profesional_id', $profesional->id)
            ->whereBetween('fecha', [$inicioSemana->toDateString(), $finSemana->toDateString()])
            ->get()
            ->keyBy(function($e) {
                return $e->fecha->toDateString();
            });

        // 3. Iteramos 7 días hacia adelante a partir del día de inicio
        for ($i = 0; $i < 7; $i++) {
            $fechaActual = $inicioSemana->copy()->addDays($i);
            $fechaString = $fechaActual->toDateString();
            
            //El día de la semana real (0=Domingo, 6=Sábado) de la fecha que se está procesando
            $diaSemanaReal = $fechaActual->dayOfWeek; 

            $bloques = [];
            $motivoCierre = null;
            $esLaboral = false;

            // CASO A: Hay una excepción cargada para esta fecha específica
            if ($excepciones->has($fechaString)) {
                $excepcion = $excepciones->get($fechaString);

                if (in_array($excepcion->tipo, ['feriado', 'licencia', 'no_disponible']) && is_null($excepcion->horaInicio)) {
                    $motivoCierre = $excepcion->motivo ?? ucfirst(str_replace('_', ' ', $excepcion->tipo));
                } else {
                    $horaInicio = $excepcion->horaInicio ? Carbon::parse($excepcion->horaInicio) : null;
                    $horaFin = $excepcion->horaFin ? Carbon::parse($excepcion->horaFin) : null;
                    
                    $reglaComun = $reglasBase->get($diaSemanaReal);
                    $duracion = $reglaComun ? (int)$reglaComun->duracion_turno : 60;
                    $buffer = $reglaComun ? (int)$reglaComun->buffer_tiempo : 0;

                    if ($horaInicio && $horaFin) {
                        $bloques = $this->calcularBloquesHorarios($horaInicio, $horaFin, $duracion, $buffer);
                        $esLaboral = count($bloques) > 0;
                    }
                }
            } 
            // CASO B: No hay excepción, usamos el patrón base de ese día de la semana
            elseif ($reglasBase->has($diaSemanaReal)) {
                $regla = $reglasBase->get($diaSemanaReal);
                
                $horaInicio = Carbon::parse($regla->hora_inicio);
                $horaFin = Carbon::parse($regla->hora_fin);
                
                $bloques = $this->calcularBloquesHorarios($horaInicio, $horaFin, (int)$regla->duracion_turno, (int)$regla->buffer_tiempo);
                $esLaboral = count($bloques) > 0;
            }

            // Marcamos si es "Hoy" para darle un toque visual en el frontend
            $esHoy = $fechaString === Carbon::now()->toDateString();

            $semana[] = [
                'num_dia_semana' => $diaSemanaReal,
                'nombre_dia' => $esHoy ? 'Hoy' : ucfirst($fechaActual->isoFormat('dddd')),
                'fecha_formateada' => $fechaActual->format('d') . ' de ' . ucfirst($fechaActual->isoFormat('MMMM')),
                'fecha' => $fechaString,
                'es_laboral' => $esLaboral,
                'es_hoy' => $esHoy,
                'motivo_cierre' => $motivoCierre,
                'tiene_excepcion' => $excepciones->has($fechaString),
                'bloques' => $bloques
            ];
        }

        return $semana;
    }

    private function calcularBloquesHorarios($inicio, $fin, $duracion, $buffer)
    {
        $bloques = [];
        $saltoTotal = $duracion + $buffer;

        while ($inicio->copy()->addMinutes($duracion)->lessThanOrEqualTo($fin)) {
            $bloques[] = [
                'hora' => $inicio->format('H:i'),
                'ocupado' => false,
                'duracion' => $duracion,
                'buffer' => $buffer
            ];
            $inicio->addMinutes($saltoTotal);
        }

        return $bloques;
    }

    public function guardarReglasBase($profesional, array $reglas)
    {
        DB::transaction(function () use ($profesional, $reglas) {
            $profesional->reglasDisponibilidad()->delete();

            foreach ($reglas as $regla) {
                if (!empty($regla['activo']) && !empty($regla['hora_inicio']) && !empty($regla['hora_fin'])) {
                    $profesional->reglasDisponibilidad()->create([
                        'dia_semana'     => $regla['dia_semana'],
                        'hora_inicio'    => $regla['hora_inicio'],
                        'hora_fin'       => $regla['hora_fin'],
                        'duracion_turno' => $regla['duracion_turno'] ?? 60,
                        'buffer_tiempo'  => $regla['buffer_tiempo'] ?? 0,
                    ]);
                }
            }
        });

        return true;
    }

    public function guardarExcepcion($profesional, array $datos)
    {
        return \App\Models\ExcepcionDisponibilidad::create([
            'profesional_id' => $profesional->id,
            'fecha'          => $datos['fecha'],
            'tipo'           => $datos['tipo'],
            'motivo'         => $datos['motivo'] ?? null,
            'horaInicio'     => null,
            'horaFin'        => null,
        ]);
    }

    public function eliminarExcepcion($profesional, $fecha)
    {
        return \App\Models\ExcepcionDisponibilidad::where('profesional_id', $profesional->id)
            ->where('fecha', $fecha)
            ->delete();
    }
}