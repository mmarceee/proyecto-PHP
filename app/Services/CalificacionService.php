<?php

namespace App\Services;

use App\Models\Reserva;
use App\Models\Calificacion;
use Illuminate\Support\Facades\DB;
use Exception;

class CalificacionService
{
    /**
     * Registra una calificación bidireccional para una reserva finalizada.
     *
     * @param Reserva $reserva
     * @param \App\Models\User $usuarioAutenticado
     * @param array $datos
     * @return Calificacion
     * @throws Exception
     */
    public function calificar(Reserva $reserva, $usuarioAutenticado, array $datos)
    {
        // Usamos strtolower para evitar choques entre 'finalizada', 'Finalizada' o 'FINALIZADA'
        $estado = $reserva->estado ?? $reserva->estado_reserva ?? '';

        // REGLA DE NEGOCIO 1: La reserva debe estar estrictamente en estado 'Finalizada'
        if (strtolower($estado) !== 'finalizada') {
            throw new Exception('Solo se pueden calificar reservas en estado Finalizada.');
        }

        // Aseguramos que las relaciones de los perfiles estén cargadas en memoria
        $reserva->loadMissing(['cliente', 'profesional']);

        // Extraemos los user_id físicos de las cuentas correspondientes
        $clienteUserId = $reserva->cliente->user_id;
        $profesionalUserId = $reserva->profesional->user_id;

        $tipoCalificacion = null;
        $evaluadoId = null;

        // Determinar quién evalúa a quién basándonos en el ID del Usuario logueado
        if ($clienteUserId === $usuarioAutenticado->id) {
            // El evaluador es el Cliente, el evaluado es el Profesional
            $tipoCalificacion = 'ClienteAProfesional';
            $evaluadoId = $profesionalUserId; 
            
        } elseif ($profesionalUserId === $usuarioAutenticado->id) {
            // El evaluador es el Profesional, el evaluado es el Cliente
            $tipoCalificacion = 'ProfesionalACliente';
            $evaluadoId = $clienteUserId; 
        } else {
            // Si el ID no coincide con ninguno, significa que no participó de la reserva (ej: Administrador u otro usuario)
            throw new Exception('No tienes permiso para calificar esta reserva porque no participaste en ella.');
        }

        // REGLA DE NEGOCIO 2: Límite de calificaciones (Un usuario solo puede calificar una vez esta reserva)
        $yaCalifico = Calificacion::where('reserva_id', $reserva->id)
            ->where('evaluador_id', $usuarioAutenticado->id)
            ->exists();

        if ($yaCalifico) {
            throw new Exception('Ya has enviado una calificación para esta reserva.');
        }

        // Guardamos la calificación utilizando transacciones de base de datos para garantizar consistencia e integridad
        return DB::transaction(function () use ($reserva, $usuarioAutenticado, $evaluadoId, $tipoCalificacion, $datos) {
            $calificacion = Calificacion::create([
                'reserva_id'       => $reserva->id,
                'evaluador_id'     => $usuarioAutenticado->id,
                'evaluado_id'      => $evaluadoId,
                'tipoCalificacion' => $tipoCalificacion,
                'puntuacion'       => $datos['puntuacion'],
                'comentario'       => $datos['comentario'] ?? null,
                'fecha'            => now(), // Mapea con el cast 'datetime' de tu modelo
            ]);
            // Si el cliente califica al profesional, recalculamos su reputación promedio
            if ($tipoCalificacion === 'ClienteAProfesional') {
                $profesional = $reserva->profesional;
                if ($profesional) {
                    $promedio = Calificacion::where('evaluado_id', $evaluadoId)
                        ->where('tipoCalificacion', 'ClienteAProfesional')
                        ->avg('puntuacion');
                    $profesional->update([
                        'reputacion_promedio' => round($promedio, 2)
                    ]);
                }
            }
            return $calificacion;
        });
    }
}