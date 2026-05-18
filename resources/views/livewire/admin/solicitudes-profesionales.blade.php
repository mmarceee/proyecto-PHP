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

<div class="py-12 bg-slate-950 min-h-screen text-white px-12">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between border-b border-slate-700 pb-4 mb-8">
            <h1 class="font-serif text-4xl tracking-tight">Solicitudes Profesionales Pendientes</h1>
            <span class="bg-slate-800 border border-slate-700 text-xs px-3 py-1 font-bold rounded-full uppercase tracking-wider">
                {{ $solicitudes->count() }} Solicitudes
            </span>
        </div>

        @if (session()->has('success'))
            <div class="mb-6 p-4 bg-green-950/40 border border-green-800 text-green-200 text-sm rounded">
                {{ session('success') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-red-950/40 border border-red-800 text-red-200 text-sm rounded">
                {{ session('error') }}
            </div>
        @endif

        {{-- Lista de Solicitudes --}}
        @if($solicitudes->isEmpty())
            <div class="border border-slate-800 bg-slate-900/40 p-12 text-center rounded-lg">
                <p class="text-slate-400 font-serif italic text-lg">No hay postulaciones pendientes de revisión por el momento.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6">
                @foreach($solicitudes as $solicitud)
                    <div class="border border-slate-800 bg-slate-900/60 p-8 rounded-lg grid grid-cols-1 lg:grid-cols-[1fr_auto] items-center gap-6 transition hover:border-slate-700">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-widest text-indigo-400">
                                {{ $solicitud->especialidad }}
                            </span>
                            <h2 class="font-serif text-3xl mt-1">
                                {{ $solicitud->user->name }} {{ $solicitud->user->apellido }}
                            </h2>
                            
                            <div class="flex flex-wrap gap-4 text-xs text-slate-400 mt-2">
                                <span>📧 {{ $solicitud->user->email }}</span>
                                <span>📞 {{ $solicitud->user->telefono }}</span>
                                @if($solicitud->nombre_comercial)
                                    <span>🏢 Comercial: <strong>{{ $solicitud->nombre_comercial }}</strong></span>
                                @endif
                            </div>

                            <p class="text-sm text-slate-300 mt-4 border-l-2 border-slate-700 pl-4 italic">
                                "{{ $solicitud->descripcion }}"
                            </p>
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="flex sm:flex-row lg:flex-col gap-3 w-full lg:w-44">
                            <button wire:click="aceptar({{ $solicitud->id }})" class="w-full bg-white text-black font-bold uppercase tracking-wider text-xs py-3 rounded hover:bg-slate-200 transition">
                                Aceptar
                            </button>
                            <button wire:click="rechazar({{ $solicitud->id }})" class="w-full border border-red-800 text-red-400 font-bold uppercase tracking-wider text-xs py-3 rounded hover:bg-red-950/30 transition">
                                Rechazar
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>