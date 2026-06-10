<x-app-layout>
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <x-app-sidebar/>
        
        {{-- Contenido principal --}}
        <main x-data="paquetesProfesional" class="flex-1 min-w-0 lg:ml-20 bg-gray-900 text-gray-100">
            <div class="border-b border-gray-700 bg-gray-800">
                <div class="max-w-7xl mx-auto pl-14 pr-6 py-6 sm:px-6 lg:px-8 flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                        {{ __('Catálogo de Paquetes de Sesiones') }}
                    </h2>
                    
                    <button 
                        @click="abrirModalCrear()"
                        data-requires-online
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl text-sm transition tracking-wide shadow-lg flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Crear Paquete
                    </button>
                </div>
            </div>

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                    {{-- Notificaciones de Éxito o Error generales --}}
                    <div x-show="mensajeExito" x-transition class="p-4 bg-green-950/40 border border-green-800 text-green-200 text-sm rounded-xl" x-text="mensajeExito" style="display: none;"></div>
                    <div x-show="error && !mostrarModal" x-transition class="p-4 bg-red-950/40 border border-red-800 text-red-200 text-sm rounded-xl" x-text="error" style="display: none;"></div>

                    {{-- Estado de carga --}}
                    <div x-show="cargando" class="text-center py-12 text-gray-400 italic">
                        Cargando catálogo de paquetes...
                    </div>

                    {{-- Vista vacía --}}
                    <template x-if="!cargando && paquetes.length === 0">
                        <div class="p-12 text-center bg-gray-800 border border-dashed border-gray-700 rounded-2xl text-gray-400">
                            No tienes paquetes creados en tu catálogo todavía. ¡Crea el primero para ofrecerle a tus clientes!
                        </div>
                    </template>

                    {{-- Grilla de Paquetes --}}
                    <div x-show="!cargando && paquetes.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" style="display: none;">
                        <template x-for="paquete in paquetes" :key="paquete.id">
                            <div 
                                class="bg-gray-800 border border-gray-700 rounded-2xl p-6 shadow-sm flex flex-col justify-between transition-all"
                                :class="!paquete.activo ? 'opacity-60 bg-gray-800/50' : ''"
                            >
                                <div>
                                    <div class="flex justify-between items-start gap-3 mb-2">
                                        <h3 class="text-lg font-bold text-white" x-text="paquete.nombre"></h3>
                                        <span 
                                            class="text-xs font-bold font-mono px-2 py-0.5 rounded-md uppercase"
                                            :class="paquete.activo ? 'bg-green-900/50 text-green-300 border border-green-700' : 'bg-gray-700 text-gray-400'"
                                            x-text="paquete.activo ? 'Activo' : 'Pausado'"
                                        ></span>
                                    </div>

                                    <span class="text-xs text-blue-400 font-medium uppercase tracking-wider block mb-3" x-text="paquete.servicio?.nombre"></span>
                                    
                                    <p class="text-sm text-gray-400 mb-4 line-clamp-3" x-text="paquete.descripcion ?? 'Sin descripción.'"></p>
                                    
                                    <div class="grid grid-cols-2 gap-2 bg-gray-900/50 p-3 rounded-xl border border-gray-700/50 text-xs mb-6">
                                        <div>
                                            <span class="text-gray-500 block">Sesiones:</span>
                                            <strong class="text-white text-sm" x-text="paquete.cantidad_sesiones"></strong>
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

                                    {{-- Botón para pausar/activar --}}
                                    <button 
                                        type="button"
                                        @click="toggleActivo(paquete)"
                                        data-requires-online
                                        class="text-xs font-bold px-3 py-1.5 rounded-lg border transition-all"
                                        :class="paquete.activo 
                                            ? 'border-amber-700 text-amber-400 hover:bg-amber-950/30' 
                                            : 'border-blue-700 text-blue-400 hover:bg-blue-950/30'"
                                        x-text="paquete.activo ? 'Pausar Venta' : 'Activar Venta'"
                                    ></button>
                                </div>
                            </div>
                        </template>
                    </div>

                </div>

                {{-- MODAL OSCURO CON BLUR PARA CREAR PAQUETE --}}
                <div 
                    x-show="mostrarModal" 
                    style="display: none;" 
                    x-cloak
                    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm"
                >
                    <div 
                        class="bg-slate-900 border border-slate-700 rounded-2xl p-6 max-w-lg w-full mx-4 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto"
                        @click.away="mostrarModal = false"
                    >
                        <div class="flex justify-between items-center border-b border-slate-700 pb-3">
                            <h3 class="text-lg font-bold text-white">Crear Nuevo Paquete</h3>
                            <button @click="mostrarModal = false" class="text-slate-400 hover:text-white">&times;</button>
                        </div>

                        {{-- Errores dentro del modal --}}
                        <div x-show="error" class="p-3 bg-red-950/40 border border-red-800 text-red-200 text-xs rounded-xl" x-text="error"></div>

                        <form @submit.prevent="guardarPaquete" class="space-y-4 text-sm">
                            {{-- Seleccionar Servicio --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-400 mb-1">Servicio Vinculado *</label>
                                <select 
                                    x-model="form.servicio_id" 
                                    required
                                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">Selecciona un servicio...</option>
                                    <template x-for="srv in servicios" :key="srv.id">
                                        <option :value="srv.id" x-text="srv.nombre"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Nombre del Paquete --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-400 mb-1">Nombre del Paquete *</label>
                                <input 
                                    type="text" 
                                    x-model="form.nombre" 
                                    placeholder="Ej: Pack de 4 Sesiones de Entrenamiento" 
                                    required
                                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white placeholder-slate-500 focus:ring-blue-500 focus:border-blue-500"
                                >
                            </div>

                            {{-- Descripción --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-400 mb-1">Descripción</label>
                                <textarea 
                                    x-model="form.descripcion" 
                                    rows="2" 
                                    placeholder="Detalla qué incluye el paquete o alguna condición..."
                                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white placeholder-slate-500 focus:ring-blue-500 focus:border-blue-500"
                                ></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                {{-- Cantidad de Sesiones --}}
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-400 mb-1">Cant. Sesiones *</label>
                                    <input 
                                        type="number" 
                                        x-model.number="form.cantidad_sesiones" 
                                        min="2" 
                                        required
                                        class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </div>

                                {{-- Validez en meses --}}
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-400 mb-1">Validez (Meses) *</label>
                                    <input 
                                        type="number" 
                                        x-model.number="form.validez_meses" 
                                        min="1" 
                                        required
                                        class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </div>
                            </div>

                            {{-- Precio Total --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-400 mb-1">Precio Total del Paquete ($) *</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    x-model="form.precio" 
                                    placeholder="0.00" 
                                    required
                                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-white placeholder-slate-500 focus:ring-blue-500 focus:border-blue-500"
                                >
                            </div>

                            {{-- Botones del Modal --}}
                            <div class="flex justify-end gap-3 border-t border-slate-700 pt-3 mt-4">
                                <button 
                                    type="button" 
                                    @click="mostrarModal = false" 
                                    class="px-4 py-2 rounded-xl text-sm font-bold text-slate-400 hover:bg-slate-800 transition tracking-wide"
                                >
                                    CANCELAR
                                </button>
                                <button 
                                    type="submit" 
                                    :disabled="guardando"
                                    data-requires-online
                                    class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold transition tracking-wide shadow-lg flex items-center gap-2"
                                >
                                    <span x-show="guardando" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                                    <span x-text="guardando ? 'GUARDANDO...' : 'GUARDAR PAQUETE'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                {{-- FIN DEL MODAL --}}

            </div>
        </main>
    </div>
</x-app-layout>
