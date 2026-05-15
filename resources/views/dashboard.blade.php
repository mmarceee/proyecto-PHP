@php
    $profesional = Auth::user()?->profesional;
@endphp
<x-app-layout>
    <div
    x-data="{
        selectedClient: 'maria',

        clients: {
            maria: {
                name: 'Maria G.',
                packages: [
                    { name: 'Flujo Premium', used: 2, total: 8 },
                    { name: 'Plan Base', used: 1, total: 5 },
                    { name: 'Consulta Inicial', used: 1, total: 1 }
                ]
            },

            carlos: {
                name: 'Carlos T.',
                packages: [
                    { name: 'Seguimiento Mensual', used: 5, total: 5 },
                    { name: 'Plan Base', used: 3, total: 5 },
                    { name: 'Asesoría Técnica', used: 2, total: 4 }
                ]
            }
        }
    }"
    class="min-h-screen flex bg-slate-950 text-white">

        <!-- Sidebar -->
       <aside class="sticky top-16 self-start w-20 h-[calc(100vh-4rem)] border-r border-slate-700 bg-slate-900 flex flex-col items-center justify-between py-8">

            <!-- Parte superior de la sidebar -->
            <div class="flex flex-col items-center gap-8">
                <!-- Logo chico -->
                <a href="{{ route('dashboard') }}"
                   class="w-9 h-9 border border-slate-300 rounded-md flex items-center justify-center text-sm font-semibold hover:bg-slate-800">
                   <img src="{{ asset('gendarSinFondo.png') }}" class="w-10 h-10 object-contain" alt="Logo">
                </a>

                <!-- Botones de navegación -->
                <nav class="flex flex-col items-center gap-8">
                    <a href="{{ route('dashboard') }}"
                       class="w-10 h-10 rounded-lg border border-slate-400 flex items-center justify-center text-white hover:bg-slate-800">
                        <!-- Calendario -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/>
                        </svg>
                    </a>

                    <a href="/prototipo/busqueda"
                       class="w-10 h-10 rounded-lg border border-slate-400 flex items-center justify-center text-white hover:bg-slate-800">
                        <!-- Busqueda -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </a>
                    <!-- Agenda -->
                    @if(Auth::user()->puedeAccederPanelProfesional())
                        <a href="/prototipo/agenda"
                        class="w-10 h-10 rounded-lg flex items-center justify-center text-white hover:bg-slate-800">
                            <svg xmlns="http://www.w3.org/2000/svg" 
                                class="w-5 h-5" 
                                fill="none" 
                                viewBox="0 0 24 24" 
                                stroke="currentColor">
                                <path stroke-linecap="round" 
                                    stroke-linejoin="round" 
                                    stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 5h6M9 12h6M9 16h6M7 12h.01M7 16h.01"/>
                            </svg>
                        </a>
                    @endif
                </nav>
            </div>

            <!-- Perfil abajo -->
            <div>
                <a href="{{ route('profile') }}"
                   class="block w-10 h-10 rounded-lg overflow-hidden border border-slate-500 hover:ring-2 hover:ring-blue-500">
                    <img
                        src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=1e293b&color=fff"
                        alt="Perfil"
                        class="w-full h-full object-cover"
                    >
                </a>
            </div>

        </aside>

        <!-- Contenido principal -->
        <main class="flex-1 px-12 py-10">
            <div class="grid grid-cols-1 xl:grid-cols-[1fr_365px] gap-12">

                <!-- Columna izquierda -->
                <section>
                    <div class="mb-16">
                        <h1 class="font-serif text-6xl leading-none tracking-tight">
                            Buenos
                        </h1>

                        <p class="font-serif italic text-6xl leading-none text-slate-400">
                            Días,
                        </p>

                        <h2 class="font-serif text-6xl leading-none tracking-tight">
                            Profesional.
                        </h2>
                    </div>

                    <div>
                        <div class="flex items-end justify-between border-b border-slate-400 pb-4 mb-8">
                            <h3 class="uppercase tracking-[0.25em] text-sm font-bold">
                                Próximas sesiones
                            </h3>

                            <span class="font-serif italic text-slate-400 text-xl">
                                2 hoy
                            </span>
                        </div>

                        <div class="bg-slate-900 border border-slate-800">

                            <article 
                                @click="selectedClient = 'maria'"
                                :class="selectedClient === 'maria' ? 'ring-1 ring-blue-500 bg-slate-800' : 'bg-slate-900'"
                                class="cursor-pointer grid grid-cols-[130px_1fr_auto] items-center gap-4 px-8 py-8 border-b border-slate-300 transition">
                                <div>
                                    <span class="font-serif text-4xl tracking-widest">10:00</span>
                                    <span class="text-xs font-bold ml-1">AM</span>
                                </div>

                                <div>
                                    <h4 class="font-serif text-3xl">Maria G.</h4>
                                    <p class="uppercase tracking-[0.25em] text-sm text-slate-400 mt-1">
                                        ▫ Consulta inicial
                                    </p>
                                </div>

                                <div class="flex items-center gap-4">
                                    <span class="px-4 py-1 rounded-md bg-blue-700/70 text-xs font-bold uppercase tracking-wider text-blue-100">
                                        Por comenzar
                                    </span>

                                    <a href="#"
                                       class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-xs font-bold uppercase tracking-wider">
                                        Enlace
                                    </a>
                                </div>
                            </article>

                            <article 
                                @click="selectedClient = 'carlos'"
                                :class="selectedClient === 'carlos' ? 'ring-1 ring-blue-500 bg-slate-800' : 'bg-slate-900'"
                                class="cursor-pointer grid grid-cols-[130px_1fr_auto] items-center gap-4 px-8 py-8 border-b border-slate-300 transition">
                                <div>
                                    <span class="font-serif text-4xl tracking-widest">12:30</span>
                                    <span class="text-xs font-bold ml-1">PM</span>
                                </div>

                                <div>
                                    <h4 class="font-serif text-3xl">Carlos T.</h4>
                                    <p class="uppercase tracking-[0.25em] text-sm text-slate-400 mt-1">
                                        ▫ Seguimiento
                                    </p>
                                </div>

                                <div class="flex items-center gap-4">
                                    <span class="px-4 py-1 rounded-md border border-white text-xs font-bold uppercase tracking-wider">
                                        Confirmada
                                    </span>

                                    <a href="#"
                                       class="px-4 py-2 rounded-md border border-slate-300 hover:bg-slate-800 text-xs font-bold uppercase tracking-wider">
                                        Detalles
                                    </a>
                                </div>
                            </article>

                        </div>
                    </div>
                </section>

                <!-- Columna derecha -->
                <aside class="xl:pt-32">
                    <div class="border border-slate-300 rounded-lg p-6 bg-slate-900/60">
                        <h3 class="uppercase tracking-[0.18em] text-sm font-bold mb-3">
                            Paquetes disponibles
                        </h3>

                        <div class="border-b border-slate-400 mb-6"></div>

                        <div class="mb-6">
                            <p class="text-xs uppercase tracking-[0.25em] text-slate-400 mb-1">
                                Cliente seleccionado
                            </p>

                            <h4 class="font-serif text-2xl" x-text="clients[selectedClient].name"></h4>
                        </div>

                        <template x-for="package in clients[selectedClient].packages" :key="package.name">
                            <div class="mb-8">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-serif text-xl" x-text="package.name"></h4>

                                    <span 
                                        class="text-xs text-slate-300"
                                        x-text="package.used + ' / ' + package.total"
                                    ></span>
                                </div>

                                <div class="mt-3 flex gap-1">
                                    <template x-for="index in package.total" :key="index">
                                        <span 
                                            class="w-2 h-2 border border-white"
                                            :class="index <= package.used ? 'bg-white' : 'bg-transparent'"
                                        ></span>
                                    </template>
                                </div>

                                <p class="uppercase text-xs font-bold tracking-wider text-slate-400 mt-3">
                                    Sesiones utilizadas
                                </p>
                            </div>
                        </template>

                        <a href="#"
                        class="block w-full text-center border border-slate-300 rounded-md py-3 text-xs font-bold uppercase hover:bg-slate-800">
                            Ver todos los registros
                        </a>
                    </div>
                </aside>

            </div>
        </main>
    </div>
</x-app-layout>