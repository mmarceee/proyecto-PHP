<x-app-layout>
    <div 
        x-data="solicitudesProfesionales()"
        x-init="cargarSolicitudes()"
        class="flex min-h-screen bg-slate-950 text-white"
    >
        {{-- Sidebar --}}
        <x-app-sidebar/>
        
        {{-- Contenido principal --}}
        <main class="flex-1 min-w-0 lg:ml-20">
            <div class="max-w-6xl mx-auto px-6 sm:px-8 lg:px-12 py-12">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-700 pb-4 mb-8 gap-4">
                    <h1 class="font-serif text-2xl sm:text-4xl tracking-tight">
                        Solicitudes Profesionales Pendientes
                    </h1>

                    <span class="bg-slate-800 border border-slate-700 text-xs px-3 py-1 font-bold rounded-full uppercase tracking-wider w-fit">
                        <span x-text="solicitudes.length"></span> Solicitudes
                    </span>
                </div>

                <template x-if="mensaje">
                    <div class="mb-6 p-4 bg-green-950/40 border border-green-800 text-green-200 text-sm rounded" x-text="mensaje"></div>
                </template>

                <template x-if="error">
                    <div class="mb-6 p-4 bg-red-950/40 border border-red-800 text-red-200 text-sm rounded" x-text="error"></div>
                </template>

                <template x-if="cargando">
                    <div class="border border-slate-800 bg-slate-900/40 p-12 text-center rounded-lg">
                        <p class="text-slate-400 font-serif italic text-lg">
                            Cargando solicitudes...
                        </p>
                    </div>
                </template>

                <template x-if="!cargando && solicitudes.length === 0">
                    <div class="border border-slate-800 bg-slate-900/40 p-12 text-center rounded-lg">
                        <p class="text-slate-400 font-serif italic text-lg">
                            No hay postulaciones pendientes de revisión por el momento.
                        </p>
                    </div>
                </template>

                <div class="grid grid-cols-1 gap-6" x-show="!cargando && solicitudes.length > 0">
                    <template x-for="solicitud in solicitudes" :key="solicitud.id">
                        <div class="border border-slate-800 bg-slate-900/60 p-6 sm:p-8 rounded-lg grid grid-cols-1 lg:grid-cols-[1fr_auto] items-center gap-6 transition hover:border-slate-700">

                            <div>
                                <span class="text-xs font-bold uppercase tracking-widest text-indigo-400" x-text="solicitud.especialidad"></span>

                                <h2 class="font-serif text-2xl sm:text-3xl mt-1" x-text="solicitud.name"></h2>

                                <div class="flex flex-wrap gap-4 text-xs text-slate-400 mt-2">
                                    <span x-show="solicitud.email">
                                        📧 <span x-text="solicitud.email"></span>
                                    </span>

                                    <span x-show="solicitud.telefono">
                                        📞 <span x-text="solicitud.telefono"></span>
                                    </span>

                                    <span x-show="solicitud.nombre_comercial">
                                        🏢 Comercial:
                                        <strong x-text="solicitud.nombre_comercial"></strong>
                                    </span>
                                </div>

                                <p 
                                    x-show="solicitud.descripcion"
                                    class="text-sm text-slate-300 mt-4 border-l-2 border-slate-700 pl-4 italic"
                                >
                                    “<span x-text="solicitud.descripcion"></span>”
                                </p>
                            </div>

                            <div class="flex flex-col sm:flex-row lg:flex-col gap-3 w-full lg:w-44">
                                <button 
                                    type="button"
                                    @click="aceptar(solicitud.id)"
                                    class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold uppercase tracking-wider text-xs py-3 rounded transition"
                                >
                                    Aceptar
                                </button>

                                <button 
                                    type="button"
                                    @click="rechazar(solicitud.id)"
                                    class="w-full border border-red-800 text-red-400 font-bold uppercase tracking-wider text-xs py-3 rounded hover:bg-red-950/30 transition"
                                >
                                    Rechazar
                                </button>
                            </div>

                        </div>
                    </template>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>