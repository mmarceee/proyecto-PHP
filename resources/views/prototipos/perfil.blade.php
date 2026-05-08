<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Mi Perfil y Servicios') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-2xl">
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-20 h-20 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-blue-600 text-2xl font-bold">
                            EP
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Ezequiel Profesional</h3>
                            <p class="text-sm text-gray-500">Configura tu presencia en la plataforma</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Especialidad Principal</label>
                            <input type="text" value="Consultoría IT y Desarrollo" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ubicación / Base</label>
                            <input type="text" value="Montevideo, Uruguay" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-2xl">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Servicios Ofrecidos</h3>
                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition">
                            + AGREGAR SERVICIO
                        </button>
                    </div>

                    <div class="space-y-3">
                        @php
                            $servicios = [
                                ['nombre' => 'Asesoría Laravel 11', 'precio' => '1200', 'duracion' => '45 min', 'mod' => 'Virtual'],
                                ['nombre' => 'Revisión de Arquitectura', 'precio' => '2500', 'duracion' => '90 min', 'mod' => 'Presencial']
                            ];
                        @endphp

                        @foreach($servicios as $s)
                        <div class="flex items-center justify-between p-4 border border-gray-100 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <div class="flex items-center gap-4">
                                <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $s['nombre'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $s['duracion'] }} • {{ $s['mod'] }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-blue-600 dark:text-blue-400">${{ $s['precio'] }}</p>
                                <button class="text-[10px] font-bold text-gray-400 hover:text-red-500 uppercase tracking-tight">Eliminar</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-2xl">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Reseñas de Clientes</h3>
                    <div class="flex items-center gap-2 mb-6">
                        <span class="text-3xl font-black text-gray-900 dark:text-white">4.9</span>
                        <div class="flex text-yellow-400">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </div>
                        <span class="text-sm text-gray-500">(15 servicios completados)</span>
                    </div>
                    
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-100 dark:border-gray-600">
                        <p class="text-sm italic text-gray-600 dark:text-gray-400">"Excelente profesional, muy puntual y claro en sus explicaciones."</p>
                        <p class="text-xs font-bold mt-2 text-gray-900 dark:text-white">- Juan Pérez</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>