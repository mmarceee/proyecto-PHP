<?php

namespace App\Observers;

use App\Models\CompraPaquete;
use App\Models\Reserva;
use App\Models\UsoSesionPaquete;

class ReservaObserver
{
    /**
     * Handle the Reserva "created" event.
     */
    public function created(Reserva $reserva): void
    {
        $paquete = CompraPaquete::where('cliente_id', $reserva->cliente_id)
            ->where('estado_paquete', 'activo')
            ->where('sesiones_disponibles', '>', 0)
            ->first();

        if($paquete) {

            UsoSesionPaquete::create([
                'compra_paquete_id' => $paquete->id,
                'reserva_id' => $reserva->id,
                'fechaUso' => now(),
            ]);

            $paquete->sesiones_disponibles -= 1;
            $paquete->sesiones_consumidas += 1;

            if($paquete->sesiones_disponibles === 0) {
                $paquete->estado_paquete = 'consumido';
            }

            $paquete->save();
        }
    }

    /**
     * Handle the Reserva "updated" event.
     */
    public function updated(Reserva $reserva): void
    {
        //
    }

    /**
     * Handle the Reserva "deleted" event.
     */
    public function deleted(Reserva $reserva): void
    {
        //
    }

    /**
     * Handle the Reserva "restored" event.
     */
    public function restored(Reserva $reserva): void
    {
        //
    }

    /**
     * Handle the Reserva "force deleted" event.
     */
    public function forceDeleted(Reserva $reserva): void
    {
        //
    }
}
