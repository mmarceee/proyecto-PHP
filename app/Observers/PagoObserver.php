<?php

namespace App\Observers;

use App\Models\Pago;
use Illuminate\Support\Facades\Log;
use App\Services\EventLogService;

class PagoObserver
{
    /**
     * Escucha si el pago se crea directamente ya aprobado/confirmado.
     */
    public function created(Pago $pago): void
    {
        if (in_array($pago->estado_pago, ['confirmado', 'aprobado'])) {
            $this->registrarAuditoria($pago);
        }
    }

    /**
     * Escucha si un pago existente cambia su estado a aprobado/confirmado.
     */
    public function updated(Pago $pago): void
    {
        if ($pago->wasChanged('estado_pago') && in_array($pago->estado_pago, ['confirmado', 'aprobado'])) {
            $this->registrarAuditoria($pago);
        }
    }

    /**
     * Guarda el registro en la base de datos de MongoDB.
     */
    private function registrarAuditoria(Pago $pago): void
    {
        try {
            app(EventLogService::class)->log('pago_confirmado', [
                'pago_id'           => $pago->id,
                'reserva_id'        => $pago->reserva_id,
                'compra_paquete_id' => $pago->compra_paquete_id,
                'monto'             => $pago->monto,
                'metodo_pago'       => $pago->metodo_pago,
                'referencia'        => $pago->referencia_externa,
            ], $pago->reserva?->cliente?->user_id ?? $pago->compraPaquete?->cliente?->user_id);
        } catch (\Exception $e) {
            Log::error("Fallo al registrar auditoría NoSQL para pago: " . $e->getMessage());
        }
    }
}