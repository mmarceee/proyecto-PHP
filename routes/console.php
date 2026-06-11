<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Reserva;
use App\Services\ReservaService;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reservas:recordatorios')->dailyAt('08:00');
Schedule::command('reservas:recordatorios')->dailyAt('20:00');

Schedule::call(function (ReservaService $reservaService) {
    $ahora = now();
    
    // Obtenemos todas las reservas en curso de fechas pasadas o del día de hoy
    $reservas = Reserva::where('estado_reserva', 'en_curso')
        ->where('fecha', '<=', $ahora->toDateString())
        ->get()
        ->filter(function ($reserva) use ($ahora) {
            $horaFinReal = Carbon::parse($reserva->fecha . ' ' . $reserva->hora_fin);
            return $ahora->isAfter($horaFinReal);
        });
    foreach ($reservas as $reserva) {
        $reservaService->avanzarEstado($reserva);
    }
})->everyMinute();
