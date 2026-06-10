<x-app-layout>
    <div class="flex min-h-screen">
        <x-app-sidebar/>
        
        <main class="flex-1 min-w-0 lg:ml-20 bg-gray-900 text-gray-100" x-data="explorarPaquetes">
            <div class="border-b border-gray-700 bg-gray-800">
                <div class="max-w-7xl mx-auto pl-14 pr-6 py-6 sm:px-6 lg:px-8">
                    <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                        {{ __('Explorar Paquetes de Sesiones') }}
                    </h2>
                </div>
            </div>

            <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                {{-- Alertas --}}
                <div x-show="mensajeExito" x-transition class="p-4 bg-green-950/40 border border-green-800 text-green-200 text-sm rounded-xl" x-text="mensajeExito" style="display: none;"></div>
                <div x-show="error" x-transition class="p-4 bg-red-950/40 border border-red-800 text-red-200 text-sm rounded-xl" x-text="error" style="display: none;"></div>

                {{-- Estado de carga --}}
                <div x-show="cargando" class="text-center py-12 text-gray-400 italic">
                    Buscando paquetes disponibles...
                </div>

                {{-- Grilla de la tienda --}}
                <div x-show="!cargando && paquetes.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" style="display: none;">
                    <template x-for="paquete in paquetes" :key="paquete.id">
                        <div class="bg-gray-800 border border-gray-700 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-white mb-1" x-text="paquete.nombre"></h3>
                                
                                {{-- Nombre del profesional --}}
                                <div class="text-xs text-gray-400 font-medium mb-3 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span x-text="paquete.profesional?.user?.name + ' ' + (paquete.profesional?.user?.last_name || '')"></span>
                                </div>

                                <span class="text-xs text-blue-400 font-medium uppercase tracking-wider block mb-3" x-text="paquete.servicio?.nombre"></span>
                                
                                <p class="text-sm text-gray-400 mb-4 line-clamp-3" x-text="paquete.descripcion ?? 'Sin descripción.'"></p>
                                
                                <div class="grid grid-cols-2 gap-2 bg-gray-900/50 p-3 rounded-xl border border-gray-700/50 text-xs mb-6">
                                    <div>
                                        <span class="text-gray-500 block">Incluye:</span>
                                        <strong class="text-white text-sm" x-text="paquete.cantidad_sesiones + ' Sesiones'"></strong>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 block">Validez:</span>
                                        <strong class="text-white text-sm" x-text="paquete.validez_meses + ' meses'"></strong>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-gray-700/60 pt-4 mt-auto">
                                <div class="text-xl font-extrabold text-white">
                                    $<span x-text="parseFloat(paquete.precio).toFixed(2)"></span>
                                </div>

                                {{-- Botón de Comprar --}}
                                <button 
                                    @click="abrirModalConfirmacion(paquete.id)"
                                    :disabled="comprandoId !== null"
                                    class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-800 text-white font-bold py-2 px-4 rounded-xl text-sm transition tracking-wide shadow-lg flex items-center gap-2"
                                >
                                    <span x-show="comprandoId === paquete.id" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                                    <span x-text="comprandoId === paquete.id ? 'PROCESANDO...' : 'COMPRAR'"></span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
                        <!-- Modal de Confirmación de Compra -->
            <div x-show="showModalConfirmacion" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    
                    <!-- Fondo Oscuro -->
                    <div x-show="showModalConfirmacion" x-transition.opacity class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" aria-hidden="true"></div>

                    <!-- Centrado vertical para pantallas grandes -->
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <!-- Panel del Modal -->
                    <div x-show="showModalConfirmacion" 
                         x-transition:enter="ease-out duration-300" 
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave="ease-in duration-200" 
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-gray-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-gray-700">
                        
                        <div class="sm:flex sm:items-start">
                            <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-blue-900/50 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                                <!-- Icono de Tarjeta / PayPal -->
                                <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-white" id="modal-title">
                                    Abonar Paquete de Sesiones
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-400">
                                        ¿Estás seguro de que deseas adquirir este paquete de sesiones? Serás redirigido a la pasarela de pagos segura de PayPal para completar la transacción.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button @click="comprar()" type="button" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white transition-colors border border-transparent rounded-lg shadow-sm bg-blue-600 hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Proceder al Pago
                            </button>
                            <button @click="cerrarModalConfirmacion()" type="button" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-300 transition-colors bg-gray-700 border border-gray-600 rounded-lg shadow-sm hover:bg-gray-600 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>