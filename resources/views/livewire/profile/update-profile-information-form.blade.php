<?php
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }
}; ?>

<section>
    @vite(['resources/js/profile.js'])

    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Información del Perfil
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Actualiza los datos de tu cuenta y tu correo electrónico.
        </p>
    </header>

    <div x-data="perfilForm('{{ $name }}', '{{ $email }}')" class="mt-6 space-y-6">
        
        <template x-if="mensaje">
            <div class="mb-4 p-4 bg-green-950/40 border border-green-800 text-green-200 text-sm rounded" x-text="mensaje"></div>
        </template>
        <template x-if="error">
            <div class="mb-4 p-4 bg-red-950/40 border border-red-800 text-red-200 text-sm rounded" x-text="error"></div>
        </template>

        <div>
            <x-input-label for="name" value="Nombre Completo" />
            <x-text-input x-model="name" id="name" type="text" class="mt-1 block w-full" />
        </div>

        <div>
            <x-input-label for="email" value="Correo Electrónico" />
            <x-text-input x-model="email" id="email" type="email" class="mt-1 block w-full" />
        </div>

        <div class="flex items-center gap-4">
            <button 
                @click="guardarPerfil" 
                x-bind:disabled="cargando"
                class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50 transition"
            >
                <span x-text="cargando ? 'Guardando...' : 'Guardar Cambios'"></span>
            </button>
        </div>
    </div>
</section>