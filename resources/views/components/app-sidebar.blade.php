@php
    $user = Auth::user();
@endphp

<div x-data="{ sidebarOpen: false }" class="contents">
    {{-- Botón hamburguesa: solo en pantallas chicas --}}
    <button
        type="button"
        x-show="!sidebarOpen"
        x-cloak
        @click="sidebarOpen = true"
        class="md:hidden fixed top-5 left-2 z-[70] inline-flex items-center justify-center w-8 h-8 rounded-md bg-slate-900 text-white border border-slate-600 hover:bg-slate-800"
        aria-label="Abrir menú"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    {{-- Fondo oscuro al abrir en celular --}}
    <div
        x-show="sidebarOpen"
        x-cloak
        @click="sidebarOpen = false"
        class="md:hidden fixed inset-0 bg-black/50 z-[55]"
    ></div>
        <aside
            class="overflow-visible fixed inset-y-0 left-0 z-[60]
                w-20 h-screen border-r border-slate-700 bg-slate-900
                flex flex-col items-center justify-between py-8
                transform transition-transform duration-300
                -translate-x-full md:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
        <!-- Parte superior de la sidebar -->
        <div class="flex flex-col items-center gap-8">

            <!-- Logo chico -->
            <a href="{{ route('dashboard') }}"
            class="relative group w-9 h-9 border border-slate-300 rounded-md flex items-center justify-center text-sm font-semibold hover:bg-slate-800">
                <img src="{{ asset('gendarSinFondo.png') }}" class="w-10 h-10 object-contain" alt="Logo">
                <span 
                    class="absolute left-12 top-1/2 -translate-y-1/2 
                        bg-gray-800 text-white text-xs px-2 py-1 rounded-md 
                        opacity-0 invisible group-hover:opacity-100 group-hover:visible 
                        transition whitespace-nowrap z-50"
            >                Menú principal
                </span>
            </a>

            <!-- Botones de navegación -->
            <nav class="flex flex-col items-center gap-8">

                <a href="#"
                class="relative group w-10 h-10 rounded-lg border border-slate-400 flex items-center justify-center text-white hover:bg-slate-800">
                    <!-- Calendario -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/>
                    </svg>
                    <span 
                        class="absolute left-14 top-1/2 -translate-y-1/2 
                            bg-gray-800 text-white text-xs px-2 py-1 rounded-md 
                            opacity-0 invisible group-hover:opacity-100 group-hover:visible 
                            transition whitespace-nowrap z-50"
                    >
                        Calendario de consultas
                    </span>
                </a>

                <a href="/prototipo/busqueda"
                class="relative group w-10 h-10 rounded-lg border border-slate-400 flex items-center justify-center text-white hover:bg-slate-800">
                    <!-- Búsqueda -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span 
                        class="absolute left-14 top-1/2 -translate-y-1/2 
                            bg-gray-800 text-white text-xs px-2 py-1 rounded-md 
                            opacity-0 invisible group-hover:opacity-100 group-hover:visible 
                            transition whitespace-nowrap z-50"
                    >
                        Búsqueda de servicios
                    </span>
                </a>

                <!-- Agenda: profesional aprobado o admin -->
                @if($user && $user->esProfesionalAprobado())
                    <a href="/prototipo/agenda"
                    class="relative group w-10 h-10 rounded-lg flex items-center justify-center text-white hover:bg-slate-800">
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
                        <span 
                            class="absolute left-14 top-1/2 -translate-y-1/2 
                                bg-gray-800 text-white text-xs px-2 py-1 rounded-md 
                                opacity-0 invisible group-hover:opacity-100 group-hover:visible 
                                transition whitespace-nowrap z-50"
                        >
                            Agenda personal
                        </span>
                    </a>

                    <a href="/profesional/servicios"
                    class="relative group w-10 h-10 rounded-lg flex items-center justify-center text-white hover:bg-slate-800">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11.42 15.17L17.25 21A2.5 2.5 0 0021 17.25l-5.83-5.83M11.42 15.17l2.12-2.12M11.42 15.17l-4.24 4.24a2 2 0 01-2.83-2.83l4.24-4.24M13.54 13.05l-4.59-4.59M15.17 8.83l1.41-1.41a2 2 0 10-2.83-2.83l-1.41 1.41M8.95 8.46L5.12 4.64M8.95 8.46l-2.12 2.12"/>
                        </svg>

                        <span 
                            class="absolute left-14 top-1/2 -translate-y-1/2 
                                bg-gray-800 text-white text-xs px-2 py-1 rounded-md 
                                opacity-0 invisible group-hover:opacity-100 group-hover:visible 
                                transition whitespace-nowrap z-50"
                        >
                            Mis servicios
                        </span>
                    </a>

                @endif

            </nav>
        </div>

        <!-- Perfil abajo -->
        <div class="relative group/perfil overflow-visible">
            <a href="{{ route('profile') }}"
            class="block w-10 h-10 rounded-lg border border-slate-500 hover:ring-2 hover:ring-blue-500 overflow-hidden">
                <img
                    src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=1e293b&color=fff"
                    alt="Perfil"
                    class="w-full h-full object-cover"
                >
            </a>

            <span 
                class="pointer-events-none absolute left-full ml-3 top-1/2 -translate-y-1/2 
                    bg-gray-800 text-white text-xs px-2 py-1 rounded-md 
                    opacity-0 invisible group-hover/perfil:opacity-100 group-hover/perfil:visible 
                    transition whitespace-nowrap z-[9999]"
            >
                Mi perfil
            </span>
        </div>
    </aside>
</div>