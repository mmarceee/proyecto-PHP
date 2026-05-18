<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProfileService;
use Illuminate\Validation\Rule;

class ProfileApiController extends Controller
{
    public function updateInfo(Request $request, ProfileService $profileService)
    {
        // 1. El Mozo toma y revisa el pedido
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)],
        ]);

        // 2. El Mozo le pasa el pedido al Chef (El Servicio)
        $user = $profileService->updateInformation($request->user(), $validated);

        // 3. El Mozo devuelve el plato listo en formato JSON
        return response()->json([
            'mensaje' => 'Perfil actualizado exitosamente',
            'usuario' => $user
        ]);
    }
}