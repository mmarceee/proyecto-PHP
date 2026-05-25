<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProfesionalService;

class ProfesionalApiController extends Controller
{
    protected $profesionalService;

    public function __construct(ProfesionalService $profesionalService)
    {
        $this->profesionalService = $profesionalService;
    }

    public function postularse(Request $request)
    {
        // 1. Validamos los datos de entrada
        $validated = $request->validate([
            'especialidad'     => ['required', 'string', 'max:255'],
            'descripcion'      => ['required', 'string', 'max:1000'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            // 2. Usamos el Service para crear el registro
            $this->profesionalService->crearPostulacion($request->user(), $validated);

            return response()->json([
                'message' => 'Postulación creada con éxito'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}