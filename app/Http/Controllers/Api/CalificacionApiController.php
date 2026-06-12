<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\Calificacion;
use App\Models\Profesional;
use App\Services\CalificacionService;
use Illuminate\Http\JsonResponse;

class CalificacionApiController extends Controller
{
    protected $calificacionService;

    public function __construct(CalificacionService $calificacionService)
    {
        $this->calificacionService = $calificacionService;
    }

    public function store(Request $request, $reservaId)
    {
        $validated = $request->validate([
            'puntuacion' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $reserva = Reserva::with('profesional')->findOrFail($reservaId);
            $usuario = $request->user();

            $calificacion = $this->calificacionService->calificar($reserva, $usuario, $validated);

            return response()->json([
                'message' => '¡Calificación enviada con éxito!',
                'calificacion' => $calificacion
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    /**
     * Listar todas las calificaciones (Para el Admin).
     */
    public function index(Request $request): JsonResponse
    {
        $calificaciones = Calificacion::with([
            'reserva.servicio',
            'evaluador',
            'evaluado'
        ])
        ->latest()
        ->get()
        ->map(function ($calificacion) {
            return [
                'id' => $calificacion->id,
                'reserva_id' => $calificacion->reserva_id,
                'evaluador_id' => $calificacion->evaluador_id,
                'evaluador_nombre' => trim(($calificacion->evaluador?->name ?? '') . ' ' . ($calificacion->evaluador?->apellido ?? '')) ?: 'Usuario',
                'evaluador_email' => $calificacion->evaluador?->email,
                'evaluado_id' => $calificacion->evaluado_id,
                'evaluado_nombre' => trim(($calificacion->evaluado?->name ?? '') . ' ' . ($calificacion->evaluado?->apellido ?? '')) ?: 'Usuario',
                'evaluado_email' => $calificacion->evaluado?->email,
                'tipo_calificacion' => $calificacion->tipoCalificacion,
                'puntuacion' => $calificacion->puntuacion,
                'comentario' => $calificacion->comentario,
                'fecha' => $calificacion->fecha ? $calificacion->fecha->format('d/m/Y H:i') : null,
                'servicio_nombre' => $calificacion->reserva?->servicio?->nombre ?? 'Servicio',
            ];
        });

        return response()->json([
            'calificaciones' => $calificaciones,
        ]);
    }

    /**
     * Eliminar una calificación y recalcular reputación promedio (Para el Admin).
     */
    public function destroy(Request $request, Calificacion $calificacion): JsonResponse
    {
        $tipoCalificacion = $calificacion->tipoCalificacion;
        $evaluadoId = $calificacion->evaluado_id;

        $calificacion->delete();

        if ($tipoCalificacion === 'ClienteAProfesional') {
            $profesional = Profesional::where('user_id', $evaluadoId)->first();
            if ($profesional) {
                $promedio = Calificacion::where('evaluado_id', $evaluadoId)
                    ->where('tipoCalificacion', 'ClienteAProfesional')
                    ->avg('puntuacion') ?? 0;

                $profesional->update([
                    'reputacion_promedio' => round($promedio, 2)
                ]);
            }
        }

        return response()->json([
            'message' => 'Reseña eliminada correctamente.'
        ]);
    }

     /**
     * Obtener las calificaciones de un profesional específico (Para Clientes y Profesionales).
     */
    public function obtenerCalificacionesProfesional($id): JsonResponse
    {
        // $id es el ID del profesional en la tabla 'profesionales'
        $profesional = Profesional::findOrFail($id);
        $userId = $profesional->user_id;
        $calificaciones = Calificacion::where('evaluado_id', $userId)
            ->where('tipoCalificacion', 'ClienteAProfesional')
            ->with('evaluador')
            ->latest()
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'puntuacion' => $c->puntuacion,
                    'comentario' => $c->comentario,
                    'fecha' => $c->fecha ? $c->fecha->format('d/m/Y') : null,
                    'cliente_nombre' => trim(($c->evaluador?->name ?? '') . ' ' . ($c->evaluador?->apellido ?? '')) ?: 'Cliente',
                ];
            });
        return response()->json([
            'calificaciones' => $calificaciones
        ]);
    }

}