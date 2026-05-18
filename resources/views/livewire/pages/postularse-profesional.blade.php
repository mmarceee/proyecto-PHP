<?php

use App\Models\Profesional;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component
{
    public string $especialidad = '';
    public string $descripcion = '';
    public string $nombre_comercial = '';

    public function mount(): void
    {
        //Si ya envió una solicitud o ya es profesional, lo sacamos de acá
        if (Auth::user()->profesional()->exists()) {
            $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function enviarPostulacion(): void
    {
        $validated = $this->validate([
            'especialidad'     => ['required', 'string', 'max:255'],
            'descripcion'      => ['required', 'string', 'max:1000'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
        ]);

        // Creamos el perfil profesional asociado al usuario logueado
        Auth::user()->profesional()->create([
            'especialidad'        => $validated['especialidad'],
            'descripcion'         => $validated['descripcion'],
            'nombre_comercial'    => $validated['nombre_comercial'] ?? null,
            'reputacion_promedio' => 0.00,
            'estado'              => 'pendiente', // Se envía a revisión de los admins
        ]);

        session()->flash('success', 'Tu solicitud ha sido enviada. Un administrador la revisará pronto.');

        $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<div class="max-w-2xl mx-auto p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
    <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">
        Postularse como Profesional de la Clínica
    </h2>
    
    <form wire:submit="enviarPostulacion" class="space-y-4">
        <div>
            <x-input-label for="especialidad" :value="__('Especialidad Médica / Rubro')" />
            <x-text-input wire:model="especialidad" id="especialidad" class="block mt-1 w-full" type="text" required />
            <x-input-error :messages="$errors->get('especialidad')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="descripcion" :value="__('Descripción de tu perfil profesional')" />
            <textarea wire:model="descripcion" id="descripcion" rows="4" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 text-gray-900 dark:text-gray-100" required></textarea>
            <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="nombre_comercial" :value="__('Nombre comercial (Opcional)')" />
            <x-text-input wire:model="nombre_comercial" id="nombre_comercial" class="block mt-1 w-full" type="text" />
            <x-input-error :messages="$errors->get('nombre_comercial')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Enviar Solicitud') }}
            </x-primary-button>
        </div>
    </form>
</div>