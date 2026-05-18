@php
    $user = Auth::user();
@endphp

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
                <!-- Búsqueda -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </a>

            <!-- Agenda: profesional aprobado o admin -->
            @if($user && $user->puedeAccederPanelProfesional())
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
                src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=1e293b&color=fff"
                alt="Perfil"
                class="w-full h-full object-cover"
            >
        </a>
    </div>

</aside>