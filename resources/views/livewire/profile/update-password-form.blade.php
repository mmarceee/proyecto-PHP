<section x-data="passwordForm()">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Actualizar Contraseña
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Asegúrate de que tu cuenta use una contraseña larga y aleatoria para mantenerte seguro.
        </p>
    </header>

    <div class="mt-6 space-y-6">
        <template x-if="mensaje">
            <div class="mb-4 p-4 bg-green-950/40 border border-green-800 text-green-200 text-sm rounded" x-text="mensaje"></div>
        </template>
        <template x-if="errores.general">
            <div class="mb-4 p-4 bg-red-950/40 border border-red-800 text-red-200 text-sm rounded" x-text="errores.general[0]"></div>
        </template>

        <div>
            <x-input-label for="current_password" value="Contraseña Actual" />
            <x-text-input x-model="current_password" id="current_password" type="password" class="mt-1 block w-full" />
            <template x-if="errores.current_password">
                <span class="text-sm text-red-600 dark:text-red-400 mt-2 block" x-text="errores.current_password[0]"></span>
            </template>
        </div>

        <div>
            <x-input-label for="password" value="Nueva Contraseña" />
            <x-text-input x-model="password" id="password" type="password" class="mt-1 block w-full" />
            <template x-if="errores.password">
                <span class="text-sm text-red-600 dark:text-red-400 mt-2 block" x-text="errores.password[0]"></span>
            </template>
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirmar Contraseña" />
            <x-text-input x-model="password_confirmation" id="password_confirmation" type="password" class="mt-1 block w-full" />
        </div>

        <div class="flex items-center gap-4">
            <button 
                @click="guardarPassword" 
                x-bind:disabled="cargando"
                class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50 transition"
            >
                <span x-text="cargando ? 'Guardando...' : 'Guardar Cambios'"></span>
            </button>
        </div>
    </div>
</section>