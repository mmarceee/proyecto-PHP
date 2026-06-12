<x-app-layout>
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <x-app-sidebar/>
        
        {{-- Contenido principal --}}
        <main class="flex-1 min-w-0 lg:ml-20">
            <div class="border-b border-gray-700 bg-gray-800">
                <div class="max-w-7xl mx-auto pl-14 pr-6 py-6 sm:px-6 lg:px-8">
                    <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                        {{ __('Búsqueda de Servicios Profesionales') }}
                    </h2>
                </div>
            </div>

            <div
                class="py-12"
                x-data="busquedaServicios"
            >
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                    {{-- Buscador --}}
                    <div class="bg-white dark:bg-gray-800 overflow-visible shadow-sm sm:rounded-lg p-6">
                        <div class="max-w-2xl mx-auto">
                            <div class="flex shadow-xs rounded-base -space-x-0.5">
                                
                                <div class="relative shrink-0 w-56">
                                    <button
                                        type="button"
                                        @click="menuCategoriasAbierto = !menuCategoriasAbierto"
                                        class="w-full inline-flex items-center justify-between shrink-0 z-10 whitespace-nowrap
                                            text-gray-900 dark:text-white
                                            bg-gray-100 dark:bg-gray-700
                                            border border-gray-300 dark:border-gray-600
                                            px-4 py-2.5 rounded-s-lg
                                            hover:bg-gray-200 dark:hover:bg-gray-600
                                            focus:outline-none"
                                    >
                                        <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.143 4H4.857A.857.857 0 0 0 4 4.857v4.286c0 .473.384.857.857.857h4.286A.857.857 0 0 0 10 9.143V4.857A.857.857 0 0 0 9.143 4Zm10 0h-4.286a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286A.857.857 0 0 0 20 9.143V4.857A.857.857 0 0 0 19.143 4Zm-10 10H4.857a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286a.857.857 0 0 0 .857-.857v-4.286A.857.857 0 0 0 9.143 14Zm10 0h-4.286a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286a.857.857 0 0 0 .857-.857v-4.286a.857.857 0 0 0-.857-.857Z"/>
                                        </svg>

                                        <span x-text="categoriaSeleccionada"></span>

                                        <svg class="w-4 h-4 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    
                                    <div
                                        x-show="menuCategoriasAbierto"
                                        @click.away="menuCategoriasAbierto = false"
                                        class="absolute z-50 mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-48 p-1"
                                        style="display: none;"
                                    >
                                        <ul class="text-sm text-gray-900 dark:text-white">
                                            <template x-for="cat in categorias" :key="cat">
                                                <li>
                                                    <button
                                                        type="button"
                                                        @click="categoriaSeleccionada = cat; menuCategoriasAbierto = false"
                                                        class="w-full text-left px-3 py-2 rounded-lg text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-600"
                                                        x-text="cat"
                                                    ></button>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>

                                <input
                                    type="search"
                                    x-model.debounce.300ms="query"
                                    class="min-w-0 px-3 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-sm block w-full placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-white"
                                    placeholder="Buscar consultoría, entrenamiento, profesionales..."
                                    required
                                >
                                
                                <div class="inline-flex items-center text-white bg-blue-600 px-4 py-2.5 text-sm font-medium rounded-e-lg">
                                    <svg x-show="!cargando" class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
                                    </svg>

                                    <svg x-show="cargando" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" style="display: none;">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Layout principal --}}
                    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_380px] gap-8">

                        {{-- Columna izquierda: mapa + especialistas --}}
                        <section class="space-y-8">
                            
                            {{-- Especialistas encontrados --}}
                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm space-y-6">
                                <div class="space-y-4">
                                    <h3 class="text-xs uppercase tracking-wider font-extrabold text-gray-400">
                                        Especialistas Encontrados
                                    </h3>
                                    
                                    <div x-show="cargando && profesionales.length === 0" class="text-center py-8 text-gray-400 italic">
                                        Filtrando base de datos...
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-show="!cargando || profesionales.length > 0">
                                        <template x-for="profesional in profesionales" :key="profesional.id">
                                            <div
                                            :class="profesionalSeleccionado?.id === profesional.id ? 'border-2 border-blue-500 ring-2 ring-blue-500/10' : 'border border-gray-200 dark:border-gray-600'"
                                            class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow-sm flex flex-col justify-between transition-all"
                                            >
                                                <div>
                                                    <div class="flex justify-between items-start gap-3">
                                                        <div>
                                                            <h4 class="text-lg font-bold text-gray-900 dark:text-white" x-text="profesional.nombre"></h4>
                                                            
                                                            <!-- Calificación con Estrellas -->
                                                            <div class="flex items-center gap-1 mt-1 text-yellow-500">
                                                                <template x-if="profesional.reputacion_promedio > 0">
                                                                    <div class="flex items-center gap-0.5">
                                                                        <template x-for="i in 5">
                                                                            <svg class="w-4 h-4" :class="i <= Math.round(profesional.reputacion_promedio) ? 'fill-current' : 'text-gray-300 dark:text-gray-600'" viewBox="0 0 20 20" fill="currentColor">
                                                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                                            </svg>
                                                                        </template>
                                                                        <span class="text-xs font-semibold ml-1 text-gray-600 dark:text-gray-400" x-text="parseFloat(profesional.reputacion_promedio).toFixed(1)"></span>
                                                                    </div>
                                                                </template>
                                                                <template x-if="!profesional.reputacion_promedio || profesional.reputacion_promedio == 0">
                                                                    <span class="text-xs text-gray-400 dark:text-gray-500 italic">Sin calificaciones</span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        
                                                        <span
                                                        class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs font-medium px-2.5 py-0.5 rounded uppercase tracking-wider font-mono"
                                                        x-text="profesional.nombre_comercial ?? 'Independiente'"
                                                        ></span>
                                                    </div>
                                                    
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                        Servicios que ofrece:
                                                    </p>
                                                    
                                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                                        <template x-for="s in profesional.servicios" :key="s.id">
                                                            <span
                                                            class="text-[10px] font-bold px-2 py-0.5 rounded bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600"
                                                            x-text="s.nombre"
                                                            ></span>
                                                        </template>
                                                    </div>
                                                </div>
                                                
                                                <button
                                                type="button"
                                                @click="verDisponibilidad(profesional)"
                                                class="w-full mt-5 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition text-sm font-semibold tracking-wide"
                                                >
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
                            </div>

                            {{-- Mapa --}}
                            <div 
                                x-data="mapaBusqueda()" 
                                x-init="iniciarMapa()"
                                @filtrar-mapa.window="filtrarPorProfesional($event.detail)"
                                class="w-full h-[350px] rounded-lg shadow-md z-0 relative border border-gray-700 overflow-hidden"
                            >
                                <div x-ref="mapaBusqueda" class="w-full h-full rounded-lg"></div>
                            </div>
                        </section>
                        
                        {{-- Columna derecha: reserva de turno --}}
                        <aside class="space-y-4">
                            <h3 class="text-xs uppercase tracking-wider font-extrabold text-gray-400">
                                Reserva de Turno
                            </h3>
                            
                            <div
                            x-show="error"
                            class="p-4 bg-red-950/40 border border-red-800 text-red-200 text-xs rounded-xl"
                            x-text="error"
                                style="display: none;"
                            ></div>

                            <div
                                x-show="mensajeExito"
                                class="p-4 bg-green-950/40 border border-green-800 text-green-200 text-xs rounded-xl"
                                x-text="mensajeExito"
                                style="display: none;"
                            ></div>

                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm space-y-6">
                                
                                <div x-show="!profesionalSeleccionado" class="text-center py-12 text-gray-400 text-xs italic">
                                    Selecciona un profesional para ver sus modalidades de servicio y turnos libres.
                                </div>

                                <div x-show="profesionalSeleccionado" style="display: none;" class="space-y-6">
                                    <div>
                                        <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block">
                                            Profesional:
                                        </span>

                                        <h4
                                            class="text-lg font-extrabold text-gray-900 dark:text-white"
                                            x-text="profesionalSeleccionado?.nombre"
                                        ></h4>
                                    </div>

                                    <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                                        <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block mb-2.5">
                                            1. Selecciona el servicio y modalidad:
                                        </span>

                                        <div class="space-y-2">
                                            <template x-for="srv in profesionalSeleccionado?.servicios" :key="srv.id">
                                                <button
                                                    type="button"
                                                    @click="seleccionarServicio(srv)"
                                                    :class="servicioSeleccionado?.id === srv.id ? 'border-2 border-blue-500 bg-blue-50/10' : 'border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40'"
                                                    class="w-full p-3 rounded-xl text-left flex justify-between items-center transition-all"
                                                >
                                                    <div>
                                                        <span
                                                            class="font-bold text-sm block text-gray-900 dark:text-white"
                                                            x-text="srv.nombre"
                                                        ></span>

                                                        <span
                                                            :class="srv.modalidad === 'Virtual' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'"
                                                            class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase mt-1 inline-block"
                                                            x-text="srv.modalidad"
                                                        ></span>
                                                    </div>

                                                    <span
                                                        class="font-bold text-sm text-gray-900 dark:text-white"
                                                        x-text="'$' + srv.precio"
                                                    ></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <div x-show="servicioSeleccionado" class="border-t border-gray-100 dark:border-gray-700 pt-4" style="display: none;">
                                        <div class="flex justify-between items-center mb-3">
                                            <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block">
                                                2. Horarios libres (7 días rodantes):
                                            </span>

                                            <div class="flex gap-1">
                                                <button
                                                    type="button"
                                                    @click="retrocederSemana()"
                                                    class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs font-bold hover:bg-gray-200"
                                                >
                                                    <-
                                                </button>

                                                <button
                                                    type="button"
                                                    @click="avanzarSemana()"
                                                    class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs font-bold hover:bg-gray-200"
                                                >
                                                    ->
                                                </button>
                                            </div>
                                        </div>

                                        <div x-show="cargandoAgenda" class="text-center py-6 text-xs text-gray-400 italic">
                                            Buscando turnos libres...
                                        </div>

                                        <div x-show="!cargandoAgenda" class="space-y-2 max-h-[300px] overflow-y-auto pr-1">
                                            <template x-for="dia in semana" :key="dia.fecha">
                                                <div class="p-2 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-gray-100 dark:border-gray-700">
                                                    <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-1 mb-1.5">
                                                        <span
                                                            :class="dia.es_hoy ? 'text-blue-600 dark:text-blue-400 font-extrabold' : 'text-gray-600 dark:text-gray-400 font-bold'"
                                                            class="text-[11px] uppercase tracking-wider"
                                                            x-text="dia.nombre_dia"
                                                        ></span>

                                                        <span
                                                            class="text-[10px] text-gray-400"
                                                            x-text="dia.fecha_formateada"
                                                        ></span>
                                                    </div>

                                                    <template x-if="!dia.es_laboral">
                                                        <div
                                                            class="text-[10px] text-center text-red-400 italic py-0.5"
                                                            x-text="dia.motivo_cierre ? ' Cerrado: ' + dia.motivo_cierre : 'No atiende'"
                                                        ></div>
                                                    </template>

                                                    <div class="grid grid-cols-4 gap-1" x-show="dia.es_laboral">
                                                        <template x-for="slot in dia.bloques" :key="slot.hora">
                                                            <button 
                                                                type="button"
                                                                @click="prepararReserva(dia.fecha, slot.hora, slot.ocupado)"
                                                                :disabled="slot.ocupado" 
                                                                :class="slot.ocupado 
                                                                    ? 'bg-gray-200 dark:bg-gray-800 text-gray-400 dark:text-gray-600 border-transparent cursor-not-allowed line-through' 
                                                                    : 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-800 hover:bg-blue-600 hover:text-white'" 
                                                                class="p-1 text-[11px] font-bold rounded-lg border text-center transition"
                                                            >
                                                                <span x-text="slot.hora"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                 <!-- Opiniones de otros clientes -->
                                    <div class="border-t border-gray-100 dark:border-gray-700 pt-4 mt-4">
                                        <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block mb-3">
                                            3. Opiniones de otros clientes:
                                        </span>
                                        <div class="space-y-3 max-h-[250px] overflow-y-auto pr-1">
                                            <template x-for="resena in resenas" :key="resena.id">
                                                <div class="p-3 bg-gray-50 dark:bg-gray-900/60 rounded-xl border border-gray-100 dark:border-gray-700">
                                                    <div class="flex justify-between items-start mb-1">
                                                        <span class="text-xs font-bold text-gray-900 dark:text-white" x-text="resena.cliente_nombre"></span>
                                                        <div class="flex items-center text-yellow-500 gap-0.5">
                                                            <template x-for="i in 5">
                                                                <svg class="w-3 h-3" :class="i <= resena.puntuacion ? 'fill-current' : 'text-gray-300 dark:text-gray-600'" viewBox="0 0 20 20" fill="currentColor">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                                </svg>
                                                            </template>
                                                        </div>
                                                    </div>
                                                    <p class="text-xs text-gray-600 dark:text-gray-300 italic" x-text="resena.comentario ?? 'Sin comentario'"></p>
                                                    <span class="text-[9px] text-gray-400 block mt-1" x-text="resena.fecha"></span>
                                                </div>
                                            </template>
                                            <template x-if="resenas.length === 0">
                                                <p class="text-xs text-gray-400 dark:text-gray-500 italic py-1">Este profesional aún no tiene opiniones de clientes.</p>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>

                {{-- MODAL DE CONFIRMACIÓN DE RESERVA --}}
                <div x-show="showConfirmModal" style="display: none;" x-cloak
                    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm">
                    
                    <div class="bg-slate-900 border border-slate-700 rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl" 
                        @click.away="cerrarModalReserva()">
                        
                        <h3 class="text-xl font-serif text-white mb-4">Confirmar Reserva</h3>
                        
                        <p class="text-sm text-slate-300 mb-6">
                            ¿Confirmas la reserva para el día <strong class="text-white" x-text="fechaSeleccionada"></strong> a las <strong class="text-white"><span x-text="horaSeleccionada"></span> hs</strong>?
                        </p>
                        
                        <div 
                            x-show="paqueteDisponible" 
                            x-transition
                            class="mt-6 p-4 bg-blue-950/40 border border-blue-800/60 rounded-xl flex items-start gap-4 shadow-inner" 
                            style="display: none;"
                        >
                            <div class="bg-blue-900/50 p-2 rounded-lg text-blue-400 border border-blue-700/50">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>

                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-blue-300 uppercase tracking-wide">¡Beneficio disponible!</h4>
                                <p class="text-sm text-gray-300 mt-1">
                                    Tienes activo el <strong class="text-white" x-text="paqueteDisponible?.nombre"></strong>. 
                                    Te quedan <strong class="text-white bg-blue-900/50 px-1.5 py-0.5 rounded text-xs" x-text="paqueteDisponible?.disponibles"></strong> sesiones.
                                </p>
                                
                                <label class="flex items-center gap-3 mt-4 cursor-pointer group">
                                    <input 
                                        type="checkbox" 
                                        x-model="usarPaquete" 
                                        class="w-5 h-5 text-blue-600 bg-gray-900 border-gray-600 rounded focus:ring-blue-600 focus:ring-offset-gray-900 cursor-pointer transition"
                                    >
                                    <span class="text-sm font-semibold text-white group-hover:text-blue-300 transition">
                                        Usar 1 sesión para pagar este turno
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button 
                                @click="cerrarModalReserva()" 
                                class="px-4 py-2 rounded-md text-sm font-bold text-slate-400 hover:bg-slate-800 transition tracking-wide">
                                CANCELAR
                            </button>
                            <button 
                                @click="ejecutarReserva()" 
                                data-requires-online
                                class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold transition tracking-wide shadow-lg">
                                ACEPTAR
                            </button>
                        </div>
                    </div>
                </div>
                {{-- FIN DEL MODAL --}}

            </div>
        </main>
    </div>
</x-app-layout>
