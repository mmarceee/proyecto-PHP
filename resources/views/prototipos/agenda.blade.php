<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gestión de Agenda y Disponibilidad') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header de Acciones Rápidas (Requerimiento: Excepciones) -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Panel del Profesional</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Define tus horarios, pausas y días no laborales.</p>
                </div>
                <div class="flex gap-3">
                    <button class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Bloquear Día (Excepción)
                    </button>
                    <button class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                        Configurar Reglas Base
                    </button>
                </div>
            </div>

            <!-- Grid de Agenda Semanal (Diseño Responsivo) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-4">
                
                @php
                    $dias = ['Lunes 11', 'Martes 12', 'Miércoles 13', 'Jueves 14', 'Viernes 15', 'Sábado 16', 'Domingo 17'];
                    $slots = ['08:00', '09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00'];
                @endphp

                @foreach($dias as $index => $dia)
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <!-- Cabecera del Día -->
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 text-center">
                        <span class="block font-bold text-gray-900 dark:text-white">{{ explode(' ', $dia)[0] }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ explode(' ', $dia)[1] }} de Mayo</span>
                    </div>

                    <!-- Cuerpo de la Agenda -->
                    <div class="p-2 space-y-2">
                        @foreach($slots as $hora)
                            @php
                                // Mockup de estados para el prototipo visual
                                $estaReservado = ($index == 0 && $hora == '09:00'); 
                                $esPausa = ($hora == '12:00');
                            @endphp

                            @if($esPausa)
                                <!-- Requerimiento: Pausas/Buffers -->
                                <div class="py-1 px-2 bg-yellow-50 dark:bg-yellow-900/20 border border-dashed border-yellow-300 dark:border-yellow-700 rounded-lg text-[10px] text-yellow-700 dark:text-yellow-400 text-center font-bold italic">
                                    ALMUERZO / PAUSA
                                </div>
                            @else
                                <button class="w-full p-2 text-xs font-semibold rounded-xl border transition-all duration-200
                                    {{ $estaReservado 
                                        ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 border-gray-200 dark:border-gray-600 cursor-not-allowed' 
                                        : 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-blue-100 dark:border-blue-800 hover:bg-blue-600 hover:text-white' }}">
                                    {{ $hora }}
                                    @if($estaReservado)
                                        <span class="block text-[9px] uppercase mt-0.5">Ocupado</span>
                                    @endif
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Footer Informativo para el Análisis de Visión -->
            <div class="mt-8 p-6 bg-indigo-50 dark:bg-indigo-900/20 border-l-4 border-indigo-500 rounded-r-xl">
                <h4 class="text-indigo-900 dark:text-indigo-300 font-bold flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                    Notas de Implementación Arquitectónica:
                </h4>
                <ul class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-sm text-indigo-800 dark:text-indigo-400">
                    <li>• <strong>Control de Concurrencia:</strong> Implementado mediante bloqueos en la base de datos (Pessimistic Locking)[cite: 2, 3].</li>
                    <li>• <strong>Buffers entre turnos:</strong> Visualizados como bloques no reservables para asegurar descanso del profesional[cite: 2, 3].</li>
                    <li>• <strong>Excepciones:</strong> El sistema permite sobrescribir la regla base para fechas específicas (feriados o licencias)[cite: 2, 3].</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>