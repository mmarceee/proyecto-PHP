<?php

namespace App\Services;

use App\Models\ReglaDisponibilidad;
use App\Models\ExcepcionDisponibilidad;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Profesional;

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

        //SOLUCIÓN AL CLUSTERING DE FECHAS:
        // Forzamos el agrupamiento usando estrictamente el formato 'YYYY-MM-DD' string limpio
        // para romper el conflicto del cast 'date' del modelo Reserva.
        $reservasExistentes = Reserva::where('profesional_id', $profesional->id)
            ->whereBetween('fecha', [$inicioSemana->toDateString(), $finSemana->toDateString()])
            ->whereNotIn('estado_reserva', ['cancelada', 'no_asistida'])
            ->get()
            ->groupBy(function($reserva) {
                return Carbon::parse($reserva->fecha)->toDateString();
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

            //COMPROBACIÓN DE COLISIONES ULTRA-SEGURA
            if ($esLaboral && count($bloques) > 0) {
                foreach ($bloques as &$bloque) {
                    
                    // Convertimos la hora del bloque a string "H:i" de forma segura sin importar su tipo base
                    if ($bloque instanceof Carbon) {
                        $horaBloqueClean = $bloque->format('H:i');
                    } elseif (is_array($bloque)) {
                        $horaStr = $bloque['hora'] ?? $bloque[0] ?? '00:00';
                        $horaBloqueClean = date('H:i', strtotime($horaStr));
                    } else {
                        $horaBloqueClean = date('H:i', strtotime($bloque));
                    }

                    $estaOcupado = false;

                    // Ahora la comparación de llaves del array va a dar TRUE perfectamente
                    if (isset($reservasExistentes[$fechaString])) {
                        foreach ($reservasExistentes[$fechaString] as $reserva) {
                            
                            // Normalizamos los tiempos de la reserva para evitar fallos de tipos
                            $inicioReserva = date('H:i', strtotime($reserva->hora_inicio));
                            $finReserva = date('H:i', strtotime($reserva->hora_fin));

                            // Si se pisa el rango horario, encendemos el flag
                            if ($horaBloqueClean >= $inicioReserva && $horaBloqueClean < $finReserva) {
                                $estaOcupado = true;
                                break;
                            }
                        }
                    }

                    // Validar si existe un bloqueo temporal en caché (Hold Pattern)
                    if (!$estaOcupado) {
                        $llaveCache = "lock_turno_{$profesional->id}_{$fechaString}_{$horaBloqueClean}:00";
                        if (\Illuminate\Support\Facades\Cache::has($llaveCache)) {
                            $estaOcupado = true;
                        }
                    }

                    // Re-mapeamos el bloque con su estructura final para Alpine
                    $bloque = [
                        'hora' => $horaBloqueClean,
                        'ocupado' => $estaOcupado
                    ];
                }
                unset($bloque); 
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

    /**
     * Fragmentador matemático que ahora cruza con las reservas reales de la BD
     */
    private function calcularBloquesHorarios($inicio, $fin, $duracion, $buffer, $reservasDelDia = [])
    {
        $bloques = [];
        $saltoTotal = $duracion + $buffer;

        while ($inicio->copy()->addMinutes($duracion)->lessThanOrEqualTo($fin)) {
            $horaBloque = $inicio->format('H:i');
            
            //Si la hora del bloque ya existe en el array de reservas, se marca OCUPADO
            $estaOcupado = isset($reservasDelDia[$horaBloque]);

            $bloques[] = [
                'hora' => $horaBloque,
                'ocupado' => $estaOcupado, // Ahora lee la realidad de reservas
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
            
            foreach ($reglas as $regla) {
                // Nos aseguramos de leer bien si viene en true o false
                $activo = filter_var($regla['activo'] ?? false, FILTER_VALIDATE_BOOLEAN);

                if ($activo && !empty($regla['hora_inicio']) && !empty($regla['hora_fin'])) {
                    // SI ESTÁ MARCADO: updateOrCreate busca por dia_semana. 
                    // Si ya existía, actualiza las horas. Si no existía, lo crea nuevo.
                    $profesional->reglasDisponibilidad()->updateOrCreate(
                        ['dia_semana' => $regla['dia_semana']],
                        [
                            'hora_inicio'    => $regla['hora_inicio'],
                            'hora_fin'       => $regla['hora_fin'],
                            'duracion_turno' => $regla['duracion_turno'] ?? 60,
                            'buffer_tiempo'  => $regla['buffer_tiempo'] ?? 0,
                        ]
                    );
                } else {
                    //SI ESTÁ DESMARCADO (o le faltan datos): Lo borramos de la base de datos.
                    $profesional->reglasDisponibilidad()
                                ->where('dia_semana', $regla['dia_semana'])
                                ->delete();
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

    public function obtenerAgendaProfesional($profesionalId, ?string $fechaInicio = null)
    {
        $profesional = Profesional::findOrFail($profesionalId);
        
        return $this->obtenerAgendaSemana($profesional, $fechaInicio);
    }
}