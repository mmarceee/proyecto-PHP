<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profesional;
use App\Services\AgendaService;
use App\Services\BusquedaService;

class BusquedaApiController extends Controller
{
    /**
     * Búsqueda asincrónica de profesionales y sus servicios
     */
    public function buscar(Request $request, BusquedaService $busquedaService)
    {
        $profesionales = $busquedaService->buscarProfesionales(
            $request->query('q'),
            $request->query('categoria')
        );

        return response()->json(['profesionales' => $profesionales]);
    }

    /**
     * Obtener la agenda de un profesional específico
     */
    public function obtenerAgendaProfesional(Request $request, $id, AgendaService $agendaService)
    {
        $profesional = Profesional::findOrFail($id);
        $fechaInicio = $request->query('fecha');

        $semana = $agendaService->obtenerAgendaSemana($profesional, $fechaInicio);

        return response()->json(['semana' => $semana]);
    }

    public function categorias(BusquedaService $busquedaService)
    {
        return response()->json([
            'categorias' => $busquedaService->obtenerCategoriasServicios(),
        ]);
    }
}