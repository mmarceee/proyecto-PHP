<?php

namespace App\Observers;

use App\Models\Reserva;
use App\Models\CompraPaquete;
use App\Models\UsoSesionPaquete;

class ReservaObserver
{
    /**
     * EVENTO: Se ejecuta automáticamente al CREAR una reserva
     * (Conserva tu lógica intacta, asegurando el descuento automático)
     */
    public function created(Reserva $reserva): void
    {
        // Buscamos un paquete activo del cliente que coincida con el servicio solicitado
        $paquete = CompraPaquete::where('cliente_id', $reserva->cliente_id)
            ->where('estado_paquete', 'activo')
            ->where('sesiones_disponibles', '>', 0)
            ->first();

        if ($paquete) {
            // Creamos el eslabón de uso de sesión
            UsoSesionPaquete::create([
                'compra_paquete_id' => $paquete->id,
                'reserva_id'        => $reserva->id,
                'fechaUso'          => now(),
            ]);

            // Descontamos la sesión del acumulador
            $paquete->sesiones_disponibles -= 1;
            $paquete->sesiones_consumidas += 1;

            // Si se quedó sin nafta, lo marcamos como consumido
            if ($paquete->sesiones_disponibles === 0) {
                $paquete->estado_paquete = 'consumido';
            }

            $paquete->save();
        }
    }

    /**
     * EVENTO: Se ejecuta automáticamente al ACTUALIZAR una reserva
     * (Acá acoplamos la reacción automática ante cancelaciones)
     */
    public function updated(Reserva $reserva): void
    {
        // Investigamos si el estado cambió estrictamente en esta petición y ahora es 'cancelada'
        if ($reserva->wasChanged('estado_reserva') && $reserva->estado_reserva === 'cancelada') {
            
            // Accedemos a la relación nativa que tenés en tu modelo Reserva
            $usoSesion = $reserva->uso_sesion_paquete;

            if ($usoSesion) {
                $paquete = $usoSesion->compra_paquete;

                if ($paquete) {
                    // Devolvemos la sesión al cliente de inmediato
                    $paquete->sesiones_disponibles += 1;
                    $paquete->sesiones_consumidas -= 1;

                    // Si figuraba como consumido/agotado, vuelve a la vida ("activo")
                    if ($paquete->estado_paquete === 'consumido') {
                        $paquete->estado_paquete = 'activo';
                    }

                    $paquete->save();
                }

                // Borramos el registro de vinculación para limpiar el historial de consumo
                $usoSesion->delete();
            }
        }
    }

    /**
     * Resto de eventos del ciclo de vida (quedan limpios por si a futuro añaden más lógica)
     */
    public function deleted(Reserva $reserva): void {}
    public function restored(Reserva $reserva): void {}
    public function forceDeleted(Reserva $reserva): void {}
}