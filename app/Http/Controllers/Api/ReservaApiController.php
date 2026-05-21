<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Services\ReservaService;

class ReservaApiController extends Controller
{
    protected $reservaService;

    public function __construct(ReservaService $reservaService)
    {
        $this->reservaService = $reservaService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id'     => ['required', 'exists:clientes,id'],
            'profesional_id' => ['required', 'exists:profesionales,id'],
            'servicio_id'    => ['required', 'exists:servicios,id'],
            'fecha'          => ['required', 'date', 'after_or_equal:today'],
            'hora_inicio'    => ['required', 'date_format:H:i'],
            'hora_fin'       => ['required', 'date_format:H:i', 'after:hora_inicio'],
        ]);

        try {
            $reserva = $this->reservaService->crear($validated);
            return response()->json([
                'message' => 'Reserva creada exitosamente.',
                'reserva' => $reserva
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, $id)
    {
        $reserva = Reserva::findOrFail($id);

        $validated = $request->validate([
            'fecha'       => ['required', 'date', 'after_or_equal:today'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin'    => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'servicio_id' => ['required', 'exists:servicios,id'],
        ]);

        try {
            $reservaActualizada = $this->reservaService->actualizar($reserva, $validated);
            return response()->json([
                'message' => 'Reserva reprogramada exitosamente.',
                'reserva' => $reservaActualizada
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy(Request $request, $id)
    {
        $reserva = Reserva::findOrFail($id);

        $request->validate([
            'motivo_cancelacion' => ['required', 'string', 'max:255'],
        ]);

        $this->reservaService->cancelar($reserva, $request->motivo_cancelacion);

        return response()->json([
            'message' => 'Reserva cancelada correctamente.'
        ]);
    }

    /**
     * Endpoint para avanzar el ciclo de vida de una reserva desde el dashboard
     */
    public function avanzarEstado($id)
    {
        $reserva = Reserva::findOrFail($id);

        try {
            $reservaActualizada = $this->reservaService->avanzarEstado($reserva);
            
            return response()->json([
                'message' => 'Estado de la reserva actualizado.',
                'reserva' => $reservaActualizada
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}