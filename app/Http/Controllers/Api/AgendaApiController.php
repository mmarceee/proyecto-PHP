<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ReservaService; //Inyectamos tu servicio de control
use App\Models\Servicio;
use App\Events\AgendaActualizada;
use Carbon\Carbon;

class AgendaApiController extends Controller
{
    /**
     * Obtiene la agenda semanal para el panel de configuración del profesional
     */
    public function obtenerAgenda(Request $request, \App\Services\AgendaService $agendaService)
    {
        //Obtenemos el usuario logueado y verificamos que sea profesional
        $user = $request->user();
        if (!$user || !$user->profesional) {
            return response()->json(['error' => 'Acceso denegado. No eres un profesional.'], 403);
        }

        // Leemos la fecha de la URL (si viene)
        $fechaInicio = $request->query('fecha');

        try {
            //Llamamos al servicio que armamos con el cruce de datos inteligente
            $semana = $agendaService->obtenerAgendaSemana($user->profesional, $fechaInicio);

            return response()->json([
                'semana' => $semana,
                'reglas_actuales' => $user->profesional->reglasDisponibilidad
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
            'profesional_id'    => ['required', 'exists:profesionales,id'],
            'servicio_id'       => ['required', 'exists:servicios,id'],
            'fecha'             => ['required', 'date', 'after_or_equal:today'],
            'hora_inicio'       => ['required', 'date_format:H:i'],
            'compra_paquete_id' => ['nullable', 'exists:compra_paquetes,id'],
        ]);


        $clienteId = $request->user()->cliente->id;

        try {
            $reserva = $reservaService->crear([
                'cliente_id'        => $clienteId,
                'profesional_id'    => $validated['profesional_id'],
                'servicio_id'       => $validated['servicio_id'],
                'fecha'             => $validated['fecha'],
                'hora_inicio'       => $validated['hora_inicio'],
                'estado_reserva'    => 'pendiente',
                'compra_paquete_id' => $validated['compra_paquete_id'] ?? null,
            ]);

            return response()->json([
                'message' => 'Tu turno ha sido reservado con éxito',
                'reserva' => $reserva
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

        public function bloquearTurno(Request $request, ReservaService $reservaService)
    {
        $validated = $request->validate([
            'profesional_id' => ['required', 'exists:profesionales,id'],
            'fecha'          => ['required', 'date', 'after_or_equal:today'],
            'hora_inicio'    => ['required', 'date_format:H:i'],
        ]);

        $clienteId = $request->user()->cliente->id;

        try {
            // Delegación absoluta al servicio. Él se encarga de la caché y de notificar (broadcast).
            $reservaService->bloquearTurnoTemporal(
                $validated['profesional_id'],
                $validated['fecha'],
                $validated['hora_inicio'],
                $clienteId
            );

            return response()->json([
                'message' => 'Turno bloqueado temporalmente. Tienes 10 minutos para completar el pago.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Endpoint para guardar las reglas de disponibilidad desde el panel del profesional
     */
    public function guardarReglas(Request $request, \App\Services\AgendaService $agendaService)
    {
        $user = $request->user();
        if (!$user || !$user->profesional) {
            return response()->json(['error' => 'Acceso denegado. No eres un profesional.'], 403);
        }

        // Ahora validamos estrictamente el boolean "activo"
        $validated = $request->validate([
            'reglas'                 => ['required', 'array'],
            'reglas.*.dia_semana'    => ['required', 'integer', 'between:0,6'],
            'reglas.*.activo'        => ['required', 'boolean'],
            'reglas.*.hora_inicio'   => ['nullable', 'date_format:H:i'],
            'reglas.*.hora_fin'      => ['nullable', 'date_format:H:i'],
            'reglas.*.duracion_turno'=> ['nullable', 'integer', 'min:1'],
            'reglas.*.buffer_tiempo' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $agendaService->guardarReglasBase($user->profesional, $validated['reglas']);

            // Limpiamos la caché del modelo en memoria
            $user->profesional->unsetRelation('reglasDisponibilidad');

            return response()->json([
                'message' => 'Configuración de agenda guardada con éxito.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function guardarExcepcion(Request $request, \App\Services\AgendaService $agendaService)
    {
        $user = $request->user();

        if (!$user || !$user->profesional) {
            return response()->json(['error' => 'Acceso denegado. No eres un profesional.'], 403);
        }

        $validated = $request->validate([
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'tipo' => ['required', 'in:feriado,licencia,no_disponible'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $excepcion = $agendaService->guardarExcepcion($user->profesional, $validated);

            broadcast(new AgendaActualizada($user->profesional->id, []));

            return response()->json([
                'message' => 'Día bloqueado correctamente.',
                'excepcion' => $excepcion,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al bloquear el día: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function desbloquearDia(Request $request, \App\Services\AgendaService $agendaService)
    {
        $user = $request->user();

        if (!$user || !$user->profesional) {
            return response()->json(['error' => 'Acceso denegado. No eres un profesional.'], 403);
        }

        $validated = $request->validate([
            'fecha' => ['required', 'date'],
        ]);

        try {
            $agendaService->eliminarExcepcion($user->profesional, $validated['fecha']);

            broadcast(new AgendaActualizada($user->profesional->id, []));

            return response()->json([
                'message' => 'Día desbloqueado correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al desbloquear el día: ' . $e->getMessage(),
            ], 500);
        }
    }
}