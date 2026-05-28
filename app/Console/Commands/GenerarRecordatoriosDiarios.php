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
    protected $description = 'Busca las reservas de mañana y encola los correos de recordatorio en Redis';

    public function handle()
    {
        // 1. Calculamos la fecha de mañana
        $manana = Carbon::tomorrow()->toDateString(); // Ej: '2026-05-29'

        // 2. Buscamos en la BD todos los turnos para mañana que estén confirmados
        // (Ajusta el 'Confirmada' a como lo escribas exactamente en tu base de datos)
        $reservas = Reserva::where('fecha', $manana)
                           ->whereIn('estado_reserva', ['Confirmada', 'Pendiente', 'confirmada', 'pendiente'])
                           ->get();

        $contador = 0;

        // 3. Iteramos y tiramos un Job a Redis por cada reserva encontrada
        foreach ($reservas as $reserva) {
            EnviarRecordatorioJob::dispatch($reserva);
            $contador++;
        }

        $this->info("¡Listo! Se encolaron {$contador} recordatorios en Redis para mañana.");
    }
}