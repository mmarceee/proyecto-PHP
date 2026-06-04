<x-app-layout>
    <div class="flex min-h-screen">
        <x-app-sidebar/>
        
        <main class="flex-1 min-w-0 lg:ml-20 bg-gray-900 text-gray-100" x-data="misPaquetesCliente">
            <div class="border-b border-gray-700 bg-gray-800">
                <div class="max-w-7xl mx-auto pl-14 pr-6 py-6 sm:px-6 lg:px-8">
                    <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                        {{ __('Mis Paquetes Adquiridos') }}
                    </h2>
                </div>
            </div>

            <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                {{-- Alertas y estados --}}
                <div x-show="error" class="p-4 bg-red-950/40 border border-red-800 text-red-200 text-sm rounded-xl" x-text="error" style="display: none;"></div>
                <div x-show="cargando" class="text-center py-12 text-gray-400 italic">Cargando tu inventario...</div>

                {{-- Vista vacía --}}
                <template x-if="!cargando && compras.length === 0">
                    <div class="p-12 text-center bg-gray-800 border border-dashed border-gray-700 rounded-2xl text-gray-400">
                        Aún no tienes paquetes comprados. ¡Explora los disponibles y ahorra en tus sesiones!
                        <br>
                        <a href="{{ route('cliente.paquetes.explorar') }}" class="inline-block mt-4 text-blue-400 hover:text-blue-300 font-bold underline">Ir a explorar paquetes</a>
                    </div>
                </template>

                {{-- Grilla de Paquetes Comprados --}}
                <div x-show="!cargando && compras.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-6" style="display: none;">
                    <template x-for="compra in compras" :key="compra.id">
                        <div 
                            class="bg-gray-800 border border-gray-700 rounded-2xl p-6 shadow-sm flex flex-col relative overflow-hidden"
                            :class="compra.estado_paquete === 'completado' ? 'opacity-60' : ''"
                        >
                            {{-- Etiqueta de Estado --}}
                            <div class="absolute top-4 right-4">
                                <span 
                                    class="text-xs font-bold px-2 py-1 rounded-md uppercase"
                                    :class="compra.estado_paquete === 'activo' ? 'bg-green-900/50 text-green-400 border border-green-700/50' : 'bg-gray-700 text-gray-400'"
                                    x-text="compra.estado_paquete"
                                ></span>
                            </div>

                            <div class="mb-4 pr-20">
                                <h3 class="text-xl font-bold text-white mb-1" x-text="compra.paquete_servicio.nombre"></h3>
                                <div class="text-sm text-gray-400 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span x-text="'Prof. ' + compra.paquete_servicio.profesional?.user?.name + ' ' + (compra.paquete_servicio.profesional?.user?.last_name || '')"></span>
                                </div>
                            </div>

                            {{-- Barra de Progreso --}}
                            <div class="bg-gray-900/80 p-4 rounded-xl border border-gray-700/50 mb-4">
                                <div class="flex justify-between items-end mb-2">
                                    <span class="text-sm text-gray-400 font-medium">Progreso del paquete</span>
                                    <span class="text-lg font-bold text-white">
                                        <span x-text="compra.paquete_servicio.cantidad_sesiones - compra.sesiones_disponibles"></span> / <span x-text="compra.paquete_servicio.cantidad_sesiones"></span> <span class="text-xs text-gray-500 font-normal">usadas</span>
                                    </span>
                                </div>
                                
                                {{-- Contenedor de la barra --}}
                                <div class="w-full bg-gray-700 rounded-full h-2.5 overflow-hidden">
                                    <div 
                                        class="h-2.5 rounded-full transition-all duration-500"
                                        :class="compra.sesiones_disponibles === 0 ? 'bg-gray-500' : 'bg-blue-500'"
                                        :style="`width: ${calcularPorcentaje(compra.sesiones_disponibles, compra.paquete_servicio.cantidad_sesiones)}%`"
                                    ></div>
                                </div>
                                
                                <div class="mt-3 text-sm text-center">
                                    <template x-if="compra.sesiones_disponibles > 0">
                                        <span class="text-green-400 font-semibold bg-green-950/30 px-3 py-1 rounded-full border border-green-800/30">
                                            ¡Te quedan <span x-text="compra.sesiones_disponibles"></span> sesiones disponibles!
                                        </span>
                                    </template>
                                    <template x-if="compra.sesiones_disponibles === 0">
                                        <span class="text-gray-400">Has consumido todas las sesiones.</span>
                                    </template>
                                </div>
                            </div>

                            {{-- Footer con Vencimiento --}}
                            <div class="mt-auto border-t border-gray-700/60 pt-4 flex justify-between items-center text-xs">
                                <div class="text-gray-500">
                                    Comprado el <span x-text="new Date(compra.fecha_compra).toLocaleDateString('es-ES')"></span>
                                </div>
                                <div class="text-amber-500 font-medium bg-amber-950/20 px-2 py-1 rounded-md border border-amber-900/30 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Vence: <span x-text="calcularVencimiento(compra.fecha_compra, compra.paquete_servicio.validez_meses)"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

            </div>
        </main>
    </div>
</x-app-layout>