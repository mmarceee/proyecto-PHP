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

        if (!$user || !$user->profesional || $user->profesional->estado !== 'aprobado') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        //Capturamos la fecha de inicio opcional de la URL (ej: ?fecha=2026-05-25)
        $fechaInicio = $request->query('fecha');

        $semana = $this->agendaService->obtenerAgendaSemana($user->profesional, $fechaInicio);

        $reglasActuales = $user->profesional->reglasDisponibilidad->map(function($r) {
            return [
                'dia_semana' => $r->dia_semana,
                'hora_inicio' => substr($r->hora_inicio, 0, 5),
                'hora_fin' => substr($r->hora_fin, 0, 5),
                'duracion_turno' => $r->duracion_turno,
                'buffer_tiempo' => $r->buffer_tiempo,
            ];
        });

        return response()->json([
            'semana' => $semana,
            'reglas_actuales' => $reglasActuales
        ]);
    }

    public function guardarReglas(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->profesional || $user->profesional->estado !== 'aprobado') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'reglas'                  => ['required', 'array'],
            'reglas.*.dia_semana'     => ['required', 'integer', 'between:0,6'],
            'reglas.*.activo'         => ['required', 'boolean'],
            'reglas.*.hora_inicio'    => ['required_if:reglas.*.activo,true', 'nullable', 'date_format:H:i'],
            'reglas.*.hora_fin'       => ['required_if:reglas.*.activo,true', 'nullable', 'date_format:H:i', 'after:reglas.*.hora_inicio'],
            'reglas.*.duracion_turno' => ['required_if:reglas.*.activo,true', 'integer'],
            'reglas.*.buffer_tiempo'  => ['required_if:reglas.*.activo,true', 'integer'],
        ]);

        $this->agendaService->guardarReglasBase($user->profesional, $request->input('reglas'));

        return response()->json(['message' => 'Configuración guardada correctamente.']);
    }

    public function guardarExcepcion(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->profesional || $user->profesional->estado !== 'aprobado') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Validamos que la fecha sea válida y corresponda a las reglas del enum que tienen en la migración
        $validated = $request->validate([
            'fecha'  => ['required', 'date'],
            'tipo'   => ['required', 'in:no_disponible,licencia,feriado'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        $this->agendaService->guardarExcepcion($user->profesional, $validated);

        return response()->json([
            'message' => 'El día ha sido bloqueado correctamente.'
        ]);
    }

    /**
     * Eliminar bloqueo/excepción de una fecha
     */
    public function desbloquearDia(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->profesional || $user->profesional->estado !== 'aprobado') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'fecha' => ['required', 'date'],
        ]);

        $this->agendaService->eliminarExcepcion($user->profesional, $request->input('fecha'));

        return response()->json([
            'message' => 'El día ha sido desbloqueado. Se restauraron los horarios base.'
        ]);
    }
}