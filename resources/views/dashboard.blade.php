@php
    $user = Auth::user();

    $profesional = $user?->profesional;
    $estadoProfesional = $profesional?->estado;
    
    $tieneSolicitudProfesional = $profesional !== null;
    $profesionalPendiente = $estadoProfesional === 'pendiente';
    $profesionalAprobado = $estadoProfesional === 'aprobado';

    $esAdmin = $user?->esAdmin();
    $esProfesional = !$esAdmin && $user?->esProfesionalAprobado();
    $esCliente = !$esAdmin && !$esProfesional;

    $solicitudesPendientes = collect();
    if ($esAdmin) {
        $solicitudesPendientes = \App\Models\Profesional::where('estado', 'pendiente')
            ->with('user')
            ->latest()
            ->take(3) // Traemos solo las últimas 3 para el resumen del Home
            ->get();
    }

    $hora = now()->hour;

    if ($hora < 12) {
        $saludo = 'Buenos días';
    } elseif ($hora < 20) {
        $saludo = 'Buenas tardes';
    } else {
        $saludo = 'Buenas noches';
    }
@endphp
<x-app-layout>
    <div
        x-data="{
            selectedItem: 'maria',

            professionalSessions: {
                maria: {
                    name: 'Maria G.',
                    time: '10:00 AM',
                    reason: 'Consulta inicial',
                    status: 'Por comenzar',
                    packages: [
                        { name: 'Flujo Premium', used: 2, total: 8 },
                        { name: 'Plan Base', used: 1, total: 5 },
                        { name: 'Consulta Inicial', used: 1, total: 1 }
                    ]
                },

                carlos: {
                    name: 'Carlos T.',
                    time: '12:30 PM',
                    reason: 'Seguimiento',
                    status: 'Confirmada',
                    packages: [
                        { name: 'Seguimiento Mensual', used: 5, total: 5 },
                        { name: 'Plan Base', used: 3, total: 5 },
                        { name: 'Asesoría Técnica', used: 2, total: 4 }
                    ]
                }
            },

            clientReservations: {
                ana: {
                    professional: 'Ana Rodríguez',
                    specialty: 'Asesoría legal',
                    time: 'Mañana - 15:00',
                    status: 'Confirmada',
                    packages: [
                        { name: 'Pack Consulta Legal', used: 1, total: 4 },
                        { name: 'Seguimiento Jurídico', used: 0, total: 3 }
                    ]
                },

                martin: {
                    professional: 'Martín Silva',
                    specialty: 'Técnico electricista',
                    time: 'Viernes - 09:30',
                    status: 'Pendiente',
                    packages: []
                }
            },

            adminPendingProfessionals: [
                { name: 'Lucía Pereira', specialty: 'Psicología', date: 'Solicitó hoy' },
                { name: 'Ramiro Gómez', specialty: 'Electricidad', date: 'Hace 2 días' },
                { name: 'Valentina Díaz', specialty: 'Apoyo escolar', date: 'Hace 3 días' }
            ],

            get selectedProfessionalSession() {
                return this.professionalSessions[this.selectedItem];
            },

            get selectedClientReservation() {
                return this.clientReservations[this.selectedItem];
            }
        }"
        class="min-h-screen flex bg-slate-950 text-white">

        <!-- Sidebar -->
        <x-app-sidebar />
        <!-- Contenido principal -->
        <main class="flex-1 px-12 py-10">
            <div class="grid grid-cols-1 xl:grid-cols-[1fr_365px] gap-12">

                <!-- Columna izquierda -->
                <section>
                    <div class="mb-16">
                       <h1 class="font-serif text-6xl leading-none tracking-tight">
                            {{ $saludo }},
                        </h1>

                        <h2 class="font-serif italic text-6xl leading-none text-slate-400">
                            {{ $user->name }}.
                        </h2>
                    </div>
                    {{-- Boton de postulacion profesional --}}
                    @if(!$profesional)
                        <div class="border border-slate-800 bg-slate-900/60 p-8 mb-12 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                            <div>
                                <h3 class="font-serif text-2xl mb-1">¿Formás parte de nuestro equipo médico?</h3>
                                <p class="text-sm text-slate-400">Postulate como profesional para comenzar a gestionar tus pacientes y agendas.</p>
                            </div>
                            <a href="{{ route('profesional.postularse') }}" wire:navigate class="whitespace-nowrap border border-slate-300 hover:bg-white hover:text-black px-6 py-3 text-xs font-bold uppercase tracking-wider transition">
                                Postularse aquí
                            </a>
                        </div>
                    @elseif($profesional->estado === 'pendiente')
                        <div class="border border-amber-900/50 bg-amber-950/20 text-amber-200 p-8 mb-12 flex items-center gap-4">
                            <svg class="w-6 h-6 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-lg">Tu solicitud está siendo revisada</h3>
                                <p class="text-sm text-slate-400 mt-0.5">Un administrador verificará tus datos de especialidad para activar tu panel profesional.</p>
                            </div>
                        </div>
                    @endif

                    <div>
                        @if($esAdmin)
                            <div class="flex items-end justify-between border-b border-slate-400 pb-4 mb-8">
                                <h3 class="uppercase tracking-[0.25em] text-sm font-bold">
                                    Profesionales pendientes
                                </h3>

                                <span class="font-serif italic text-slate-400 text-xl">
                                    {{ $solicitudesPendientes->count() }} {{ $solicitudesPendientes->count() === 1 ? 'solicitud' : 'solicitudes' }}
                                </span>
                            </div>

                            <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
                                @if($solicitudesPendientes->isEmpty())
                                    <div class="px-8 py-12 text-center text-slate-500 italic font-serif">
                                        No hay solicitudes pendientes de revisión por el momento.
                                    </div>
                                @else
                                    @foreach($solicitudesPendientes as $solicitud)
                                        <article class="grid grid-cols-[1fr_auto] items-center gap-4 px-8 py-6 border-b border-slate-700 last:border-none">
                                            <div>
                                                <h4 class="font-serif text-3xl">
                                                    {{ $solicitud->user->name }} {{ $solicitud->user->apellido }}
                                                </h4>

                                                <p class="uppercase tracking-[0.25em] text-sm text-slate-400 mt-1">
                                                    <span>{{ $solicitud->especialidad }}</span>
                                                    ·
                                                    <span>{{ $solicitud->created_at->diffForHumans() }}</span>
                                                </p>
                                            </div>

                                            {{-- Formas de acción directa --}}
                                            <div class="flex items-center gap-3">
                                                <form action="{{ route('admin.dashboard.aceptar', $solicitud->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-xs font-bold uppercase tracking-wider transition">
                                                        Aceptar
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.dashboard.rechazar', $solicitud->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-4 py-2 rounded-md border border-red-400 text-red-300 hover:bg-red-950/40 text-xs font-bold uppercase tracking-wider transition">
                                                        Rechazar
                                                    </button>
                                                </form>
                                            </div>
                                        </article>
                                    @endforeach
                                @endif
                            </div>
                            

                            <div class="mt-8 grid gap-4 md:grid-cols-2">
                                <a href="/admin/usuarios"
                                class="rounded-lg border border-slate-600 bg-slate-900 p-6 hover:bg-slate-800 transition">
                                    <h4 class="font-serif text-2xl">Panel de usuarios</h4>
                                    <p class="mt-2 text-sm text-slate-400">
                                        Bloquear, desbloquear usuarios o asignar permisos de administrador.
                                    </p>
                                </a>

                                <a href="/admin/profesionales"
                                class="rounded-lg border border-slate-600 bg-slate-900 p-6 hover:bg-slate-800 transition">
                                    <h4 class="font-serif text-2xl">Solicitudes profesionales</h4>
                                    <p class="mt-2 text-sm text-slate-400">
                                        Revisar profesionales pendientes de aprobación.
                                    </p>
                                </a>
                            </div>

                        @elseif($esProfesional)

                            <div class="flex items-end justify-between border-b border-slate-400 pb-4 mb-8">
                                <h3 class="uppercase tracking-[0.25em] text-sm font-bold">
                                    Consultas de hoy
                                </h3>

                                <span class="font-serif italic text-slate-400 text-xl">
                                    2 hoy
                                </span>
                            </div>

                            <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
                                <article 
                                    @click="selectedItem = 'maria'"
                                    :class="selectedItem === 'maria' ? 'ring-1 ring-blue-500 bg-slate-800' : 'bg-slate-900'"
                                    class="cursor-pointer grid grid-cols-[130px_1fr_auto] items-center gap-4 px-8 py-8 border-b border-slate-700 transition hover:bg-slate-800">

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
                                    @click="selectedItem = 'carlos'"
                                    :class="selectedItem === 'carlos' ? 'ring-1 ring-blue-500 bg-slate-800' : 'bg-slate-900'"
                                    class="cursor-pointer grid grid-cols-[130px_1fr_auto] items-center gap-4 px-8 py-8 border-b border-slate-700 transition hover:bg-slate-800">

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

                        @else

                            <div class="flex items-end justify-between border-b border-slate-400 pb-4 mb-8">
                                <h3 class="uppercase tracking-[0.25em] text-sm font-bold">
                                    Próximas sesiones
                                </h3>

                                <span class="font-serif italic text-slate-400 text-xl">
                                    Reservas activas
                                </span>
                            </div>

                            <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
                                <article 
                                    @click="selectedItem = 'ana'"
                                    :class="selectedItem === 'ana' ? 'ring-1 ring-blue-500 bg-slate-800' : 'bg-slate-900'"
                                    class="cursor-pointer grid grid-cols-[150px_1fr_auto] items-center gap-4 px-8 py-8 border-b border-slate-700 transition hover:bg-slate-800">

                                    <div>
                                        <span class="font-serif text-2xl tracking-widest">Mañana</span>
                                        <p class="text-xs font-bold mt-1">15:00</p>
                                    </div>

                                    <div>
                                        <h4 class="font-serif text-3xl">Ana Rodríguez</h4>
                                        <p class="uppercase tracking-[0.25em] text-sm text-slate-400 mt-1">
                                            ▫ Asesoría legal
                                        </p>
                                    </div>

                                    <span class="px-4 py-1 rounded-md border border-white text-xs font-bold uppercase tracking-wider">
                                        Confirmada
                                    </span>
                                </article>

                                <article 
                                    @click="selectedItem = 'martin'"
                                    :class="selectedItem === 'martin' ? 'ring-1 ring-blue-500 bg-slate-800' : 'bg-slate-900'"
                                    class="cursor-pointer grid grid-cols-[150px_1fr_auto] items-center gap-4 px-8 py-8 border-b border-slate-700 transition hover:bg-slate-800">

                                    <div>
                                        <span class="font-serif text-2xl tracking-widest">Viernes</span>
                                        <p class="text-xs font-bold mt-1">09:30</p>
                                    </div>

                                    <div>
                                        <h4 class="font-serif text-3xl">Martín Silva</h4>
                                        <p class="uppercase tracking-[0.25em] text-sm text-slate-400 mt-1">
                                            ▫ Técnico electricista
                                        </p>
                                    </div>

                                    <span class="px-4 py-1 rounded-md bg-yellow-700/60 text-xs font-bold uppercase tracking-wider">
                                        Pendiente
                                    </span>
                                </article>
                            </div>

                        @endif
                    </div>
                </section>

                <!-- Columna derecha -->
                <aside class="xl:pt-32">
                    @if($tieneSolicitudProfesional)
                        <div class="mb-6 rounded-lg border 
                            {{ $profesionalPendiente ? 'border-yellow-500/60 bg-yellow-950/30' : 'border-blue-500/50 bg-blue-950/30' }} 
                            p-4">

                            <p class="text-xs uppercase tracking-[0.25em] text-slate-400 mb-2">
                                Estado profesional
                            </p>

                            <div class="flex items-center justify-between gap-3">
                                <span class="font-serif text-xl">
                                    Solicitud de profesional
                                </span>

                                <span class="rounded-md px-3 py-1 text-xs font-bold uppercase tracking-wider
                                    {{ $profesionalPendiente ? 'bg-yellow-600/30 text-yellow-200 border border-yellow-400/40' : 'bg-blue-600/30 text-blue-200 border border-blue-400/40' }}">
                                    {{ $estadoProfesional }}
                                </span>
                            </div>

                            @if($profesionalPendiente)
                                <p class="mt-3 text-sm text-yellow-100/80">
                                    Tu solicitud para acceder como profesional está pendiente de aprobación.
                                </p>
                            @endif
                        </div>
                    @endif
                    @if($esAdmin)

                        <div class="border border-slate-300 rounded-lg p-6 bg-slate-900/60">
                            <h3 class="uppercase tracking-[0.18em] text-sm font-bold mb-3">
                                Acciones rápidas
                            </h3>

                            <div class="border-b border-slate-400 mb-6"></div>

                            <div class="space-y-4">
                                <a href="/admin/usuarios"
                                class="block rounded-md border border-slate-500 px-4 py-3 text-sm font-bold uppercase tracking-wider hover:bg-slate-800">
                                    Gestionar usuarios
                                </a>

                                <a href="/admin/profesionales"
                                class="block rounded-md border border-slate-500 px-4 py-3 text-sm font-bold uppercase tracking-wider hover:bg-slate-800">
                                    Ver profesionales pendientes
                                </a>

                                <a href="/admin/paquetes"
                                class="block rounded-md border border-slate-500 px-4 py-3 text-sm font-bold uppercase tracking-wider hover:bg-slate-800">
                                    Gestionar paquetes
                                </a>
                            </div>
                        </div>

                    @elseif($esProfesional)

                        <div class="border border-slate-300 rounded-lg p-6 bg-slate-900/60">
                            <h3 class="uppercase tracking-[0.18em] text-sm font-bold mb-3">
                                Paquetes disponibles
                            </h3>

                            <div class="border-b border-slate-400 mb-6"></div>

                            <div class="mb-6">
                                <p class="text-xs uppercase tracking-[0.25em] text-slate-400 mb-1">
                                    Cliente seleccionado
                                </p>

                                <h4 class="font-serif text-2xl" x-text="selectedProfessionalSession.name"></h4>
                            </div>

                            <template x-for="package in selectedProfessionalSession.packages" :key="package.name">
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

                    @else

                        <div class="border border-slate-300 rounded-lg p-6 bg-slate-900/60">
                            <h3 class="uppercase tracking-[0.18em] text-sm font-bold mb-3">
                                Paquetes con este profesional
                            </h3>

                            <div class="border-b border-slate-400 mb-6"></div>

                            <div class="mb-6">
                                <p class="text-xs uppercase tracking-[0.25em] text-slate-400 mb-1">
                                    Profesional seleccionado
                                </p>

                                <h4 class="font-serif text-2xl" x-text="selectedClientReservation.professional"></h4>

                                <p class="mt-1 text-sm text-slate-400" x-text="selectedClientReservation.specialty"></p>
                            </div>

                            <template x-if="selectedClientReservation.packages.length > 0">
                                <div>
                                    <template x-for="package in selectedClientReservation.packages" :key="package.name">
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
                                </div>
                            </template>

                            <template x-if="selectedClientReservation.packages.length === 0">
                                <div class="rounded-md border border-slate-600 bg-slate-800/60 p-4">
                                    <p class="text-sm text-slate-300">
                                        No tienes paquetes activos con este profesional.
                                    </p>
                                </div>
                            </template>

                            <a href="/prototipo/busqueda"
                            class="mt-6 block w-full text-center border border-slate-300 rounded-md py-3 text-xs font-bold uppercase hover:bg-slate-800">
                                Buscar paquetes
                            </a>
                        </div>

                    @endif
                </aside>
            </div>
        </main>
    </div>
</x-app-layout>