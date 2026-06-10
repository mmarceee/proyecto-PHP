<?php

namespace App\Observers;

use App\Models\Reserva;

class ReservaObserver
{
    public function created(Reserva $reserva): void
    {
        // VACÍO: La lógica de descuento de paquetes la maneja explícitamente el ReservaService
    }

    public function updated(Reserva $reserva): void
    {
        // VACÍO: La lógica de reintegro por cancelación la maneja explícitamente el ReservaService
    }

    public function deleted(Reserva $reserva): void {}
    public function restored(Reserva $reserva): void {}
    public function forceDeleted(Reserva $reserva): void {}
}