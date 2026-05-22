<x-app-layout>
    <div x-data="salaVideollamada({{ $reserva->id }})" x-init="iniciar()" class="min-h-screen bg-slate-950 flex flex-col items-center justify-center p-8">

        <template x-if="cargando">
            <p class="text-slate-400 font-serif text-2xl animate-pulse">Conectando a la sala segura...</p>
        </template>

        <template x-if="error">
            <div class="border border-red-900 bg-red-950/30 text-red-300 p-6 rounded-lg text-center">
                <p x-text="error" class="mb-4"></p>
                <a href="/dashboard" class="px-4 py-2 border border-red-500 rounded text-xs uppercase font-bold hover:bg-red-900 transition">Volver</a>
            </div>
        </template>

        <div x-show="conectado" style="display: none;" class="relative w-full max-w-5xl aspect-video bg-black rounded-xl overflow-hidden shadow-2xl border border-slate-800">
            
            <div id="video-remoto" class="w-full h-full bg-slate-900 flex items-center justify-center">
                <p class="text-slate-600 text-sm tracking-widest uppercase">Esperando a la otra persona...</p>
            </div>

            <div id="video-local" class="absolute bottom-6 right-6 w-48 aspect-video bg-black rounded-lg overflow-hidden shadow-lg border border-slate-700">
            </div>

            <div class="absolute bottom-6 left-1/2 -translate-x-1/2">
                <button @click="desconectar()" class="bg-red-600 hover:bg-red-500 px-8 py-3 rounded text-white text-xs font-bold uppercase tracking-wider transition">
                    Colgar
                </button>
            </div>
        </div>
    </div>

</x-app-layout>