<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profesional;
use App\Services\ProfesionalService;
use Illuminate\Http\Request;

class ProfesionalAdminApiController extends Controller
{
    protected $profesionalService;

    public function __construct(ProfesionalService $profesionalService)
    {
        $this->profesionalService = $profesionalService;
    }

    public function aprobar(Request $request, Profesional $profesional)
    {
        $user = $request->user();

        if (!$user || !$user->esAdmin()) {
            return response()->json([
                'message' => 'No autorizado',
            ], 403);
        }

        //LLamo al servicio
        $this->profesionalService->aprobar($profesional->id);

        return response()->json([
            'message' => 'Profesional aprobado correctamente',
            'profesional' => $profesional,
        ]);
    }

    public function rechazar(Request $request, Profesional $profesional)
    {
        $user = $request->user();

        if (!$user || !$user->esAdmin()) {
            return response()->json([
                'message' => 'No autorizado',
            ], 403);
        }

        //LLamo al servicio para cambiar el estado a 'rechazado' sin borrar
        $this->profesionalService->rechazar($profesional->id);

        return response()->json([
            'message' => 'Solicitud profesional rechazada',
            'profesional' => $profesional,
        ]);
    }
}