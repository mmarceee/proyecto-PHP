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
use Illuminate\Support\Facades\Log;
use App\Services\NotificacionService;
use App\Services\EventLogService;

class EnviarRecordatorioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reserva;

    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }

    public function handle(NotificacionService $notificacionService, EventLogService $eventLogService): void
    {
        $marcada = Reserva::whereKey($this->reserva->id)
            ->whereNull('recordatorio_enviado_at')
            ->update([
                'recordatorio_enviado_at' => now(),
            ]);

        if ($marcada === 0) {
            return;
        }

        $this->reserva->refresh();

        $this->reserva->loadMissing(['cliente.user', 'profesional.user', 'servicio']);

        $emailCliente = $this->reserva->cliente->user->email ?? null;

        if ($emailCliente) {
            Mail::to($emailCliente)->send(new RecordatorioReservaMail($this->reserva));
        }

        $notificacionService->notificarRecordatorioTurno($this->reserva);

        try {
            $eventLogService->log('recordatorio_turno_enviado', [
                'reserva_id' => $this->reserva->id,
                'cliente_id' => $this->reserva->cliente_id,
                'profesional_id' => $this->reserva->profesional_id,
                'servicio_id' => $this->reserva->servicio_id,
                'estado_reserva' => $this->reserva->estado_reserva,
                'fecha' => $this->reserva->fecha?->format('d/m/Y'),
                'hora_inicio' => $this->reserva->hora_inicio,
            ], $this->reserva->cliente?->user_id);
        } catch (\Throwable $e) {
            Log::error('Fallo al registrar auditoría de recordatorio de turno: ' . $e->getMessage(), [
                'reserva_id' => $this->reserva->id,
            ]);
        }
    }
}