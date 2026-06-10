<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PoliticaCancelacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PoliticaCancelacionApiController extends Controller
{
    public function show()
    {
        $profesional = Auth::user()->profesional;
        
        if (!$profesional) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $politica = PoliticaCancelacion::where('profesional_id', $profesional->id)->first();
        
        return response()->json($politica ?: [
            'tiempo_minimo_cancelacion' => 24,
            'permite_reprogramacion' => true,
            'descripcion' => ''
        ]);
    }

    public function store(Request $request)
    {
        $profesional = Auth::user()->profesional;

        if (!$profesional) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'tiempo_minimo_cancelacion' => 'required|integer|min:0',
            'permite_reprogramacion' => 'required|boolean',
            'descripcion' => 'nullable|string|max:500',
        ]);

        $politica = PoliticaCancelacion::updateOrCreate(
            ['profesional_id' => $profesional->id],
            $validated
        );

        return response()->json([
            'message' => 'Política guardada correctamente',
            'politica' => $politica
        ]);
    }
}