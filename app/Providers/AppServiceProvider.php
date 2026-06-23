<?php

namespace App\Providers;

use App\Models\Pago;
use App\Observers\PagoObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use App\Services\EventLogService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        
    }

    public function boot(): void
    {

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Pago::observe(PagoObserver::class);

        // REGISTRO DE AUDITORÍA NOSQL PARA CORREOS ENVIADOS
        Event::listen(MessageSent::class, function (MessageSent $event) {
            $to = array_map(fn($address) => $address->getAddress(), $event->message->getTo() ?? []);
            $from = array_map(fn($address) => $address->getAddress(), $event->message->getFrom() ?? []);
            
            // Leemos la cabecera personalizada para clasificar la acción
            $actionHeader = $event->message->getHeaders()->get('X-Email-Action');
            $action = $actionHeader ? $actionHeader->getBodyAsString() : 'desconocido';
            app(EventLogService::class)->log('email_enviado', [
                'accion'  => $action,
                'subject' => $event->message->getSubject(),
                'to'      => $to,
                'from'    => $from,
            ]);
        });
    }
}