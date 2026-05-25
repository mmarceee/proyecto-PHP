<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Services\CalificacionService;

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
            // Buscamos la reserva
            $reserva = Reserva::with('profesional')->findOrFail($reservaId);
            $usuario = $request->user(); // El usuario logueado (Cliente o Profesional)

            $calificacion = $this->calificacionService->calificar($reserva, $usuario, $validated);

            return response()->json([
                'message' => '¡Calificación enviada con éxito!',
                'calificacion' => $calificacion
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }
}