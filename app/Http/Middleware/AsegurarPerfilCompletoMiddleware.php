<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AsegurarPerfilCompletoMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $perfilIncompleto = $user->apellido === 'Pendiente'
            || $user->telefono === '000000000';

        if ($perfilIncompleto && ! $request->routeIs('perfil.completar')) {
            return redirect()->route('perfil.completar');
        }

        return $next($request);
    }
}