<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight italic">
            {{ __('Mi Perfil Profesional y Servicios') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Sección 1: Información General (Requerimiento UTEC)[cite: 2, 3] -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg border border-gray-100 dark:border-gray-700">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Información del Perfil</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Actualiza tu descripción, especialidad y nombre comercial.
                    </p>

                    <div class="mt-6 space-y-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nombre Comercial (Opcional)</label>
                            <input type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5" placeholder="Ej: Centro de Bienestar Integral[cite: 2]">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Descripción / Bio</label>
                            <textarea rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5" placeholder="Cuéntale a tus clientes sobre tu experiencia..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección 2: Catálogo de Servicios (Múltiples Servicios y Precios)[cite: 2, 3] -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg border border-gray-100 dark:border-gray-700">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Mis Servicios</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Gestiona los servicios que ofreces, sus precios y duración[cite: 2, 3].</p>
                    </div>
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-blue-700 transition">
                        + Nuevo Servicio
                    </button>
                </div>

                <!-- Lista de Servicios (Table/List) -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Servicio</th>
                                <th class="px-6 py-3">Precio</th>
                                <th class="px-6 py-3">Duración</th>
                                <th class="px-6 py-3">Modalidad</th>
                                <th class="px-6 py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">Consultoría IT Inicial</td>
                                <td class="px-6 py-4">$1.500</td>
                                <td class="px-6 py-4">60 min</td>
                                <td class="px-6 py-4">
                                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-0.5 rounded">Remota</span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="#" class="text-blue-600 dark:text-blue-500 hover:underline">Editar</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sección 3: Reputación y Calificaciones (Visual)[cite: 2, 3] -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg border border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Reputación</h3>
                <div class="flex items-center gap-4">
                    <div class="text-4xl font-black text-gray-900 dark:text-white">4.8</div>
                    <div>
                        <div class="flex text-yellow-400">
                            <!-- SVG Estrellas -->
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <!-- Repetir estrellas... -->
                        </div>
                        <p class="text-xs text-gray-500">Basado en 24 reseñas[cite: 2, 3]</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>