<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AgendaService;

class AgendaApiController extends Controller
{
    protected $agendaService;

    public function __construct(AgendaService $agendaService)
    {
        $this->agendaService = $agendaService;
    }

    public function obtenerAgenda(Request $request)
    {
        $user = $request->user();

        // Validamos que sea un profesional activo
        if (!$user || !$user->profesional || $user->profesional->estado !== 'aprobado') {
            return response()->json(['error' => 'Acceso denegado.'], 403);
        }

        $semana = $this->agendaService->obtenerAgendaSemana($user->profesional);

        return response()->json([
            'semana' => $semana
        ]);
    }
}