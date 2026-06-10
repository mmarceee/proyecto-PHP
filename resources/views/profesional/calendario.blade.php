<x-app-layout>
    <div class="min-h-screen flex bg-slate-950 text-white overflow-x-hidden" x-data="calendarioProfesional()">
        <x-app-sidebar />
        <main class="flex-1 w-full min-w-0 px-4 sm:px-6 lg:px-12 py-6 lg:py-10 md:ml-20">
            <div class="max-w-4xl mx-auto">
                <div class="mb-8">
                    <h1 class="font-serif text-4xl sm:text-5xl leading-none tracking-tight">Calendario de Consultas</h1>
                    <p class="text-slate-400 mt-2">Aquí puedes visualizar y cancelar todas tus próximas sesiones confirmadas o pagadas.</p>
                </div>

                <div x-show="cargando" class="mb-6 rounded-lg border border-slate-700 bg-slate-900 p-4 text-sm text-slate-300">
                    Sincronizando con tu agenda...
                </div>

                <div x-show="!cargando" style="display: none;">
                    <div class="flex items-end justify-between border-b border-slate-400 pb-4 mb-8">
                        <h3 class="uppercase tracking-[0.25em] text-sm font-bold">Próximas Sesiones</h3>
                        <span class="font-serif italic text-slate-400 text-xl">
                            <span x-text="proximasSesiones.length"></span> confirmadas
                        </span>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
                        <template x-for="session in proximasSesiones" :key="session.id">
                            <article class="grid grid-cols-1 sm:grid-cols-[120px_minmax(0,1fr)] lg:grid-cols-[150px_minmax(0,1fr)_auto] items-start lg:items-center gap-4 px-4 sm:px-6 lg:px-8 py-6 lg:py-8 border-b border-slate-700 transition hover:bg-slate-800">
                                <div>
                                    <span class="font-serif text-2xl tracking-widest" x-text="session.date_label"></span>
                                    <p class="text-xs font-bold mt-1 text-slate-300"><span x-text="session.date"></span></p>
                                    <p class="text-xs font-bold mt-1 text-blue-400" x-text="session.time"></p>
                                </div>
                                <div>
                                    <h4 class="font-serif text-2xl sm:text-3xl break-words" x-text="session.client_name"></h4>
                                    <p class="text-sm text-slate-400 mt-1 break-all" x-text="session.client_email"></p>
                                    <p class="uppercase tracking-[0.25em] text-sm text-slate-400 mt-1">
                                        ▫ <span x-text="session.service_name"></span>
                                    </p>
                                </div>
                                <div class="flex flex-col items-center sm:items-end gap-3 sm:col-span-2 lg:col-span-1">
                                    <span class="px-3 py-0.5 rounded-md border border-slate-500 text-[10px] font-bold uppercase tracking-widest text-slate-300" x-text="session.status"></span>
                                    <button @click="abrirModalCancelacion(session.id)" class="px-4 py-2 rounded-md border border-red-500 text-red-500 hover:bg-red-500 hover:text-white transition text-xs font-bold uppercase tracking-wider">
                                        Cancelar
                                    </button>
                                </div>
                            </article>
                        </template>

                        <template x-if="proximasSesiones.length === 0">
                            <div class="px-8 py-8 text-sm text-slate-400">
                                No tienes próximas consultas aprobadas.
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Modal de Cancelación -->
            <div x-show="showCancelModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm">
                <div class="bg-slate-900 border border-slate-700 rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl" @click.away="cerrarModalCancelacion()">
                    <h3 class="text-xl font-serif text-white mb-2">Cancelar Consulta</h3>
                    <p class="text-sm text-slate-400 mb-5">Por favor, indica el motivo de la cancelación. Este mensaje será enviado al cliente para notificarle.</p>
                    <textarea x-model="motivoCancelacion" class="w-full bg-slate-800 border border-slate-600 rounded-md p-3 text-white text-sm focus:ring-red-500 focus:border-red-500 placeholder-slate-500 mb-5" rows="3" placeholder="Ej: Inconveniente personal de fuerza mayor..."></textarea>
                    <div class="flex justify-end gap-3">
                        <button @click="cerrarModalCancelacion()" class="px-4 py-2 rounded-md text-sm font-bold text-slate-300 hover:bg-slate-800 transition tracking-wide">VOLVER</button>
                        <button @click="confirmarCancelacion()" class="px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-bold transition tracking-wide shadow-lg">CONFIRMAR CANCELACIÓN</button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>
