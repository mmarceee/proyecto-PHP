<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Jobs\EnviarRecordatorioJob;
use Carbon\Carbon;

class GenerarRecordatoriosDiarios extends Command
{
    // El nombre que usarás en la terminal para llamarlo
    protected $signature = 'reservas:recordatorios';
    protected $description = 'Busca las reservas confirmadas dentro de las próximas 24 horas y encola los correos de recordatorio en Redis';

    public function handle()
    {
        // 1. Calculamos la fecha de hoy y 24hrs para adelante
        $desde = now();
        $hasta = now()->addDay();

        // 2. Buscamos reservas confirmadas dentro de las próximas 24 horas
        $reservas = Reserva::whereNull('recordatorio_enviado_at')
            ->whereIn('estado_reserva', ['confirmada'])
            ->get()
            ->filter(function (Reserva $reserva) use ($desde, $hasta) {
                $inicioReserva = Carbon::parse($reserva->fecha->format('Y-m-d') . ' ' . $reserva->hora_inicio);

                return $inicioReserva->between($desde, $hasta);
            });

        $contador = 0;

        // 3. Iteramos y tiramos un Job a Redis por cada reserva encontrada
        foreach ($reservas as $reserva) {
            EnviarRecordatorioJob::dispatch($reserva);
            $contador++;
        }

        $this->info("¡Listo! Se encolaron {$contador} recordatorios en Redis para las próximas 24 horas.");
    }
}