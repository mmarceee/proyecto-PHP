<?php

namespace App\Jobs;

use App\Models\Reserva;
use App\Mail\PagoConfirmadoMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotificarPagoConfirmadoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reserva;

    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }

    public function handle(): void
    {
        // Cargamos las relaciones por si no vinieron cargadas desde el controlador
        $this->reserva->loadMissing(['cliente.user', 'servicio']);

        $emailCliente = $this->reserva->cliente->user->email ?? null;

        // Si el cliente tiene un email válido, disparamos el correo
        if ($emailCliente) {
            Mail::to($emailCliente)->send(new PagoConfirmadoMail($this->reserva));
        }
    }
}