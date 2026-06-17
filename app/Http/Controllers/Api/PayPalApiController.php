<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PagoService;
use App\Services\ReservaService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PayPalApiController extends Controller
{
    public function __construct(private PagoService $pagoService) {}

    public function createPaymentReserva(Request $request, ReservaService $reservaService)
    {
        $validated = $request->validate([
            'profesional_id'    => ['required', 'exists:profesionales,id'],
            'servicio_id'       => ['required', 'exists:servicios,id'],
            'fecha'             => ['required', 'date', 'after_or_equal:today'],
            'hora_inicio'       => ['required', 'date_format:H:i'],
        ]);

        $servicio = \App\Models\Servicio::findOrFail($validated['servicio_id']);
        $horaFin = \Carbon\Carbon::parse($validated['hora_inicio'])->addMinutes($servicio->duracion)->format('H:i');
        
        $clienteId = $request->user()->cliente->id;

        try {
            // Bloqueamos en caché para que nadie tome el lugar mientras este cliente paga
            $reservaService->bloquearTurnoTemporal(
                $validated['profesional_id'],
                $validated['fecha'],
                $validated['hora_inicio'],
                $clienteId
            );

            $intentId = Str::uuid()->toString();
            $payload = [
                'tipo' => 'reserva',
                'monto' => $servicio->precio,
                'intentId' => $intentId,
                'datos' => [
                    'cliente_id'     => $clienteId,
                    'profesional_id' => $validated['profesional_id'],
                    'servicio_id'    => $validated['servicio_id'],
                    'fecha'          => $validated['fecha'],
                    'hora_inicio'    => $validated['hora_inicio'],
                    'hora_fin'       => $horaFin,
                ]
            ];

            // Guardamos el payload por 15 minutos
            Cache::put("paypal_intent_{$intentId}", $payload, now()->addMinutes(15));

            $orden = $this->pagoService->crearOrden($servicio->precio, $intentId);

            return response()->json([
                'id' => $orden['order_id'],
                'approval_url' => $orden['approval_url']
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function createPaymentPaquete(Request $request)
    {
        $validated = $request->validate([
            'paquete_id' => ['required', 'exists:paquetes_servicios,id'],
        ]);

        $paquete = \App\Models\PaqueteServicio::findOrFail($validated['paquete_id']);
        $clienteId = $request->user()->cliente->id;

        try {
            $intentId = Str::uuid()->toString();
            $payload = [
                'tipo' => 'paquete',
                'monto' => $paquete->precio,
                'intentId' => $intentId,
                'datos' => [
                    'cliente_id' => $clienteId,
                    'paquete_id' => $paquete->id,
                ]
            ];

            Cache::put("paypal_intent_{$intentId}", $payload, now()->addMinutes(15));

            $orden = $this->pagoService->crearOrden($paquete->precio, $intentId);

            return response()->json([
                'id' => $orden['order_id'],
                'approval_url' => $orden['approval_url']
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function successPayment(Request $request)
    {
        $token = $request->query('token');
        $intentId = $request->query('intent');

        if (!$token || !$intentId) {
            return redirect()->away(config('app.url') . '/dashboard?pago=error&msg=missing_token');
        }

        $payload = Cache::get("paypal_intent_{$intentId}");

        if (!$payload) {
            return redirect()->away(config('app.url') . '/dashboard?pago=error&msg=timeout');
        }
        
        $lock = Cache::lock("paypal_lock_{$intentId}", 30);

        try {
            if (!$lock->get()) {
                return redirect()->away(config('app.url') . '/dashboard?pago=exito');
            }

            $this->pagoService->capturarPago($token, $payload);
            Cache::forget("paypal_intent_{$intentId}");

            return redirect()->away(config('app.url') . '/dashboard?pago=exito');

        } catch (\Exception $e) {
            Log::error('Error en callback PayPal: ' . $e->getMessage());
            return redirect()->away(config('app.url') . '/dashboard?pago=error');
        } finally {
            $lock->forceRelease();
        }
    }

    public function cancelPayment(Request $request)
    {
        $intentId = $request->query('intent');
        if ($intentId) {
            Cache::forget("paypal_intent_{$intentId}");
        }
        return redirect()->away(config('app.url') . '/dashboard?pago=cancelado');
    }
}