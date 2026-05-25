<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProfileService;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class ProfileApiController extends Controller
{
    public function updateInfo(Request $request, ProfileService $profileService)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'apellido' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $profileService->updateInformation($request->user(), $validated);

        $profesional = $user->profesional;

        return response()->json([
            'mensaje' => 'Perfil actualizado exitosamente',
            'usuario' => [
                'nombre' => $user->name,
                'apellido' => $user->apellido,
                'correo' => $user->email,
                'telefono' => $user->telefono,
            ],
            'profesional' => $user->esProfesionalAprobado() && $profesional ? [
                'descripcion' => $profesional->descripcion,
                'nombre_comercial' => $profesional->nombre_comercial,
            ] : null,
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