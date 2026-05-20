<x-app-layout>
    @vite(['resources/js/agenda-profesional.js'])
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gestión de Agenda y Disponibilidad') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="agendaProfesional">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Panel del Profesional</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Define tus horarios, pausas y días no laborales.</p>
                </div>
                <div class="flex gap-3">
                    <button @click="bloquearDia(new Date().toISOString().split('T')[0])" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Bloquear Día (Excepción)
                    </button>
                    <button class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                        Configurar Reglas Base
                    </button>
                </div>
            </div>

            <div x-show="cargando" class="text-center py-12 text-gray-500 italic">
                Sincronizando calendario semanal...
            </div>

            <div x-show="!cargando" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-4" style="display: none;">
                <template x-for="dia in semana" :key="dia.fecha">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 text-center">
                            <span class="block font-bold text-gray-900 dark:text-white" x-text="dia.nombre_dia"></span>
                            <span class="text-xs text-gray-500 dark:text-gray-400" x-text="dia.fecha_formateada"></span>
                        </div>

                        <div class="p-2 space-y-2">
                            <template x-for="slot in dia.bloques" :key="slot.hora">
                                <div>
                                    <template x-if="slot.es_pausa">
                                        <div class="py-1 px-2 bg-yellow-50 dark:bg-yellow-900/20 border border-dashed border-yellow-300 dark:border-yellow-700 rounded-lg text-[10px] text-yellow-700 dark:text-yellow-400 text-center font-bold italic" x-text="slot.etiqueta || 'PAUSA'">
                                        </div>
                                    </template>

                                    <template x-if="!slot.es_pausa">
                                        <button 
                                            :disabled="slot.ocupado"
                                            :class="slot.ocupado 
                                                ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 border-gray-200 dark:border-gray-600 cursor-not-allowed' 
                                                : 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-blue-100 dark:border-blue-800 hover:bg-blue-600 hover:text-white'"
                                            class="w-full p-2 text-xs font-semibold rounded-xl border transition-all duration-200"
                                        >
                                            <span x-text="slot.hora"></span>
                                            <span x-show="slot.ocupado" class="block text-[9px] uppercase mt-0.5">Ocupado</span>
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-app-layout>