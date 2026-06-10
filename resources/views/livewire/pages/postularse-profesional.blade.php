<x-app-layout>
    <div 
        x-data="formularioPostulacion()" 
        x-init="verificarEstado()"
        class="max-w-2xl mx-auto p-6 mt-10 bg-white dark:bg-gray-800 rounded-lg shadow"
    >
        <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">
            Postularse como profesional para ofrecer servicios
        </h2>
        
        <template x-if="mensajeExito">
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded" x-text="mensajeExito"></div>
        </template>
        <template x-if="mensajeError">
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded" x-text="mensajeError"></div>
        </template>

        <form @submit.prevent="enviarPostulacion" class="space-y-4" x-show="!redireccionando">
            <div>
                <x-input-label for="especialidad" :value="__('Especialidad / Rubro')" />
                <x-text-input x-model="formulario.especialidad" id="especialidad" class="block mt-1 w-full" type="text" required />
                <template x-if="errores.especialidad">
                    <p class="text-sm text-red-600 mt-2" x-text="errores.especialidad[0]"></p>
                </template>
            </div>

            <div>
                <x-input-label for="descripcion" :value="__('Descripción de tu perfil profesional')" />
                <textarea x-model="formulario.descripcion" id="descripcion" rows="4" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 text-gray-900 dark:text-gray-100" required></textarea>
                <template x-if="errores.descripcion">
                    <p class="text-sm text-red-600 mt-2" x-text="errores.descripcion[0]"></p>
                </template>
            </div>

            <div>
                <x-input-label for="nombre_comercial" :value="__('Nombre comercial (Opcional)')" />
                <x-text-input x-model="formulario.nombre_comercial" id="nombre_comercial" class="block mt-1 w-full" type="text" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-primary-button x-bind:disabled="cargando" data-requires-online>
                    <span x-text="cargando ? 'Enviando...' : 'Enviar Solicitud'"></span>
                </x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
