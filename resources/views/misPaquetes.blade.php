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
                                    <button @click="abrirHistorial(compra)" class="text-blue-400 hover:text-blue-300 font-semibold underline flex items-center gap-1 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Ver usos
                                    </button>
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
            <div 
                x-show="historialAbierto" 
                style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm"
            >
                <div 
                    @click.away="cerrarHistorial()" 
                    class="bg-gray-800 rounded-2xl w-full max-w-lg shadow-2xl border border-gray-700 overflow-hidden flex flex-col max-h-[80vh]"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div class="px-6 py-4 border-b border-gray-700 flex justify-between items-center bg-gray-900/50">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Historial de Sesiones
                        </h3>
                        <button @click="cerrarHistorial()" class="text-gray-400 hover:text-white transition bg-gray-800 hover:bg-gray-700 p-1.5 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    
                    <div class="p-6 overflow-y-auto">
                        <div class="text-sm text-gray-400 mb-4 pb-4 border-b border-gray-700/50">
                            Mostrando consumos del paquete: <br>
                            <strong class="text-blue-400 text-base" x-text="paqueteActivoNombre"></strong>
                        </div>

                        <div x-show="cargandoHistorial" class="text-center py-8">
                            <div class="animate-spin inline-block w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full mb-2"></div>
                            <p class="text-gray-400 italic text-sm">Cargando registros...</p>
                        </div>
                        
                        <div x-show="!cargandoHistorial && historial.length === 0" class="text-center py-10 bg-gray-900/30 rounded-xl border border-gray-700/50">
                            <div class="text-gray-500 mb-2 text-4xl">🎫</div>
                            <p class="text-gray-400 font-medium">Aún no has consumido sesiones.</p>
                        </div>
                        
                        <ul x-show="!cargandoHistorial && historial.length > 0" class="space-y-3">
                            <template x-for="(uso, index) in historial" :key="uso.id">
                                <li class="bg-gray-900/50 border border-gray-700/60 rounded-xl p-4 flex justify-between items-center hover:bg-gray-800/80 transition">
                                    <div class="flex items-start gap-4">
                                        <div class="bg-blue-900/50 text-blue-400 font-black text-sm w-8 h-8 rounded-full flex items-center justify-center border border-blue-700/50 shrink-0">
                                            <span x-text="historial.length - index"></span>
                                        </div>
                                        
                                        <div>
                                            <div class="font-bold text-gray-200">
                                                Turno: <span x-text="uso.reserva_fecha"></span> <span class="text-gray-500 mx-1">|</span> <span x-text="uso.reserva_hora"></span> hs
                                            </div>
                                            <div class="text-sm text-gray-400 mt-0.5 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                <span x-text="'Prof. ' + uso.profesional"></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-right ml-2 shrink-0">
                                        <span class="px-2 py-1 text-[10px] font-bold rounded-md uppercase tracking-wider" 
                                            :class="{
                                                'bg-green-900/50 text-green-400 border border-green-800/50': uso.estado_reserva === 'finalizada' || uso.estado_reserva === 'en_curso',
                                                'bg-gray-700 text-gray-300': uso.estado_reserva !== 'finalizada' && uso.estado_reserva !== 'en_curso'
                                            }"
                                            x-text="uso.estado_reserva"
                                        ></span>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>