<?php

namespace App\Providers;

use App\Models\Reserva;
use App\Observers\ReservaObserver;
use Illuminate\Support\ServiceProvider;

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
    }
}
