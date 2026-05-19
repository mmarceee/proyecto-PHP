<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

// 1. LIMPIEZA: Dejamos el bloque PHP casi vacío. 
// Solo lo usamos para cargar los datos iniciales al abrir la pantalla.
// Borramos toda la lógica de validación y guardado en base de datos.
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
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information (API Version)') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Esta pantalla ahora guarda los datos consumiendo nuestra propia API REST.
        </p>
    </header>

    <form id="formulario-perfil-api" class="mt-6 space-y-6">
        <div>
            <x-input-label for="name" :value="__('Nombre')" />
            <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Correo')" />
            <x-text-input wire:model="email" id="email" type="email" class="mt-1 block w-full" />
        </div>

        <div class="flex items-center gap-4">
            <button type="button" onclick="guardarPerfilPorAPI()" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700">
                Guardar usando API
            </button>
            <span id="mensaje-exito" class="text-green-600 hidden">¡Guardado exitosamente!</span>
        </div>
    </form>
</section>

<script>
    function guardarPerfilPorAPI() {
        const nombreIngresado = document.getElementById('name').value;
        const emailIngresado = document.getElementById('email').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Ocultamos el mensaje de éxito antes de intentar guardar
        document.getElementById('mensaje-exito').classList.add('hidden');

        fetch('/api/profile/info', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            // LA CLAVE MAGICA: Le dice a JS que envíe tu cookie de sesión a la API
            credentials: 'same-origin',
            body: JSON.stringify({
                name: nombreIngresado,
                email: emailIngresado
            })
        })
        .then(response => {
            // AHORA SI: Verificamos si el servidor nos dio un OK (200)
            if (!response.ok) {
                throw new Error('Error del servidor: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log("Respuesta de la API:", data);
            
            // Solo mostramos el verde si realmente guardó
            document.getElementById('mensaje-exito').classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('mensaje-exito').classList.add('hidden');
            }, 3000);
        })
        .catch(error => {
            // Ahora los 401 o 500 caerán aquí
            console.error('Hubo un error contactando a la API:', error);
            alert("No se pudo guardar. Revisa la consola roja.");
        });
    }
</script>