<?php

namespace App\Providers;

use App\Models\Reserva;
use App\Observers\ReservaObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Reserva::observe(ReservaObserver::class);
        // REGISTRO DE AUDITORÍA NOSQL PARA CORREOS ENVIADOS
        Event::listen(MessageSent::class, function (MessageSent $event) {
            $to = array_map(fn($address) => $address->getAddress(), $event->message->getTo() ?? []);
            $from = array_map(fn($address) => $address->getAddress(), $event->message->getFrom() ?? []);
            
            app(\App\Services\EventLogService::class)->log('email_enviado', [
                'subject' => $event->message->getSubject(),
                'to'      => $to,
                'from'    => $from,
            ]);
        });
    }
}
