<x-app-layout>
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <x-app-sidebar/>
        
        {{-- Contenido principal --}}
        <main class="flex-1 min-w-0 lg:ml-20">
           <div class="border-b border-gray-700 bg-gray-800">
                <div class="max-w-7xl mx-auto pl-14 pr-6 py-6 sm:px-6 lg:px-8">
                    <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                        {{ __('Gestión de Agenda y Disponibilidad') }}        
                    </h2>
                </div>
            </div>  
            <div class="py-12" x-data="agendaProfesional">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Panel del Profesional</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Define tus horarios, pausas y días no laborales.</p>
                        </div>
                        <div class="flex gap-3">
                            <button @click="bloquearDia(new Date().toISOString().split('T')[0])" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                                Bloquear Día (Excepción)
                            </button>
                            <button @click="mostrarModalReglas = true" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                Configurar Reglas Base
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
                        <div class="flex gap-2">
                            <button @click="semanaAnterior()" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-300 transition">
                                <- Seman. Anterior
                            </button>
                            <button @click="volverAHoy()" class="px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 text-blue-600 dark:text-blue-400 rounded-xl text-xs font-bold transition">
                                Volver a Hoy
                            </button>
                        </div>
                        <div class="text-sm font-serif italic text-gray-500 dark:text-gray-400">
                            Vista de 7 días
                        </div>
                        <div>
                            <button @click="semanaSiguiente()" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-300 transition">
                                Seman. Siguiente ->
                            </button>
                        </div>
                    </div>

                    <div x-show="error" class="mb-6 p-4 bg-red-950/40 border border-red-800 text-red-200 text-sm rounded-xl" x-text="error" style="display: none;"></div>
                    <div x-show="mensajeExito" class="mb-6 p-4 bg-green-950/40 border border-green-800 text-green-200 text-sm rounded-xl" x-text="mensajeExito" style="display: none;"></div>

                    <div x-show="cargando" class="text-center py-12 text-gray-500 italic font-serif text-lg">
                        Sincronizando calendario de flujo continuo...
                    </div>

                    <div x-show="!cargando" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-4" style="display: none;">
                        <template x-for="dia in semana" :key="dia.fecha">
                            <div :class="dia.es_hoy ? 'border-2 border-blue-500 ring-2 ring-blue-500/20' : 'border border-gray-200 dark:border-gray-700'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden flex flex-col justify-between min-h-[250px] transition-all duration-200">
                                
                                <div>
                                    <div :class="dia.es_hoy ? 'bg-blue-50 dark:bg-blue-950/40 border-b border-blue-200 dark:border-blue-900' : 'bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700'" class="p-3 text-center relative group">
                                        <span :class="dia.es_hoy ? 'text-blue-600 dark:text-blue-400 uppercase tracking-wider text-xs' : 'text-gray-900 dark:text-white'" class="block font-extrabold" x-text="dia.nombre_dia"></span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="dia.fecha_formateada"></span>
                                        
                                        <template x-if="!dia.tiene_excepcion">
                                            <button @click="bloquearDia(dia.fecha)" title="Bloquear este día específico" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity bg-red-950/40 hover:bg-red-900 p-1 rounded text-red-400 text-xs">
                                                Bloquear
                                            </button>
                                        </template>

                                        <template x-if="dia.tiene_excepcion">
                                            <button @click="confirmarDesbloqueo(dia.fecha)" title="Quitar excepción de este día" class="absolute top-2 right-2 bg-red-600 hover:bg-red-700 p-1 rounded text-white text-[10px] font-bold shadow-sm transition">
                                                Desbloquear
                                            </button>
                                        </template>
                                    </div>

                                    <template x-if="!dia.es_laboral">
                                        <div class="p-4 text-center bg-red-50/30 dark:bg-red-950/10 h-full flex flex-col items-center justify-center gap-3 min-h-[180px]">
                                            <span class="text-xs text-red-400 dark:text-red-300 font-serif italic font-bold" x-text="dia.motivo_cierre ? 'Cerrado: ' + dia.motivo_cierre : 'X - No laboral'"></span>
                                            
                                            <template x-if="dia.tiene_excepcion">
                                                <button @click="confirmarDesbloqueo(dia.fecha)" class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-red-600 dark:text-red-400 border border-gray-200 dark:border-gray-600 rounded-xl text-[10px] font-extrabold tracking-wide uppercase shadow-sm transition-all duration-150">
                                                    Desbloquear día
                                                </button>
                                            </template>
                                        </div>
                                    </template>

                                    <div class="p-2 space-y-2" x-show="dia.es_laboral">
                                        <template x-for="slot in dia.bloques" :key="slot.hora">
                                            <button :disabled="slot.ocupado" :class="slot.ocupado ? 'bg-gray-100 dark:bg-gray-700 text-gray-400' : 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-blue-100 dark:border-blue-800 hover:bg-blue-600 hover:text-white'" class="w-full p-2 text-xs font-semibold rounded-xl border flex flex-col items-center justify-center min-h-[44px]">
                                                <span class="text-sm font-bold" x-text="slot.hora"></span>
                                                <span x-show="slot.ocupado" class="text-[9px] uppercase tracking-wider font-extrabold text-red-400 mt-0.5">Ocupado</span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                            </div>
                        </template>
                    </div>

                    <div x-show="mostrarModalReglas" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" @keydown.escape.window="mostrarModalReglas = false">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-3xl w-full p-6 shadow-xl border border-gray-200 dark:border-gray-700" @click.away="mostrarModalReglas = false">
                            <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-3 mb-4">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Definir Disponibilidad Semanal</h3>
                                <button @click="mostrarModalReglas = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl">&times;</button>
                            </div>
                            <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-2">
                                <template x-for="regla in formReglas" :key="regla.dia_semana">
                                    <div class="grid grid-cols-1 lg:grid-cols-[120px_1fr] items-center gap-4 p-3 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-gray-100 dark:border-gray-700/60">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" x-model="regla.activo" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                            <span class="font-bold text-sm text-gray-900 dark:text-white" x-text="regla.nombre"></span>
                                        </label>
                                        <div class="flex flex-wrap items-center gap-3" x-show="regla.activo">
                                            <div class="flex items-center gap-1">
                                                <span class="text-[11px] text-gray-500">De:</span>
                                                <input type="time" x-model="regla.hora_inicio" class="p-1 text-xs rounded-lg border-gray-300 dark:bg-gray-900 text-gray-900 dark:text-white">
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <span class="text-[11px] text-gray-500">A:</span>
                                                <input type="time" x-model="regla.hora_fin" class="p-1 text-xs rounded-lg border-gray-300 dark:bg-gray-900 text-gray-900 dark:text-white">
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <span class="text-[11px] text-gray-500">Duración:</span>
                                                <select x-model="regla.duracion_turno" class="p-1 text-xs rounded-lg border-gray-300 dark:bg-gray-900 text-gray-900 dark:text-white">
                                                    <option value="15">15 min</option>
                                                    <option value="30">30 min</option>
                                                    <option value="45">45 min</option>
                                                    <option value="60">1 hora</option>
                                                    <option value="120">2 horas</option>
                                                </select>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <span class="text-[11px] text-gray-500">Descanso:</span>
                                                <select x-model="regla.buffer_tiempo" class="p-1 text-xs rounded-lg border-gray-300 dark:bg-gray-900 text-gray-900 dark:text-white">
                                                    <option value="0">Sin pausa</option>
                                                    <option value="5">5 min</option>
                                                    <option value="10">10 min</option>
                                                    <option value="15">15 min</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-400 italic" x-show="!regla.activo">No laboral / Cerrado</div>
                                    </div>
                                </template>
                            </div>
                            <div class="flex justify-end gap-3 mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                                <button type="button" @click="mostrarModalReglas = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Cancelar</button>
                                <button type="button" @click="guardarReglasBase()" :disabled="guardandoReglas" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 rounded-lg transition">
                                    <span x-text="guardandoReglas ? 'Guardando...' : 'Guardar Cambios'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div x-show="mostrarModalExcepcion" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" @click.away="mostrarModalExcepcion = false" @keydown.escape.window="mostrarModalExcepcion = false">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-3 mb-4">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Registrar Bloqueo de Día</h3>
                                <button @click="mostrarModalExcepcion = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl">&times;</button>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Fecha a bloquear:</label>
                                    <input type="date" x-model="formExcepcion.fecha" class="w-full p-2 rounded-lg border border-gray-300 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-red-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Tipo de bloqueo:</label>
                                    <select x-model="formExcepcion.tipo" class="w-full p-2 rounded-lg border border-gray-300 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-red-500">
                                        <option value="no_disponible">No Disponible (Asunto Personal)</option>
                                        <option value="licencia">Licencia / Vacaciones</option>
                                        <option value="feriado">Feriado / Día Festivo</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Motivo / Descripción (Opcional):</label>
                                    <input type="text" x-model="formExcepcion.motivo" placeholder="Ej: Médico enfermo, Vacaciones de invierno..." class="w-full p-2 rounded-lg border border-gray-300 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-red-500">
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                                <button @click="mostrarModalExcepcion = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Cancelar</button>
                                <button @click="guardarBloqueoDia()" :disabled="guardandoExcepcion" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 disabled:bg-red-400 rounded-lg transition">
                                    <span x-text="guardandoExcepcion ? 'Bloqueando...' : 'Confirmar Bloqueo'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div x-show="mostrarModalDesbloquear" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" @keydown.escape.window="mostrarModalDesbloquear = false">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-sm w-full p-6 shadow-xl border border-gray-200 dark:border-gray-700 animate-in fade-in zoom-in-95 duration-150" @click.away="mostrarModalDesbloquear = false">
                            <div class="text-center">
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-amber-50 dark:bg-amber-950/30 text-amber-500 mb-4 text-xl">!</div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">¿Restaurar Horarios?</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed px-2">¿Estás seguro de que quieres quitar el bloqueo de este día y restaurar tus horarios habituales de consultorio?</p>
                            </div>
                            <div class="flex justify-end gap-3 mt-6 border-t border-gray-100 dark:border-gray-700 pt-4">
                                <button @click="mostrarModalDesbloquear = false" class="px-4 py-2 text-xs font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition">Cancelar</button>
                                <button @click="desbloquearDia()" class="px-4 py-2 text-xs font-bold text-white bg-amber-500 hover:bg-amber-600 rounded-xl shadow-sm transition">Sí, desbloquear</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</x-app-layout>