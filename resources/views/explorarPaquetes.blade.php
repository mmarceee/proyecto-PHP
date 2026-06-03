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
                                    @click="comprar(paquete.id)"
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
        </main>
    </div>
</x-app-layout>