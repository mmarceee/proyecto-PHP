<?php

namespace App\Jobs;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservaStatusMail;

class EnviarNotificacionReserva implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reserva;
    public $estado;

    /**
     * Create a new job instance.
     */
    public function __construct(Reserva $reserva, string $estado)
    {
        $this->reserva = $reserva;
        $this->estado = $estado;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        
        // Cargamos las relaciones por las dudas para poder sacar los emails y nombres
        $this->reserva->loadMissing(['cliente.user', 'profesional.user']);

        $emailCliente = $this->reserva->cliente->user->email ?? 'desconocido@app.com';
        $nombreCliente = $this->reserva->cliente->user->name ?? 'Cliente';

        $this->reserva->loadMissing(['cliente.user', 'profesional.user', 'servicio']);

        $emailCliente = $this->reserva->cliente->user->email ?? null;

        if ($emailCliente) {
            Mail::to($emailCliente)->send(new ReservaStatusMail($this->reserva, $this->estado));
        }
        
    }
}