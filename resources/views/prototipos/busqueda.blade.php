<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Búsqueda de Servicios Profesionales') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="busquedaServicios">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="max-w-2xl mx-auto">
                    <div class="flex shadow-xs rounded-base -space-x-0.5">
                        
                        <div class="relative">
                            <button @click="menuCategoriasAbierto = !menuCategoriasAbierto" type="button" class="inline-flex items-center shrink-0 z-10 text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 px-4 py-2.5 rounded-s-lg hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none">
                                <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.143 4H4.857A.857.857 0 0 0 4 4.857v4.286c0 .473.384.857.857.857h4.286A.857.857 0 0 0 10 9.143V4.857A.857.857 0 0 0 9.143 4Zm10 0h-4.286a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286A.857.857 0 0 0 20 9.143V4.857A.857.857 0 0 0 19.143 4Zm-10 10H4.857a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286a.857.857 0 0 0 .857-.857v-4.286A.857.857 0 0 0 9.143 14Zm10 0h-4.286a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286a.857.857 0 0 0 .857-.857v-4.286a.857.857 0 0 0-.857-.857Z"/></svg>
                                <span x-text="categoriaSeleccionada"></span>
                                <svg class="w-4 h-4 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                            </button>
                            
                            <div x-show="menuCategoriasAbierto" @click.away="menuCategoriasAbierto = false" class="absolute z-50 mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-48 p-1" style="display: none;">
                                <ul class="text-sm text-gray-900 dark:text-white">
                                    <template x-for="cat in ['Todas las categorías', 'Consultoría', 'Salud no clínica', 'Servicios Técnicos', 'Entrenamiento']">
                                        <li>
                                            <button type="button" @click="categoriaSeleccionada = cat; menuCategoriasAbierto = false" class="w-full text-left px-3 py-2 rounded-lg text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-600" x-text="cat"></button>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <input type="search" x-model.debounce.300ms="query" class="px-3 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-sm block w-full placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-white" placeholder="Buscar consultoría, entrenamiento, profesionales..." required>
                        
                        <div class="inline-flex items-center text-white bg-blue-600 px-4 py-2.5 text-sm font-medium rounded-e-lg">
                            <svg x-show="!cargando" class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                            <svg x-show="cargando" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>
                    </div>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_450px] gap-8 items-start">
                
                <div class="space-y-4">
                    <h3 class="text-xs uppercase tracking-wider font-extrabold text-gray-400">Especialistas Encontrados</h3>
                    
                    <div x-show="cargando && profesionales.length === 0" class="text-center py-8 text-gray-400 italic">Filtrando base de datos...</div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-show="!cargando || profesionales.length > 0">
                        <template x-for="profesional in profesionales" :key="profesional.id">
                            <div :class="profesionalSeleccionado?.id === profesional.id ? 'border-2 border-blue-500 ring-2 ring-blue-500/10' : 'border border-gray-200 dark:border-gray-600'" class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow-sm flex flex-col justify-between transition-all">
                                <div>
                                    <div class="flex justify-between items-start">
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-white" x-text="profesional.nombre"></h4>
                                        <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs font-medium px-2.5 py-0.5 rounded uppercase tracking-wider font-mono" x-text="profesional.nombre_comercial ?? 'Independiente'"></span>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Servicios que ofrece:</p>
                                    
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        <template x-for="s in profesional.servicios" :key="s.id">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600" x-text="s.nombre"></span>
                                        </template>
                                    </div>
                                </div>
                                
                                <button @click="verDisponibilidad(profesional)" class="w-full mt-5 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition text-sm font-semibold tracking-wide">
                                    Ver Disponibilidad
                                </button>
                            </div>
                        </template>
                    </div>

                    <template x-if="!cargando && profesionales.length === 0">
                        <div class="p-8 bg-white dark:bg-gray-800 text-center rounded-xl text-sm text-gray-400 border border-dashed border-gray-300 dark:border-gray-700">
                            Escribe el nombre de un especialista o servicio para comenzar.
                        </div>
                    </template>
                </div>

                <aside class="space-y-4">
                    <h3 class="text-xs uppercase tracking-wider font-extrabold text-gray-400">Reserva de Turno</h3>
                    
                    <div x-show="error" class="p-4 bg-red-950/40 border border-red-800 text-red-200 text-xs rounded-xl" x-text="error" style="display: none;"></div>
                    <div x-show="mensajeExito" class="p-4 bg-green-950/40 border border-green-800 text-green-200 text-xs rounded-xl" x-text="mensajeExito" style="display: none;"></div>

                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm space-y-6">
                        
                        <div x-show="!profesionalSeleccionado" class="text-center py-12 text-gray-400 text-xs italic">
                            Selecciona un profesional para ver sus modalidades de servicio y turnos libres.
                        </div>

                        <div x-show="profesionalSeleccionado" style="display: none;" class="space-y-6">
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block">Profesional:</span>
                                <h4 class="text-lg font-extrabold text-gray-900 dark:text-white" x-text="profesionalSeleccionado?.nombre"></h4>
                            </div>

                            <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                                <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block mb-2.5">1. Selecciona el servicio y modalidad:</span>
                                <div class="space-y-2">
                                    <template x-for="srv in profesionalSeleccionado?.servicios" :key="srv.id">
                                        <button @click="seleccionarServicio(srv)" :class="servicioSeleccionado?.id === srv.id ? 'border-2 border-blue-500 bg-blue-50/10' : 'border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40'" class="w-full p-3 rounded-xl text-left flex justify-between items-center transition-all">
                                            <div>
                                                <span class="font-bold text-sm block text-gray-900 dark:text-white" x-text="srv.nombre"></span>
                                                <span :class="srv.modalidad === 'Virtual' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'" class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase mt-1 inline-block" x-text="srv.modalidad"></span>
                                            </div>
                                            <span class="font-bold text-sm text-gray-900 dark:text-white" x-text="'$' + srv.precio"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div x-show="servicioSeleccionado" class="border-t border-gray-100 dark:border-gray-700 pt-4" style="display: none;">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block">2. Horarios libres (7 días rodantes):</span>
                                    <div class="flex gap-1">
                                        <button @click="retrocederSemana()" class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs font-bold hover:bg-gray-200"><-</button>
                                        <button @click="avanzarSemana()" class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs font-bold hover:bg-gray-200">-></button>
                                    </div>
                                </div>

                                <div x-show="cargandoAgenda" class="text-center py-6 text-xs text-gray-400 italic">Buscando turnos libres...</div>

                                <div x-show="!cargandoAgenda" class="space-y-2 max-h-[300px] overflow-y-auto pr-1">
                                    <template x-for="dia in semana" :key="dia.fecha">
                                        <div class="p-2 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-gray-100 dark:border-gray-700">
                                            <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-1 mb-1.5">
                                                <span :class="dia.es_hoy ? 'text-blue-600 dark:text-blue-400 font-extrabold' : 'text-gray-600 dark:text-gray-400 font-bold'" class="text-[11px] uppercase tracking-wider" x-text="dia.nombre_dia"></span>
                                                <span class="text-[10px] text-gray-400" x-text="dia.fecha_formateada"></span>
                                            </div>

                                            <template x-if="!dia.es_laboral">
                                                <div class="text-[10px] text-center text-red-400 italic py-0.5" x-text="dia.motivo_cierre ? ' Cerrado: ' + dia.motivo_cierre : 'No atiende'"></div>
                                            </template>

                                            <div class="grid grid-cols-4 gap-1" x-show="dia.es_laboral">
                                                <template x-for="slot in dia.bloques" :key="slot.hora">
                                                    <button 
                                                        @click="reservarTurno(dia.fecha, slot.hora, slot.ocupado)" :disabled="slot.ocupado" 
                                                        :class="slot.ocupado 
                                                            ? 'bg-gray-200 dark:bg-gray-800 text-gray-400 dark:text-gray-600 border-transparent cursor-not-allowed line-through' 
                                                            : 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-800 hover:bg-blue-600 hover:text-white'" 
                                                        class="p-1 text-[11px] font-bold rounded-lg border text-center transition">
                                                        <span x-text="slot.hora"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                        </div>
                    </div>
                </aside>

            </div>
        </div>
    </div>
</x-app-layout>