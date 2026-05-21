<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ClienteService;
use Illuminate\Support\Facades\Auth;

class ClienteApiController extends Controller
{
    public function store(Request $request, ClienteService $clienteService)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'telefono' => ['required', 'string', 'max:255'],
        ]);

        $user = $clienteService->registrarCliente($validated);

        // Mantenemos el auto-login tras registrarse con éxito
        Auth::login($user);

        return response()->json([
            'message' => '¡Bienvenido! Tu cuenta ha sido creada.',
            'user' => $user
        ], 201);
    }
}