<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $apellido = '';
    public string $email = '';
    public string $telefono = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $tipo_registro = 'cliente';
    public string $descripcion = '';
    public string $especialidad = '';
    public string $nombre_comercial = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'apellido' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:30'],
            'tipo_registro' => ['required', 'in:cliente,profesional'],
            'descripcion' => ['required_if:tipo_registro,profesional', 'string','nullable', 'max:1000'],
            'especialidad' => ['required_if:tipo_registro,profesional', 'string', 'nullable', 'max:255'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
            
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = null;

        DB::transaction(function () use ($validated, &$user) {

            $user = User::create([
                'name' => $validated['name'],
                'apellido' => $validated['apellido'],
                'email' => $validated['email'],
                'telefono' => $validated['telefono'],
                'password' => $validated['password'],
                'estado_usuario' => 'activo',
            ]);

            $user->cliente()->create();

            if ($validated['tipo_registro'] === 'profesional') {
                $user->profesional()->create([
                    'descripcion' => $validated['descripcion'],
                    'especialidad' => $validated['especialidad'],
                    'reputacion_promedio' => 0,
                    'nombre_comercial' => $validated['nombre_comercial'] ?? null,
                    'estado' => 'pendiente',
                ]);
            }
        });

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; 
?>

<div>
    <form wire:submit="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nombre')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Apellido -->
        <div class="mt-4">
            <x-input-label for="apellido" :value="__('Apellido')" />
            <x-text-input wire:model="apellido" id="apellido" class="block mt-1 w-full" type="text" name="apellido" required autocomplete="apellido" />
            <x-input-error :messages="$errors->get('apellido')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Teléfono -->
        <div class="mt-4">
            <x-input-label for="telefono" :value="__('Teléfono')" />
            <x-text-input wire:model.live="telefono" id="telefono" class="block mt-1 w-full" type="tel" name="telefono" required inputmode="numeric" pattern="[0-9]*" autocomplete="tel" oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
            <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="tipo_registro" :value="__('Tipo de registro')" />

            <select wire:model.live="tipo_registro" id="tipo_registro" name="tipo_registro" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                <option value="cliente">Cliente</option>
                <option value="profesional">Profesional</option>
            </select>

            <x-input-error :messages="$errors->get('tipo_registro')" class="mt-2" />
         </div> 

         @if ($tipo_registro === 'profesional')
            <div class="mt-4">
                <x-input-label for="especialidad" :value="__('Especialidad')" />
                <x-text-input wire:model="especialidad" id="especialidad" class="block mt-1 w-full" type="text" name="especialidad" />
                <x-input-error :messages="$errors->get('especialidad')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="descripcion" :value="__('Descripción profesional')" />
                <textarea wire:model="descripcion" id="descripcion" name="descripcion" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"></textarea>
                <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="nombre_comercial" :value="__('Nombre comercial')" />
                <x-text-input wire:model="nombre_comercial" id="nombre_comercial" class="block mt-1 w-full" type="text" name="nombre_comercial" />
                <x-input-error :messages="$errors->get('nombre_comercial')" class="mt-2" />
            </div>

            <p class="mt-2 text-sm text-gray-600">
                Tu perfil profesional quedará pendiente de aprobación por un administrador.
            </p>
        @endif

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}" wire:navigate>
                {{ __('¿Ya estás registrado?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Registrarse') }}
            </x-primary-button>
        </div>
    </form>
</div>
