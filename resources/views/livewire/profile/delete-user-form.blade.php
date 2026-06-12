<section class="space-y-6" x-data="{ modalAbierto: false, ...deleteAccountForm() }">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Eliminar Cuenta
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Una vez que se elimine tu cuenta, todos sus recursos y datos se borrarán. Antes de eliminar tu cuenta, descarga cualquier dato o información que desees conservar.
        </p>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"> 
            Esta dirección de correo no podrá volver a ser utilizada.
        </p>
    </header>

    <x-danger-button @click="modalAbierto = true" data-requires-online>
        Eliminar Cuenta
    </x-danger-button>

    <div x-show="modalAbierto" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalAbierto" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="modalAbierto = false" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="modalAbierto" x-transition class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        ¿Estás seguro de que quieres eliminar tu cuenta?
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Una vez que se elimine tu cuenta, todos sus datos se borrarán permanentemente. Por favor, ingresa tu contraseña para confirmar.
                    </p>

                    <div class="mt-6">
                        <x-input-label for="password_delete" value="Contraseña" class="sr-only" />
                        <x-text-input x-model="password" id="password_delete" type="password" class="mt-1 block w-3/4" placeholder="Contraseña" />
                        
                        <template x-if="errores.password">
                            <span class="text-sm text-red-600 dark:text-red-400 mt-2 block" x-text="errores.password[0]"></span>
                        </template>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-700">
                    <button @click="borrarCuenta" x-bind:disabled="cargando" data-requires-online type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span x-text="cargando ? 'Eliminando...' : 'Eliminar Cuenta'"></span>
                    </button>
                    <button @click="modalAbierto = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
