<x-app-layout>
    <div
        x-data="gestionServicios"
        class="min-h-screen flex bg-slate-950 text-white overflow-x-hidden">

        <!-- Sidebar -->
        <x-app-sidebar />

        <!-- Contenido principal -->
        <main class="flex-1 w-full min-w-0 px-4 sm:px-6 lg:px-12 py-6 lg:py-10 md:ml-20">
            
            <!-- Título -->
            <div class="mb-8 lg:mb-10">
                <h2 class="font-serif text-3xl sm:text-4xl lg:text-5xl leading-none tracking-tight text-white">
                    Mis Servicios Ofrecidos
                </h2>
            </div>

            <div class="max-w-7xl space-y-6">
                
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-800 gap-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Configuración del Catálogo</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Agrega o edita los servicios que los clientes podrán buscar y reservar en tu agenda.</p>
                </div>
                <div class="flex gap-3">
                    <button @click="abrirModalPolitica()" data-requires-online class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition shadow-sm">
                        Política de Cancelación
                    </button>
                    <button @click="abrirModalCrear()" data-requires-online class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition shadow-sm">>
                         Nuevo Servicio
                    </button>
                </div>
            </div>

            <div x-show="error" class="p-4 bg-red-950/40 border border-red-800 text-red-200 text-sm rounded-xl" x-text="error" style="display: none;"></div>
            <div x-show="mensajeExito" class="p-4 bg-green-950/40 border border-green-800 text-green-200 text-sm rounded-xl" x-text="mensajeExito" style="display: none;"></div>

            <div x-show="cargando" class="text-center py-12 text-gray-500 italic font-serif text-lg">
                Sincronizando tus servicios autorizados...
            </div>

            <div x-show="!cargando" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" style="display: none;">
                <template x-for="servicio in servicios" :key="servicio.id">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm flex flex-col justify-between min-h-[200px]">
                        <div>
                            <div class="flex justify-between items-start gap-4">
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white" x-text="servicio.nombre"></h4>
                                <span :class="servicio.modalidad === 'Virtual' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'" class="text-[10px] font-extrabold px-2 py-0.5 rounded uppercase tracking-wider" x-text="servicio.modalidad"></span>
                            </div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" x-text="'Duración: ' + servicio.duracion + ' min + ' + (servicio.bufferEntreTurnos ?? 0) + ' min descanso'"></p>
                            
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-3 line-clamp-2 italic" x-text="servicio.descripcion ?? 'Sin descripción añadida.'"></p>
                        </div>

                        <div class="mt-6 flex items-center justify-between border-t border-gray-100 dark:border-gray-700 pt-3">
                            <span class="text-xl font-serif font-extrabold text-gray-900 dark:text-white" x-text="'$' + servicio.precio"></span>
                            <div class="flex gap-2">
                                <button @click="abrirModalEditar(servicio)" data-requires-online class="px-3 py-1.5 text-xs font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 rounded-xl transition">
                                    Editar
                                </button>
                                <button @click="eliminarServicio(servicio.id)" data-requires-online class="px-3 py-1.5 text-xs font-bold text-red-600 dark:text-red-400 bg-red-50/50 dark:bg-red-950/20 hover:bg-red-100 rounded-xl transition">
                                    Borrar
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <template x-if="!cargando && servicios.length === 0">
                <div class="p-12 bg-white dark:bg-gray-800 text-center rounded-2xl text-sm text-gray-400 border border-dashed border-gray-300 dark:border-gray-700">
                    Todavía no has creado ningún servicio. Haz clic en "Nuevo Servicio" arriba para inaugurar tu catálogo.
                </div>
            </template>

            <div x-show="mostrarModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" @keydown.escape.window="mostrarModal = false">
                <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-200 dark:border-gray-700 animate-in fade-in zoom-in-95 duration-150" @click.away="mostrarModal = false">
                    
                    <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-3 mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white" x-text="modoEdicion ? 'Editar Servicio' : 'Registrar Nuevo Servicio'"></h3>
                        <button @click="mostrarModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl">&times;</button>
                    </div>

                    <form @submit.prevent="guardarServicio()" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Nombre del servicio:</label>
                            <input type="text" x-model="form.nombre" placeholder="Ej: Consultoría TI Avanzada" class="w-full p-2.5 rounded-xl border border-gray-300 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Categoría del Servicio:</label>
                            <select x-model="form.categoria_id" class="w-full p-2.5 rounded-xl border border-gray-300 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500" required>
                                <option value="" disabled selected>Selecciona una categoría...</option>
                                <template x-for="cat in categorias" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.nombre"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Descripción / Qué incluye:</label>
                            <textarea x-model="form.descripcion" rows="2" class="w-full p-2.5 rounded-xl border border-gray-300 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Precio ($):</label>
                                <input type="number" x-model="form.precio" class="w-full p-2.5 rounded-xl border border-gray-300 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Modalidad:</label>
                                <select x-model="form.modalidad" @change="cambiarModalidad()" class="w-full p-2.5 rounded-xl border border-gray-300 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500">
                                    <option value="Virtual">Virtual</option>
                                    <option value="Presencial">Presencial</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Duración (min):</label>
                                <select x-model="form.duracion" class="w-full p-2.5 rounded-xl border border-gray-300 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500">
                                    <option value="30">30 min</option>
                                    <option value="60">1 hora</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Descanso posterior (min):</label>
                                <select x-model="form.bufferEntreTurnos" class="w-full p-2.5 rounded-xl border border-gray-300 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500">
                                    <option value="0">Sin descanso</option>
                                    <option value="15">15 min</option>
                                </select>
                            </div>
                        </div>

                        <template x-if="form.modalidad === 'Presencial'">
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 space-y-4">
                                <h4 class="text-sm font-bold text-blue-600 dark:text-blue-400">Ubicación del Consultorio/Local</h4>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Nombre del Lugar:</label>
                                        <input type="text" x-model="form.lugar_nombre" placeholder="Ej: Clínica Central" class="w-full p-2.5 rounded-xl border border-gray-300 dark:bg-gray-900 text-white focus:ring-blue-500" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Dirección:</label>
                                        <input type="text" x-model="form.lugar_direccion" placeholder="Ej: 18 de Julio 1234" class="w-full p-2.5 rounded-xl border border-gray-300 dark:bg-gray-900 text-white focus:ring-blue-500" required>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Ciudad:</label>
                                        <input type="text" x-model="form.lugar_ciudad" class="w-full p-2.5 rounded-xl border border-gray-300 dark:bg-gray-900 text-white focus:ring-blue-500" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Departamento:</label>
                                        <input type="text" x-model="form.lugar_departamento" class="w-full p-2.5 rounded-xl border border-gray-300 dark:bg-gray-900 text-white focus:ring-blue-500" required>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Fija tu ubicación exacta en el mapa:</label>
                                    <div x-ref="mapaFormulario" class="w-full h-[200px] rounded-lg border border-gray-400 z-0 relative"></div>
                                    <p class="text-[10px] text-gray-400 mt-1">Haz clic en el mapa o arrastra el marcador azul para definir las coordenadas.</p>
                                </div>
                            </div>
                        </template>
                        <div class="flex justify-end gap-3 mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                            <button type="button" @click="mostrarModal = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition">Cancelar</button>
                            <button type="submit" data-requires-online class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition">
                                <span x-text="modoEdicion ? 'Guardar Cambios' : 'Crear Servicio'"></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <!-- Modal de Política de Cancelación -->
            <div x-show="modalPoliticaOpen" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" @keydown.escape.window="modalPoliticaOpen = false">
                <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-200 dark:border-gray-700" @click.away="modalPoliticaOpen = false">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-3 mb-4">Política de Cancelación</h3>
                    
                    <form @submit.prevent="guardarPolitica()">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Tiempo Mínimo (Horas):</label>
                                <input type="number" x-model="formPolitica.tiempo_minimo_cancelacion" min="0" class="w-full p-2.5 rounded-xl border border-gray-300 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500" required>
                                <p class="text-[10px] text-gray-400 mt-1">Horas de anticipación para que el cliente cancele o reprograme.</p>
                            </div>
                            <div>
                                <label class="flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                                    <input type="checkbox" x-model="formPolitica.permite_reprogramacion" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    Permitir reprogramaciones
                                </label>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Descripción:</label>
                                <textarea x-model="formPolitica.descripcion" rows="3" class="w-full p-2.5 rounded-xl border border-gray-300 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                            <button type="button" @click="modalPoliticaOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition">Cerrar</button>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>

                </div>
            </div>

        </main>
    </div>
</x-app-layout>
