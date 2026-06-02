<?php

namespace App\Jobs;

use App\Models\Reserva;
use App\Mail\SolicitarResenaMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EnviarSolicitudResenaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reserva;

    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }

    public function handle(): void
    {
        $this->reserva->loadMissing(['cliente.user', 'profesional.user', 'servicio']);

        $emailCliente = $this->reserva->cliente->user->email ?? null;

        if ($emailCliente) {
            Mail::to($emailCliente)->send(new SolicitarResenaMail($this->reserva));
        }
    }
}