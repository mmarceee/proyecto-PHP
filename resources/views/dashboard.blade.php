<x-app-layout>
    <div
        x-data="dashboardData()"
        class="min-h-screen flex bg-slate-950 text-white overflow-x-hidden">

        <!-- Sidebar -->
        <x-app-sidebar />
        <!-- Contenido principal -->
        <main class="flex-1 w-full min-w-0 px-4 sm:px-6 lg:px-12 py-6 lg:py-10 md:ml-20">
            <div x-show="cargando" class="mb-6 rounded-lg border border-slate-700 bg-slate-900 p-4 text-sm text-slate-300">
                Cargando información del dashboard...
            </div>
            <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_365px] gap-8 xl:gap-12">

                <!-- Columna izquierda -->
                <section>
                    <!-- Saludo (implementacion desde API) -->
                    <div class="mb-8 lg:mb-16">
                        <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl leading-none tracking-tight" x-text="saludo ? saludo + ',' : ''"></h1>
                        <h2 class="font-serif italic text-4xl sm:text-5xl lg:text-6xl leading-none text-slate-400 break-words" x-text="usuario.nombre ? usuario.nombre + '.' : ''"></h2>
                    </div>
                    {{-- Boton de postulacion profesional --}}
                    <template x-if="tipo === 'cliente' && !profesional.tieneSolicitud">
                        <div class="border border-slate-800 bg-slate-900/60 p-5 sm:p-8 mb-8 lg:mb-12 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 rounded-lg">
                            <div>
                                <h3 class="font-serif text-2xl mb-1">¿Eres un profesional que quiere ofrecer sus servicios con nosotros?</h3>
                                <p class="text-sm text-slate-400">Postulate como profesional para comenzar a gestionar tus clientes y agendas.</p>
                            </div>
                            <a href="{{ route('profesional.postularse') }}" wire:navigate class="w-full sm:w-auto text-center whitespace-nowrap border border-slate-300 hover:bg-white hover:text-black px-6 py-3 text-xs font-bold uppercase tracking-wider transition">
                                Postularse aquí
                            </a>
                        </div>
                    </template>    
                    <template x-if="tipo === 'cliente' && profesional.pendiente">
                        <div class="border border-amber-900/50 bg-amber-950/20 text-amber-200 p-8 mb-12 flex items-center gap-4">
                            <svg class="w-6 h-6 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-lg">Tu solicitud está siendo revisada</h3>
                                <p class="text-sm text-slate-400 mt-0.5">Un administrador verificará tus datos de especialidad para activar tu panel profesional.</p>
                            </div>
                        </div>
                    </template>

                    <div>
                        <template x-if="tipo === 'admin'">
                            <div>
                                <div class="flex items-end justify-between border-b border-slate-400 pb-4 mb-8">
                                    <h3 class="uppercase tracking-[0.25em] text-sm font-bold">
                                        Profesionales pendientes
                                    </h3>

                                    <span class="font-serif italic text-slate-400 text-xl">
                                        Solicitudes
                                    </span>
                                </div>

                                <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
                                    <template x-for="professional in adminPendingProfessionals" :key="professional.id">
                                        <article class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_auto] items-start lg:items-center gap-6 px-4 sm:px-6 lg:px-8 py-6 border-b border-slate-700">
                                            <div>
                                                <h4 class="font-serif text-2xl sm:text-3xl break-words" x-text="professional.name"></h4>
                                                <p class="text-sm text-slate-400 mt-1 break-all" x-text="professional.email"></p>

                                                <p class="uppercase tracking-[0.25em] text-sm text-slate-400 mt-1">
                                                    <span x-text="professional.especialidad"></span>
                                                    ·
                                                    <span x-text="professional.created_at"></span>
                                                </p>

                                                <!-- Opcional: solo se muestra si existe nombre comercial -->
                                                <p class="text-sm text-slate-300 mt-2" x-show="professional.nombre_comercial">
                                                    Nombre comercial: 
                                                    <span x-text="professional.nombre_comercial"></span>
                                                </p>
                                            </div>

                                            <div class="flex flex-col sm:flex-row lg:flex-col gap-3 w-full lg:w-auto">
                                        <button 
                                            @click="aprobarProfesional(professional.id)"
                                            data-requires-online
                                            class="w-full sm:w-auto px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-xs font-bold uppercase tracking-wider">
                                                    Aceptar
                                                </button>

                                        <button 
                                            @click="rechazarProfesional(professional.id)"
                                            data-requires-online
                                            class="w-full sm:w-auto px-4 py-2 rounded-md border border-red-400 text-red-300 hover:bg-red-950/40 text-xs font-bold uppercase tracking-wider">
                                                    Rechazar
                                                </button>
                                            </div>
                                        </article>
                                    </template>
                                    <template x-if="adminPendingProfessionals.length === 0">
                                        <div class="px-8 py-8 text-sm text-slate-400">
                                            No hay profesionales pendientes de aprobación.
                                        </div>
                                    </template>
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
                            </div>
                        </template>

                        <template x-if="tipo === 'profesional'">
                            <div>
                                <div class="mb-10 lg:mb-12">
                                    <div class="flex items-end justify-between border-b border-slate-400 pb-4 mb-8">
                                        <h3 class="uppercase tracking-[0.25em] text-sm font-bold">
                                            Reservas pendientes
                                        </h3>

                                        <span class="font-serif italic text-slate-400 text-xl">
                                            <span x-text="reservasPendientes.length"></span> por aprobar
                                        </span>
                                    </div>

                                    <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
                                        <template x-for="reserva in reservasPendientes" :key="reserva.id">
                                            <article 
                                                class="grid grid-cols-1 sm:grid-cols-[120px_minmax(0,1fr)] lg:grid-cols-[150px_minmax(0,1fr)_auto] items-start lg:items-center gap-4 px-4 sm:px-6 lg:px-8 py-6 lg:py-8 border-b border-slate-700 transition hover:bg-slate-800">

                                                <div>
                                                    <span class="font-serif text-2xl tracking-widest" x-text="reserva.date_label"></span>
                                                    <p class="text-xs font-bold mt-1" x-text="reserva.time"></p>
                                                </div>

                                                <div>
                                                    <h4 class="font-serif text-2xl sm:text-3xl break-words" x-text="reserva.client_name"></h4>
                                                    <p class="text-sm text-slate-400 mt-1 break-all" x-text="professional.email"></p>
                                                    <p class="uppercase tracking-[0.25em] text-sm text-slate-400 mt-1">
                                                        ▫ <span x-text="reserva.service_name"></span>
                                                    </p>
                                                </div>

                                                <div class="flex flex-col items-center gap-2 sm:col-span-2 lg:col-span-1">
                                                    <span class="px-3 py-0.5 rounded-md border border-slate-500 text-[10px] font-bold uppercase tracking-widest text-slate-300"
                                                        x-text="reserva.status">
                                                    </span>

                                                    <div class="flex items-center gap-3">
                                                <button 
                                                    @click.stop="abrirModalCancelacion(reserva.id)" 
                                                    data-requires-online
                                                    class="px-4 py-2 rounded-md border border-red-500 text-red-500 hover:bg-red-500 hover:text-white transition text-xs font-bold uppercase tracking-wider">
                                                            Cancelar
                                                        </button>

                                                <button 
                                                    @click.stop="avanzarEstadoReserva(reserva.id)" 
                                                    data-requires-online
                                                    class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-xs font-bold uppercase tracking-wider">
                                                            Confirmar
                                                        </button>
                                                    </div>
                                                </div>
                                            </article>
                                        </template>

                                        <template x-if="reservasPendientes.length === 0">
                                            <div class="px-8 py-8 text-sm text-slate-400">
                                                No tienes reservas pendientes de aprobación.
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <div class="flex items-end justify-between border-b border-slate-400 pb-4 mb-8">
                                    <h3 class="uppercase tracking-[0.25em] text-sm font-bold">
                                        Consultas de hoy (Como profesional)
                                    </h3>

                                    <span class="font-serif italic text-slate-400 text-xl">
                                        <span x-text="consultasHoy.length"></span> hoy
                                    </span>
                                </div>

                                <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
                                    <template x-for="session in consultasHoy" :key="session.id">
                                        <article 
                                            @click="selectedItem = session.id"
                                            :class="selectedItem === session.id ? 'ring-1 ring-blue-500 bg-slate-800' : 'bg-slate-900'"
                                            class="cursor-pointer grid grid-cols-1 sm:grid-cols-[110px_minmax(0,1fr)] lg:grid-cols-[130px_minmax(0,1fr)_auto] items-start lg:items-center gap-4 px-4 sm:px-6 lg:px-8 py-6 lg:py-8 border-b border-slate-700 transition hover:bg-slate-800">

                                            <div>
                                                <span class="font-serif text-4xl tracking-widest" x-text="session.time"></span>
                                                <span class="text-xs font-bold ml-1" x-text="session.period"></span>
                                            </div>

                                            <div>
                                                <h4 class="font-serif text-2xl sm:text-3xl break-words" x-text="session.client_name"></h4>
                                                <p class="text-sm text-slate-400 mt-1 break-all" x-text="session.client_email"></p>
                                                <p class="uppercase tracking-[0.25em] text-sm text-slate-400 mt-1">
                                                    ▫ <span x-text="session.reason"></span>
                                                </p>
                                            </div>

                                            <div class="flex flex-col items-center gap-2 sm:col-span-2 lg:col-span-1">
                                                
                                                <span class="px-3 py-0.5 rounded-md border border-slate-500 text-[10px] font-bold uppercase tracking-widest text-slate-300"
                                                    x-text="session.status">
                                                </span>

                                                <div class="flex items-center gap-3">
                                                    
                                                    <template x-if="session.status.toLowerCase() !== 'cancelada' && session.status.toLowerCase() !== 'finalizada'">
                                                <button 
                                                    @click.stop="abrirModalCancelacion(session.id)" 
                                                    data-requires-online
                                                    class="px-4 py-2 rounded-md border border-red-500 text-red-500 hover:bg-red-500 hover:text-white transition text-xs font-bold uppercase tracking-wider">
                                                            Cancelar
                                                        </button>
                                                    </template>

                                                    <template x-if="session.action_label">
                                                <button 
                                                    @click.stop="avanzarEstadoReserva(session.id)" 
                                                    data-requires-online
                                                    class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-xs font-bold uppercase tracking-wider"
                                                            x-text="session.action_label">
                                                        </button>
                                                    </template>
                                                    <template x-if="esHoraDeSala(session.date_raw, session.time)">
                                                        <a :href="'/reserva/' + session.id + '/sala'" 
                                                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold uppercase tracking-wider rounded-md transition-colors duration-200">
                                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                            Sala Virtual
                                                        </a>
                                                    </template>
                                                </div>

                                            </div>
                                        </article>
                                    </template>

                                    <template x-if="consultasHoy.length === 0">
                                        <div class="px-8 py-8 text-sm text-slate-400">
                                            No tienes consultas agendadas para hoy.
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="tipo === 'cliente' || tipo === 'profesional'">
                            <div class="mt-10 lg:mt-12">
                                <div class="flex items-end justify-between border-b border-slate-400 pb-4 mb-8">
                                    <h3 
                                        class="uppercase tracking-[0.25em] text-sm font-bold"
                                        x-text="tipo === 'profesional' ? 'Tus próximas sesiones (como cliente)' : 'Tus próximas sesiones'">
                                    </h3>
                                    <span class="font-serif italic text-slate-400 text-xl">
                                        Reservas activas
                                    </span>
                                </div>

                                <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
                                    <template x-for="reservation in proximasSesiones" :key="reservation.id">
                                        <article 
                                            @click="selectedItem = reservation.id"
                                            :class="selectedItem === reservation.id ? 'ring-1 ring-blue-500 bg-slate-800' : 'bg-slate-900'"
                                            class="cursor-pointer grid grid-cols-1 sm:grid-cols-[120px_minmax(0,1fr)] lg:grid-cols-[150px_minmax(0,1fr)_auto] items-start lg:items-center gap-4 px-4 sm:px-6 lg:px-8 py-6 lg:py-8 border-b border-slate-700 transition hover:bg-slate-800">

                                            <div>
                                                <span class="font-serif text-2xl tracking-widest" x-text="reservation.date_label"></span>
                                                <p class="text-xs font-bold mt-1" x-text="reservation.time"></p>
                                            </div>

                                            <div>
                                                <h4 class="font-serif text-3xl" x-text="reservation.professional_name"></h4>
                                                <p class="text-sm text-slate-400 mt-1 break-all" x-text="reservation.professional_email"></p>
                                                <p class="uppercase tracking-[0.25em] text-sm text-slate-400 mt-1">
                                                    ▫ <span x-text="reservation.specialty"></span>
                                                </p>
                                            </div>

                                            <div class="flex flex-col items-center gap-2 sm:col-span-2 lg:col-span-1">
                                                <span class="px-4 py-1 rounded-md border border-slate-500 text-xs font-bold uppercase tracking-wider" x-text="reservation.status"></span>
                                                <div class="flex items-center gap-2">
                                                    <template x-if="reservation.status.toLowerCase() !== 'cancelada' && reservation.status.toLowerCase() !== 'finalizada'">
                                                        <div class="flex gap-2">
                                                            <button @click.stop="abrirModalReprogramar(reservation)" class="px-3 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-600 text-white text-[10px] font-bold uppercase tracking-wider transition">Reprogramar</button>
                                                            <button @click.stop="abrirModalCancelacion(reservation.id)" class="px-3 py-1.5 rounded-md border border-red-500 text-red-500 hover:bg-red-500 hover:text-white transition text-[10px] font-bold uppercase tracking-wider">Cancelar</button>
                                                        </div>
                                                    </template>
                                                    <template x-if="esHoraDeSala(reservation.date_raw, reservation.time)">
                                                        <a :href="'/reserva/' + reservation.id + '/sala'" class="inline-flex items-center px-4 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-[10px] font-bold uppercase tracking-wider rounded-md transition">
                                                            Sala Virtual
                                                        </a>
                                                    </template>
                                                </div>
                                            </div>
                                        </article>
                                    </template>

                                    <template x-if="proximasSesiones.length === 0">
                                        <div class="px-8 py-8 text-sm text-slate-400">
                                            No tienes próximas sesiones agendadas.
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>

                <!-- Columna derecha -->
                <aside class="w-full min-w-0 xl:pt-32">
                    <template x-if="profesional.tieneSolicitud">
                        <div class="mb-6 rounded-lg border p-4"
                             :class="profesional.pendiente 
                                ? 'border-yellow-500/60 bg-yellow-950/30' 
                                : 'border-blue-500/50 bg-blue-950/30'"
                        >
                            <p class="text-xs uppercase tracking-[0.25em] text-slate-400 mb-2">
                                Estado profesional
                            </p>

                            <div class="flex items-center justify-between gap-3">
                                <span class="font-serif text-xl">
                                    Solicitud de profesional
                                </span>

                                 <span 
                                    class="rounded-md px-3 py-1 text-xs font-bold uppercase tracking-wider border"
                                    :class="profesional.pendiente
                                        ? 'bg-yellow-600/30 text-yellow-200 border-yellow-400/40'
                                        : 'bg-blue-600/30 text-blue-200 border-blue-400/40'"
                                    x-text="profesional.estado">               
                                    </span>
                            </div>

                            <template x-if="profesional.pendiente">
                                <p class="mt-3 text-sm text-yellow-100/80">
                                    Tu solicitud para acceder como profesional está pendiente de aprobación.
                                </p>
                            </template>
                        </div>
                    </template>

                    <template x-if="tipo === 'admin'">
                        <div class="border border-slate-300 rounded-lg p-4 sm:p-6 bg-slate-900/60">
                            <h3 class="uppercase tracking-[0.18em] text-sm font-bold mb-3">
                                Acciones rápidas
                            </h3>

                            <div class="border-b border-slate-400 mb-6"></div>

                            <div class="space-y-4">
                                <a href="{{ route('admin.usuarios') }}"
                                class="block rounded-md border border-slate-500 px-4 py-3 text-sm font-bold uppercase tracking-wider hover:bg-slate-800">
                                    Gestionar usuarios
                                </a>

                                <a href="{{ route('admin.profesionales') }}"
                                class="block rounded-md border border-slate-500 px-4 py-3 text-sm font-bold uppercase tracking-wider hover:bg-slate-800">
                                    Ver profesionales pendientes
                                </a>
                                    <a href="{{ route('admin.categorias') }}"
                                    class="block rounded-md border border-slate-500 px-4 py-3 text-sm font-bold uppercase tracking-wider hover:bg-slate-800">
                                    Gestionar Categorias
                                </a>
                            </div>
                        </div>
                    </template>

                    <template x-if="tipo === 'profesional'">
                        <div class="border border-slate-300 rounded-lg p-4 sm:p-6 bg-slate-900/60">
                            <h3 class="uppercase tracking-[0.18em] text-sm font-bold mb-3">
                                Paquetes vendidos
                            </h3>

                            <div class="border-b border-slate-400 mb-6"></div>

                            <div class="mb-6">
                                <p class="text-sm text-slate-400">
                                    Consultá todos los paquetes que vendiste, a qué cliente pertenecen y cuántas sesiones le quedan disponibles.
                                </p>
                            </div>

                            <a href="{{ route('profesional.paquetes.vendidos') }}"
                            class="block w-full text-center border border-slate-300 rounded-md py-3 text-xs font-bold uppercase hover:bg-slate-800">
                                Ver paquetes vendidos
                            </a>
                        </div>
                    </template>

                    <template x-if="tipo === 'cliente' || tipo === 'profesional'">
                        <div class="mt-8 lg:mt-10 border border-slate-300 rounded-lg p-4 sm:p-6 bg-slate-900/60">
                            <h3 class="uppercase tracking-[0.18em] text-sm font-bold mb-3">
                                Paquetes para comprar con nuestros profesionales
                            </h3>

                            <div class="border-b border-slate-400 mb-6"></div>

                            <a href="{{ route('cliente.paquetes.explorar') }}"
                            class="mt-6 block w-full text-center border border-slate-300 rounded-md py-3 text-xs font-bold uppercase hover:bg-slate-800">
                                Buscar paquetes
                            </a>
                        </div>
                    </template>
                </aside>
            </div>
        </main>
        <div x-show="showCancelModal" style="display: none;" x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm">
            
            <div class="bg-slate-900 border border-slate-700 rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl" 
                @click.away="cerrarModalCancelacion()">
                
                <h3 class="text-xl font-serif text-white mb-2">Cancelar Consulta</h3>
                <p class="text-sm text-slate-400 mb-5">
                    Por favor, indica el motivo de la cancelación. Este mensaje será enviado al cliente para notificarle.
                </p>
                
                <textarea 
                    x-model="motivoCancelacion" 
                    class="w-full bg-slate-800 border border-slate-600 rounded-md p-3 text-white text-sm focus:ring-red-500 focus:border-red-500 placeholder-slate-500 mb-5" 
                    rows="3" 
                    placeholder="Ej: Inconveniente personal de fuerza mayor..."></textarea>
                
                <div class="flex justify-end gap-3">
                    <button 
                        @click="cerrarModalCancelacion()" 
                        class="px-4 py-2 rounded-md text-sm font-bold text-slate-300 hover:bg-slate-800 transition tracking-wide">
                        VOLVER
                    </button>
                    <button 
                        @click="confirmarCancelacion()" 
                        data-requires-online
                        class="px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-bold transition tracking-wide shadow-lg">
                        CONFIRMAR CANCELACIÓN
                    </button>
                </div>
            </div>
        </div>

    <!-- Modal de Reprogramación -->
    <div x-show="showReprogramarModal" style="display: none;" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-700 rounded-xl p-6 max-w-3xl w-full mx-4 shadow-2xl overflow-y-auto max-h-[90vh]" @click.away="cerrarModalReprogramar()">
            <div class="flex justify-between items-center mb-6 border-b border-slate-700 pb-4">
                <h3 class="text-2xl font-serif text-white">Reprogramar Consulta</h3>
                <button @click="cerrarModalReprogramar()" class="text-slate-400 hover:text-white text-2xl">&times;</button>
            </div>

            <div x-show="cargandoAgenda" class="text-center py-8 text-slate-400">Cargando disponibilidad del profesional...</div>

            <div x-show="!cargandoAgenda" class="space-y-6">
                <!-- Controles de semana: izquierda y derecha -->
                <div class="flex items-center justify-between mb-4">
                    <button @click="retrocederSemanaReprogramacion()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-600 rounded-lg text-sm font-bold uppercase tracking-wider transition shadow-sm">&larr; Semana Anterior</button>
                    <button @click="avanzarSemanaReprogramacion()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-600 rounded-lg text-sm font-bold uppercase tracking-wider transition shadow-sm">Siguiente Semana &rarr;</button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-7 gap-3">
                    <template x-for="dia in semanaReprogramacion" :key="dia.fecha">
                        <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-3 text-center flex flex-col h-full">
                            <div class="pb-2 mb-2 border-b border-slate-700/50">
                                <p class="text-xs text-indigo-400 font-bold uppercase tracking-widest" x-text="dia.nombre_dia.substring(0,3)"></p>
                                <p class="text-lg text-white font-serif mt-1" x-text="dia.fecha.split('-')[2]"></p>
                            </div>
                            <div class="space-y-2 max-h-56 overflow-y-auto custom-scrollbar pr-1 flex-1">
                                <template x-for="bloque in dia.bloques">
                                    <button 
                                        @click="confirmarReprogramacion(dia.fecha, bloque.hora)"
                                        :disabled="bloque.ocupado"
                                        :class="bloque.ocupado ? 'bg-slate-800 text-slate-600 cursor-not-allowed opacity-50 border border-slate-700/50' : 'bg-indigo-600 hover:bg-indigo-500 text-white font-bold cursor-pointer shadow-sm hover:-translate-y-0.5'"
                                        class="w-full text-sm py-2 rounded-lg transition-all duration-200">
                                        <span x-text="bloque.hora"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Reprogramación -->
    <div x-show="showConfirmarReprogramacionModal" style="display: none;" x-cloak class="fixed inset-0 z-[110] flex items-center justify-center bg-black/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-700 rounded-xl p-6 max-w-sm w-full mx-4 shadow-2xl text-center animate-in fade-in zoom-in duration-200" @click.away="showConfirmarReprogramacionModal = false">
            <h3 class="text-2xl font-serif text-white mb-2">Confirmar</h3>
            <p class="text-sm text-slate-400 mb-6">
                ¿Seguro que quieres reprogramar para el día <strong class="text-indigo-400 text-lg" x-text="formatDate(fechaSeleccionadaConfirmacion)"></strong> a la(s) <strong class="text-indigo-400 text-lg" x-text="horaSeleccionadaConfirmacion"></strong>?
            </p>
            <div class="flex justify-center gap-3">
                <button @click="showConfirmarReprogramacionModal = false" class="px-5 py-2.5 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-800 text-xs font-bold uppercase tracking-wider transition">NO</button>
                <button @click="ejecutarReprogramacion()" class="px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold uppercase tracking-wider transition shadow-lg">SÍ, REPROGRAMAR</button>
            </div>
        </div>
    </div>

    <!-- Modal de Éxito de Reprogramación -->
    <div x-show="showExitoReprogramacionModal" style="display: none;" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center bg-black/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-emerald-700/50 rounded-xl p-8 max-w-sm w-full mx-4 shadow-2xl text-center animate-in fade-in zoom-in duration-200">
            <div class="w-16 h-16 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h3 class="text-2xl font-serif text-white mb-2">¡Completado!</h3>
            <p class="text-sm text-slate-400 mb-6">La reserva se ha reprogramado exitosamente.</p>
            <button @click="showExitoReprogramacionModal = false" class="px-6 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold uppercase tracking-wider transition shadow-lg w-full">Entendido</button>
        </div>
    </div>

    <!-- Modal de Error de Reprogramación -->
    <div x-show="showErrorReprogramacionModal" style="display: none;" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center bg-black/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-red-700/50 rounded-xl p-8 max-w-sm w-full mx-4 shadow-2xl text-center animate-in fade-in zoom-in duration-200">
            <div class="w-16 h-16 bg-red-500/20 text-red-400 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>
            <h3 class="text-xl font-serif text-white mb-2">No se pudo reprogramar</h3>
            <p class="text-sm text-slate-400 mb-6" x-text="errorReprogramacionMensaje"></p>
            <button @click="showErrorReprogramacionModal = false" class="px-6 py-2.5 rounded-lg bg-red-600 hover:bg-red-500 text-white text-xs font-bold uppercase tracking-wider transition shadow-lg w-full">Cerrar</button>
        </div>
    </div>

    </div>
</x-app-layout>
