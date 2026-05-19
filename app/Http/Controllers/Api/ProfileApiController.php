<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProfileService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

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
            'usuario' => [
                'nombre' => $user->name,
                'correo' => $user->email
                // Solo mandamos esto. Ni el ID, ni fechas de creación, nada extra.
            ] 
        ]);
    }

    public function updatePassword(Request $request, ProfileService $profileService)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ], [
            'current_password.required' => 'La contraseña actual es obligatoria.',
            'current_password.current_password' => 'La contraseña actual es incorrecta.',
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.'
        ]);

        $profileService->updatePassword($request->user(), $validated['password']);

        return response()->json(['mensaje' => 'Contraseña actualizada exitosamente.']);
    }

    public function destroy(Request $request, ProfileService $profileService)
    {
        // El segundo array contiene los mensajes personalizados
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => 'Por favor, ingresa tu contraseña.',
            'password.current_password' => 'La contraseña es incorrecta.'
        ]);

        $user = $request->user();
        
        Auth::guard('web')->logout();
        $profileService->deleteAccount($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['mensaje' => 'Cuenta eliminada exitosamente.']);
    }
    
}