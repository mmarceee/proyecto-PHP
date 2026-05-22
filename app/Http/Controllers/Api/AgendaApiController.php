<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ReservaService; //Inyectamos tu servicio de control
use App\Models\Servicio;
use Carbon\Carbon;

class AgendaApiController extends Controller
{
    /**
     * Obtiene la agenda semanal para el panel de configuración del profesional
     */
    public function obtenerAgenda(Request $request, \App\Services\AgendaService $agendaService)
    {
        // 1. Obtenemos el usuario logueado y verificamos que sea profesional
        $user = $request->user();
        if (!$user || !$user->profesional) {
            return response()->json(['error' => 'Acceso denegado. No eres un profesional.'], 403);
        }

        // 2. Leemos la fecha de la URL (si viene)
        $fechaInicio = $request->query('fecha');

        try {
            // 3. Llamamos al servicio que armamos con el cruce de datos inteligente
            $semana = $agendaService->obtenerAgendaSemana($user->profesional, $fechaInicio);

            return response()->json([
                'semana' => $semana
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cargar la agenda: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Procesa la reserva del paciente impidiendo superposiciones
     */
    public function agendarTurno(Request $request, ReservaService $reservaService)
    {
        $validated = $request->validate([
            'profesional_id' => ['required', 'exists:profesionales,id'],
            'servicio_id'    => ['required', 'exists:servicios,id'],
            'fecha'          => ['required', 'date', 'after_or_equal:today'],
            'hora_inicio'    => ['required', 'date_format:H:i'],
        ]);

        // 1. Obtener el servicio para saber cuántos minutos dura
        $servicio = Servicio::findOrFail($validated['servicio_id']);
        
        // 2. Calcular la hora_fin sumando la duración
        $horaInicio = Carbon::parse($validated['hora_inicio']);
        $horaFin = $horaInicio->copy()->addMinutes($servicio->duracion)->format('H:i');

        // 3. Obtener el ID de cliente del usuario logueado
        $clienteId = $request->user()->cliente->id;

        try {
            // 4. Delegamos la creación al ReservaService, el cual correrá 
            // la validación estricta de "verificarChoqueHorario" antes de insertar
            $reserva = $reservaService->crear([
                'cliente_id'     => $clienteId,
                'profesional_id' => $validated['profesional_id'],
                'servicio_id'    => $validated['servicio_id'],
                'fecha'          => $validated['fecha'],
                'hora_inicio'    => $validated['hora_inicio'],
                'hora_fin'       => $horaFin,
                'estado_reserva' => 'pendiente', // Nace en tu enum inicial
            ]);

            return response()->json([
                'message' => '¡Tu turno ha sido reservado con éxito!',
                'reserva' => $reserva
            ], 201);

        } catch (\Exception $e) {
            // Si el profesional se ocupó justo antes, frena el insert y devuelve un 422
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}