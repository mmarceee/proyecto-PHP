<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Throwable;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse //Usa Socialite para redirigir al usuario a la página de autenticación de Google.
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse //Recibe la respuesta de Google después de que el usuario se autentica. Intenta obtener la información del usuario de Google y, si tiene éxito, muestra un volcado de los datos del usuario. Si ocurre un error, redirige al usuario a la página de inicio de sesión con un mensaje de error.
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()
                ->route('login')
                ->with('status', 'No pudimos iniciar sesión con Google. Intentá nuevamente.');
        }

        $email = Str::lower($googleUser->getEmail() ?? '');

        if ($email === '') {
            return redirect()
                ->route('login')
                ->with('status', 'Google no devolvió un email válido para esta cuenta.');
        }

        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        $debeCompletarPerfil = false;

        if ($user === null) {
            $user = User::create([
                'name' => $googleUser->getName() ?? 'Usuario',
                'apellido' => 'Pendiente',
                'email' => $email,
                'email_verified_at' => now(),
                'google_id' => $googleUser->getId(),
                'password' => Hash::make(Str::random(32)),
                'telefono' => '000000000',
                'estado_usuario' => User::ESTADO_ACTIVO,
            ]);

            $debeCompletarPerfil = true;
        } else {
            if ($user->google_id === null) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            }

            $debeCompletarPerfil = $user->apellido === 'Pendiente' || $user->telefono === '000000000';
        }

        if ($user->estaBloqueado()) {
            return redirect()
                ->route('login')
                ->with('status', 'Tu usuario se encuentra bloqueado. Contactá con un administrador.');
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        if ($debeCompletarPerfil) {
            return redirect()->route('perfil.completar');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}