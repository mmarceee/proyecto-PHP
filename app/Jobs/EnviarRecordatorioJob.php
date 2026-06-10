<?php

namespace App\Jobs;

use App\Models\Reserva;
use App\Mail\RecordatorioReservaMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EnviarRecordatorioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reserva;

    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }

    public function handle(): void
    {
        // Cargamos las relaciones por las dudas
        $this->reserva->loadMissing(['cliente.user', 'profesional.user', 'servicio']);

        $emailCliente = $this->reserva->cliente->user->email ?? null;

        if ($emailCliente) {
            Mail::to($emailCliente)->send(new RecordatorioReservaMail($this->reserva));
        }
    }
}