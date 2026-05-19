<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profesional;
use Illuminate\Http\Request;

class ProfesionalAdminApiController extends Controller
{
    public function aprobar(Request $request, Profesional $profesional)
    {
        $user = $request->user();

        if (!$user || !$user->esAdmin()) {
            return response()->json([
                'message' => 'No autorizado',
            ], 403);
        }

        $profesional->update([
            'estado' => 'aprobado',
        ]);

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

        $profesional->update([
            'estado' => 'rechazado',
        ]);

        return response()->json([
            'message' => 'Solicitud profesional rechazada',
            'profesional' => $profesional,
        ]);
    }
}