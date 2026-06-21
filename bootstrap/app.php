<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Confiar en el balanceador de carga de DigitalOcean
        $middleware->trustProxies(at: '*'); 

        $middleware->statefulApi();

        $middleware->alias([
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'perfil.completo' => \App\Http\Middleware\AsegurarPerfilCompletoMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();