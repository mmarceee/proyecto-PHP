<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $apellido = '';
    public string $telefono = '';
    public string $email = '';

    public string $descripcion = '';
    public string $nombre_comercial = '';

    public bool $esProfesionalAprobado = false;

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name ?? '';
        $this->apellido = $user->apellido ?? '';
        $this->email = $user->email ?? '';
        $this->telefono = $user->telefono ?? '';

        $profesional = $user->profesional;

        $this->esProfesionalAprobado = $user->esProfesionalAprobado();

        if ($this->esProfesionalAprobado && $profesional) {
            $this->descripcion = $profesional->descripcion ?? '';
            $this->nombre_comercial = $profesional->nombre_comercial ?? '';
        }
    }
}; 
?>

<section>
    <div  class="mt-6 space-y-6"
    x-data="perfilForm(@js([
        'name' => $name,
        'apellido' => $apellido,
        'email' => $email,
        'telefono' => $telefono,
        'descripcion' => $descripcion,
        'nombre_comercial' => $nombre_comercial,
        'esProfesionalAprobado' => $esProfesionalAprobado,
    ]))"
>
    <header class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Información del Perfil
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Actualiza los datos de tu cuenta.
            </p>
        </div>

        <template x-if="esProfesionalAprobado">
            <a
                href="/profesional/servicios"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-white hover:text-gray-800 transition"
            >
                Mis servicios
            </a>
        </template>
    </header>
        
        <template x-if="mensaje">
            <div class="mb-4 p-4 bg-green-950/40 border border-green-800 text-green-200 text-sm rounded" x-text="mensaje"></div>
        </template>

        <template x-if="aviso">
            <div class="mb-4 p-4 bg-yellow-950/40 border border-yellow-700 text-yellow-200 text-sm rounded" x-text="aviso"></div>
        </template>

        <template x-if="error">
            <div class="mb-4 p-4 bg-red-950/40 border border-red-800 text-red-200 text-sm rounded" x-text="error"></div>
        </template>

        <div>
            <x-input-label for="name" value="Nombre" />
            <x-text-input x-model="name" id="name" type="text" class="mt-1 block w-full" />
        </div>

        <div>
            <x-input-label for="apellido" value="Apellido" />
            <x-text-input x-model="apellido" id="apellido" type="text" class="mt-1 block w-full" />
        </div>

        <div>
            <x-input-label for="email" value="Correo Electrónico" />
            <div
                id="email_visual"
                x-text="email"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm px-3 py-2 bg-gray-100 dark:bg-gray-700 opacity-80"
            ></div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                El correo electrónico no se puede modificar.
            </p>
        </div>

        <div>
            <x-input-label for="telefono" value="Teléfono" />
            <x-text-input x-model="telefono" id="telefono" type="tel" class="mt-1 block w-full" />
        </div>

        <template x-if="esProfesionalAprobado">
            <div class="space-y-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-md font-medium text-gray-900 dark:text-gray-100">
                    Información profesional
                </h3>

                <div>
                    <x-input-label for="nombre_comercial" value="Nombre comercial" />
                    <x-text-input x-model="nombre_comercial" id="nombre_comercial" type="text" class="mt-1 block w-full" />
                </div>

                <div>
                    <x-input-label for="descripcion" value="Descripción profesional" />
                    <textarea 
                        x-model="descripcion"
                        id="descripcion"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    ></textarea>
                </div>
            </div>
        </template>

        <div class="flex items-center gap-4">
            <button 
                type="button"
                @click="guardarPerfil" 
                x-bind:disabled="cargando"
                data-requires-online
                class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50 transition"
            >
                <span x-text="cargando ? 'Guardando...' : 'Guardar Cambios'"></span>
            </button>
        </div>
    </div>
</section>
