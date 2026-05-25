<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Historial de Reservas') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="historialReservas">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <h3 class="text-xs uppercase tracking-wider font-extrabold text-gray-400 mb-4">Tus Turnos Pasados</h3>

            <div x-show="cargando" class="text-center py-8 text-gray-400 italic">Cargando historial...</div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-show="!cargando">
                <template x-for="reserva in reservas" :key="reserva.id">
                    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-mono bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-1 rounded" x-text="reserva.fecha"></span>
                                <span :class="reserva.estado === 'Finalizada' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'" class="text-[10px] font-extrabold px-2 py-0.5 rounded uppercase tracking-wider" x-text="reserva.estado"></span>
                            </div>
                            
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white" x-text="reserva.servicio_nombre"></h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Con: <span class="font-semibold" x-text="reserva.rol_contextual === 'cliente' ? reserva.profesional_nombre : reserva.cliente_nombre"></span>
                            </p>
                        </div>

                        <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <template x-if="reserva.estado === 'Finalizada' && !reserva.ya_calificado">
                                <button @click="abrirModalCalificacion(reserva)" class="w-full bg-blue-600/10 text-blue-600 dark:text-blue-400 py-2 rounded-lg hover:bg-blue-600 hover:text-white transition text-sm font-semibold tracking-wide">
                                    <span x-text="reserva.rol_contextual === 'cliente' ? '⭐ Calificar Atención' : '⭐ Calificar Cliente'"></span>
                                </button>
                            </template>
                            
                            <template x-if="reserva.ya_calificado">
                                <div class="text-center text-sm font-semibold text-yellow-500 dark:text-green-400">
                                    ✓ Ya valoraste este turno
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <template x-if="!cargando && reservas.length === 0">
                <div class="p-12 bg-white dark:bg-gray-800 text-center rounded-2xl text-sm text-gray-400 border border-dashed border-gray-300 dark:border-gray-700">
                    Aún no tienes un historial de reservas.
                </div>
            </template>

            <div x-show="mostrarModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
                <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-sm w-full p-6 shadow-xl border border-gray-200 dark:border-gray-700" @click.away="mostrarModal = false">
                    
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white text-center mb-1">¿Qué tal te fue?</h3>
                    <p class="text-xs text-center text-gray-500 dark:text-gray-400 mb-6" x-text="reservaSeleccionada?.servicio_nombre"></p>

                    <div x-show="error" class="mb-4 p-3 bg-red-950/40 border border-red-800 text-red-200 text-xs rounded-xl text-center" x-text="error" style="display: none;"></div>
                    <div x-show="mensaje" class="mb-4 p-3 bg-green-950/40 border border-green-800 text-green-200 text-xs rounded-xl text-center font-bold" x-text="mensaje" style="display: none;"></div>

                    <div class="flex justify-center gap-2 mb-6 cursor-pointer">
                        <template x-for="i in 5">
                            <svg 
                                @click="form.puntuacion = i" 
                                @mouseenter="estrellasHover = i" 
                                @mouseleave="estrellasHover = 0"
                                :class="(estrellasHover >= i || form.puntuacion >= i) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                                class="w-10 h-10 transition-colors duration-150" 
                                fill="currentColor" 
                                viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </template>
                    </div>

                    <div class="mb-6">
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Comentario (Opcional):</label>
                        <textarea x-model="form.comentario" rows="3" placeholder="Escribe tu experiencia..." class="w-full p-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 text-sm"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <button type="button" @click="mostrarModal = false" class="px-4 py-2 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition">Cancelar</button>
                        <button @click="enviarCalificacion()" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition">
                            Enviar
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>