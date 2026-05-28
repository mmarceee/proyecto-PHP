<?php

use Illuminate\Support\Facades\Auth;
use App\Services\CompletarPerfilService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $name = '';
    public string $apellido = '';
    public string $telefono = '';
    public string $tipo_registro = 'cliente';
    public string $descripcion = '';
    public string $especialidad = '';
    public string $nombre_comercial = '';

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name ?? '';
        $this->apellido = $user->apellido === 'Pendiente' ? '' : ($user->apellido ?? '');
        $this->telefono = $user->telefono === '000000000' ? '' : ($user->telefono ?? '');
    }

    public function completarPerfil(CompletarPerfilService $service): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:30'],
            'tipo_registro' => ['required', 'in:cliente,profesional'],
            'descripcion' => ['required_if:tipo_registro,profesional', 'string', 'nullable', 'max:1000'],
            'especialidad' => ['required_if:tipo_registro,profesional', 'string', 'nullable', 'max:255'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
        ]);

        $service->completar(Auth::user(), $validated);

        $this->redirect(route('dashboard', absolute: false), navigate: true);

    }
}; 
?>

<div class="mx-auto w-full max-w-xl px-4 py-8 sm:px-6 lg:py-12">
    <form wire:submit="completarPerfil">
        <div>
            <x-input-label for="name" :value="__('Nombre')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="apellido" :value="__('Apellido')" />
            <x-text-input wire:model="apellido" id="apellido" class="block mt-1 w-full" type="text" required />
            <x-input-error :messages="$errors->get('apellido')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="telefono" :value="__('Teléfono')" />
            <x-text-input wire:model.live="telefono" id="telefono" class="block mt-1 w-full" type="tel" required inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
            <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="tipo_registro" :value="__('Tipo de registro')" />

            <select wire:model.live="tipo_registro" id="tipo_registro" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                <option value="cliente">Cliente</option>
                <option value="profesional">Profesional</option>
            </select>

            <x-input-error :messages="$errors->get('tipo_registro')" class="mt-2" />
        </div>

        @if ($tipo_registro === 'profesional')
            <div class="mt-4">
                <x-input-label for="especialidad" :value="__('Especialidad')" />
                <x-text-input wire:model="especialidad" id="especialidad" class="block mt-1 w-full" type="text" />
                <x-input-error :messages="$errors->get('especialidad')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="descripcion" :value="__('Descripción profesional')" />
                <textarea wire:model="descripcion" id="descripcion" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"></textarea>
                <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="nombre_comercial" :value="__('Nombre comercial')" />
                <x-text-input wire:model="nombre_comercial" id="nombre_comercial" class="block mt-1 w-full" type="text" />
                <x-input-error :messages="$errors->get('nombre_comercial')" class="mt-2" />
            </div>

            <p class="mt-2 text-sm text-gray-600">
                Tu perfil profesional quedará pendiente de aprobación por un administrador.
            </p>
        @endif

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>
                {{ __('Completar perfil') }}
            </x-primary-button>
        </div>
    </form>
</div>



