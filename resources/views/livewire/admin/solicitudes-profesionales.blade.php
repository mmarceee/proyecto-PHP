<?php

use App\Models\Profesional;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component 
{
    
    public function mount(): void
    {
        // Si el usuario NO es administrador, lo mandamos al dashboard
        if (!Auth::user() || !Auth::user()->esAdmin()) {
            $this->redirect(route('dashboard'), navigate: true);
        }
    }

    // Función para obtener las solicitudes pendientes en tiempo real
    public function with(): array
    {
        return [
            'solicitudes' => Profesional::where('estado', 'pendiente')
                ->with('user')
                ->latest()
                ->get()
        ];
    }

    // Acción para Aceptar al profesional
    public function aceptar($id): void
    {
        $profesional = Profesional::findOrFail($id);
        $profesional->update(['estado' => 'aprobado']);

        session()->flash('success', "¡Perfil de {$profesional->user->name} activado con éxito!");
    }

    // Acción para Rechazar la solicitud
    public function rechazar($id): void
    {
        $profesional = Profesional::findOrFail($id);
        
        // Lo eliminamos para que el usuario pueda volver a postularse si se equivocó en algo
        $profesional->delete(); 

        session()->flash('error', 'La postulación fue rechazada y eliminada.');
    }
}; ?>

<div 
    x-data="solicitudesProfesionales()"
    x-init="cargarSolicitudes()"
    class="py-12 bg-slate-950 min-h-screen text-white px-12"
>
    <div class="max-w-6xl mx-auto">

        <div class="flex items-center justify-between border-b border-slate-700 pb-4 mb-8">
            <h1 class="font-serif text-4xl tracking-tight">
                Solicitudes Profesionales Pendientes
            </h1>

            <span class="bg-slate-800 border border-slate-700 text-xs px-3 py-1 font-bold rounded-full uppercase tracking-wider">
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
                <div class="border border-slate-800 bg-slate-900/60 p-8 rounded-lg grid grid-cols-1 lg:grid-cols-[1fr_auto] items-center gap-6 transition hover:border-slate-700">

                    <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-indigo-400" x-text="solicitud.specialty"></span>

                        <h2 class="font-serif text-3xl mt-1" x-text="solicitud.name"></h2>

                        <div class="flex flex-wrap gap-4 text-xs text-slate-400 mt-2">
                            <span x-show="solicitud.email">
                                📧 <span x-text="solicitud.email"></span>
                            </span>

                            <span x-show="solicitud.phone">
                                📞 <span x-text="solicitud.phone"></span>
                            </span>

                            <span x-show="solicitud.commercial_name">
                                🏢 Comercial:
                                <strong x-text="solicitud.commercial_name"></strong>
                            </span>
                        </div>

                        <p 
                            x-show="solicitud.description"
                            class="text-sm text-slate-300 mt-4 border-l-2 border-slate-700 pl-4 italic"
                        >
                            “<span x-text="solicitud.description"></span>”
                        </p>
                    </div>

                    <div class="flex sm:flex-row lg:flex-col gap-3 w-full lg:w-44">
                        <button 
                            @click="aceptar(solicitud.id)"
                            class="w-full bg-white text-black font-bold uppercase tracking-wider text-xs py-3 rounded hover:bg-slate-200 transition">
                            Aceptar
                        </button>

                        <button 
                            @click="rechazar(solicitud.id)"
                            class="w-full border border-red-800 text-red-400 font-bold uppercase tracking-wider text-xs py-3 rounded hover:bg-red-950/30 transition">
                            Rechazar
                        </button>
                    </div>

                </div>
            </template>
        </div>
    </div>

    <script>
        function solicitudesProfesionales() {
            return {
                cargando: true,
                solicitudes: [],
                mensaje: '',
                error: '',

                async cargarSolicitudes() {
                    try {
                        const response = await fetch('/api/dashboard', {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });

                        if (!response.ok) {
                            throw new Error('No se pudieron cargar las solicitudes.');
                        }

                        const data = await response.json();

                        if (data.tipo !== 'admin') {
                            window.location.href = '/dashboard';
                            return;
                        }

                        this.solicitudes = data.datos.profesionalesPendientes ?? [];

                    } catch (error) {
                        console.error(error);
                        this.error = 'No se pudieron cargar las solicitudes profesionales.';
                    } finally {
                        this.cargando = false;
                    }
                },

                async aceptar(id) {
                    await this.cambiarEstado(id, 'aprobar', 'Profesional aprobado correctamente.');
                },

                async rechazar(id) {
                    await this.cambiarEstado(id, 'rechazar', 'Solicitud profesional rechazada.');
                },

                async cambiarEstado(id, accion, mensajeExito) {
                    this.mensaje = '';
                    this.error = '';

                    try {
                        const response = await fetch(`/api/profesionales/${id}/${accion}`, {
                            method: 'PATCH',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        if (!response.ok) {
                            throw new Error('No se pudo procesar la solicitud.');
                        }

                        this.solicitudes = this.solicitudes.filter(
                            solicitud => solicitud.id !== id
                        );

                        this.mensaje = mensajeExito;

                    } catch (error) {
                        console.error(error);
                        this.error = 'Ocurrió un error al procesar la solicitud.';
                    }
                }
            }
        }
    </script>
</div>