<?php

namespace App\Services;

use App\Models\Reserva;
use App\Models\Pago;
use App\Events\EstadoReservaCambiado; // IMPORTANTE: Agregado para el WebSocket
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PagoService
{
    protected PayPalClient $provider;

    public function __construct()
    {
        $this->provider = new PayPalClient;
        $this->provider->setApiCredentials(config('paypal'));
        try {
            $this->provider->getAccessToken();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('PayPal: fallo al obtener access token', [
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('No se pudo conectar con PayPal. Intente nuevamente más tarde.');
        }
    }

    public function crearOrden(float $montoUyu, string $intentId)
    {
        $tasaCambio = config('paypal.uyu_to_usd_rate', 40);
        $montoUsd = round($montoUyu / $tasaCambio, 2);

        $data = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "reference_id" => 'intent_' . $intentId,
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => number_format($montoUsd, 2, '.', '')
                    ]
                ]
            ],
            "application_context" => [
                "cancel_url" => url('/api/paypal/cancel?intent=' . $intentId),
                "return_url" => url('/api/paypal/success?intent=' . $intentId)
            ]
        ];

        $order = $this->provider->createOrder($data);

        if (isset($order['id']) && $order['status'] !== 'FAILED') {
            $approvalUrl = collect($order['links'])->firstWhere('rel', 'approve');
            return [
                'order_id' => $order['id'],
                'approval_url' => $approvalUrl['href'] ?? null
            ];
        }

        throw new Exception('Fallo en la comunicación con PayPal al crear la orden.');
    }

    public function capturarPago(string $token, array $payload)
    {
        $response = $this->provider->capturePaymentOrder($token);
        if (isset($response['status']) && $response['status'] === 'COMPLETED') {
            // --- VALIDACIÓN DE MONTO ---
            $tasaCambio = config('paypal.uyu_to_usd_rate', 40);
            $montoEsperadoUsd = round($payload['monto'] / $tasaCambio, 2);
            $montoCobradoUsd  = (float) ($response['purchase_units'][0]['payments']['captures'][0]['amount']['value']
                ?? $response['purchase_units'][0]['amount']['value']
                ?? 0);
            if (abs($montoCobradoUsd - $montoEsperadoUsd) > 0.01) {
                Log::error('PayPal: monto cobrado no coincide', [
                    'esperado_usd' => $montoEsperadoUsd,
                    'cobrado_usd'  => $montoCobradoUsd,
                ]);
                throw new Exception('El monto cobrado por PayPal no coincide con el monto de la orden.');
            }
            // --- VALIDACIÓN DE reference_id ---
            $referenceIdPayPal   = $response['purchase_units'][0]['reference_id'] ?? null;
            $referenceIdEsperado = 'intent_' . ($payload['intentId'] ?? '');
            if ($referenceIdPayPal !== $referenceIdEsperado) {
                Log::error('PayPal: reference_id no coincide', [
                    'esperado' => $referenceIdEsperado,
                    'recibido' => $referenceIdPayPal,
                ]);
                throw new Exception('El reference_id de PayPal no coincide con la orden esperada.');
            }    
            // --- Extraer el ID de Captura real en lugar del ID de Orden ---
            $captureId = $response['purchase_units'][0]['payments']['captures'][0]['id'] ?? $response['id'];

            return DB::transaction(function () use ($response, $payload, $captureId) {
                
                if ($payload['tipo'] === 'reserva') {
                    $reservaService = app(ReservaService::class);
                    $payload['datos']['estado_reserva'] = 'pendiente';
                    
                    $reserva = $reservaService->crear($payload['datos']);

                    Pago::create([
                        'reserva_id' => $reserva->id,
                        'compra_paquete_id' => null,
                        'monto' => $payload['monto'],
                        'estado_pago' => 'aprobado',
                        'metodo_pago' => 'paypal',
                        'referencia_externa' => $captureId // Guardamos el Capture ID correcto
                    ]);

                    \Illuminate\Support\Facades\DB::afterCommit(function () use ($reserva) {
                        broadcast(new \App\Events\EstadoReservaCambiado($reserva->cliente->user_id, $reserva->id, 'pendiente'));
                    });

                    return $reserva;

                } elseif ($payload['tipo'] === 'paquete') {
                    $paqueteService = app(PaqueteService::class);
                    $cliente = \App\Models\Cliente::find($payload['datos']['cliente_id']);
                    $paqueteServicio = \App\Models\PaqueteServicio::find($payload['datos']['paquete_id']);
                    
                    $compra = $paqueteService->comprarPaquete($cliente, $paqueteServicio);

                    Pago::create([
                        'reserva_id' => null,
                        'compra_paquete_id' => $compra->id,
                        'monto' => $payload['monto'],
                        'estado_pago' => 'aprobado',
                        'metodo_pago' => 'paypal',
                        'referencia_externa' => $captureId // Guardamos el Capture ID correcto
                    ]);

                    return $compra;
                }
            });
        }

        throw new Exception('Transacción rechazada por PayPal.');
    }

    public function reembolsarPago(Pago $pago, string $motivo = 'Reembolso por cancelación')
    {
        if ($pago->metodo_pago !== 'paypal' || !$pago->referencia_externa) {
            throw new Exception('El pago no se puede reembolsar de forma automática.');
        }

        // --- Convertir el monto de UYU a USD con la misma tasa usada al cobrar ---
        $tasaCambio = config('paypal.uyu_to_usd_rate', 40);
        $montoUsd = round($pago->monto / $tasaCambio, 2);

        $response = $this->provider->refundCapturedPayment(
            $pago->referencia_externa, 
            "ref_" . $pago->id, 
            $montoUsd, // Pasamos el monto en USD convertido
            $motivo
        );

        if (isset($response['status']) && $response['status'] === 'COMPLETED') {
            $pago->update(['estado_pago' => 'reembolso']);
            return true;
        }

        \Illuminate\Support\Facades\Log::error("Fallo al reembolsar en PayPal: " . json_encode($response));
        throw new Exception('No se pudo procesar el reembolso en PayPal.');
    }
}