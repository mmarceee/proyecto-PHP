<x-app-layout>
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <x-app-sidebar/>

        {{-- Contenido principal --}}
        <main
            class="flex-1 min-w-0 lg:ml-20"
            x-data="gestionResenas()"
            x-init="cargarResenas()"
        >
            <div class="border-b border-gray-700 bg-gray-800">
                <div class="max-w-7xl mx-auto pl-14 pr-6 py-6 sm:px-6 lg:px-8">
                    <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                        {{ __('Moderación de Reseñas') }}
                    </h2>
                </div>
            </div>

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                    {{-- Encabezado e Info --}}
                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Registro de Opiniones y Calificaciones
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Consulta y modera los comentarios y puntajes asignados tras la finalización de las reservas.
                            </p>
                        </div>
                    </div>

                    {{-- Barra de Filtros --}}
                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="filtroTipo = 'todas'"
                            :class="filtroTipo === 'todas' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'"
                            class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition"
                        >
                            Todas
                        </button>
                        <button
                            @click="filtroTipo = 'ClienteAProfesional'"
                            :class="filtroTipo === 'ClienteAProfesional' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'"
                            class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition"
                        >
                            Cliente a Profesional
                        </button>
                        <button
                            @click="filtroTipo = 'ProfesionalACliente'"
                            :class="filtroTipo === 'ProfesionalACliente' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'"
                            class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition"
                        >
                            Profesional a Cliente
                        </button>
                    </div>

                    {{-- Mensaje de Carga --}}
                    <div
                        x-show="cargando"
                        class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg"
                    >
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Cargando calificaciones del sistema...
                        </p>
                    </div>

                    {{-- Mensaje de Error --}}
                    <div
                        x-show="error"
                        class="p-4 sm:p-8 bg-red-50 dark:bg-red-900/30 border border-red-300 dark:border-red-700 shadow sm:rounded-lg"
                        style="display: none;"
                    >
                        <p class="text-sm text-red-700 dark:text-red-300" x-text="error"></p>
                    </div>

                    {{-- Tabla de Reseñas --}}
                    <div
                        x-show="!cargando"
                        class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg"
                        style="display: none;"
                    >
                        <div class="overflow-x-auto">
                            <table class="min-w-[900px] divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                            Fecha / Servicio
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                            Evaluador (Emisor)
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                            Evaluado (Receptor)
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                            Tipo
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                            Valoración
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                            Comentario
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                            Acción
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="resena in resenasFiltradas()" :key="resena.id">
                                        <tr class="bg-white dark:bg-gray-800">
                                            <td class="px-4 py-4 text-gray-900 dark:text-gray-100">
                                                <span class="block font-medium" x-text="resena.fecha"></span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="resena.servicio_nombre"></span>
                                            </td>

                                            <td class="px-4 py-4 text-gray-900 dark:text-gray-100">
                                                <span class="block font-medium" x-text="resena.evaluador_nombre"></span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 block" x-text="resena.evaluador_email"></span>
                                            </td>

                                            <td class="px-4 py-4 text-gray-900 dark:text-gray-100">
                                                <span class="block font-medium" x-text="resena.evaluado_nombre"></span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 block" x-text="resena.evaluado_email"></span>
                                            </td>

                                            <td class="px-4 py-4">
                                                <span
                                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                                    :class="resena.tipo_calificacion === 'ClienteAProfesional'
                                                        ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300'
                                                        : 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300'"
                                                    x-text="resena.tipo_calificacion === 'ClienteAProfesional' ? 'Cliente → Prof' : 'Prof → Cliente'"
                                                ></span>
                                            </td>

                                            <td class="px-4 py-4 text-yellow-500">
                                                <div class="flex items-center gap-0.5">
                                                    <template x-for="i in 5">
                                                        <svg class="w-4 h-4" :class="i <= resena.puntuacion ? 'fill-current' : 'text-gray-300 dark:text-gray-600'" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                    </template>
                                                    <span class="text-xs font-semibold ml-1.5 text-gray-700 dark:text-gray-300" x-text="resena.puntuacion"></span>
                                                </div>
                                            </td>

                                            <td class="px-4 py-4 text-gray-700 dark:text-gray-300 max-w-xs truncate" :title="resena.comentario">
                                                <span x-text="resena.comentario ?? '-'"></span>
                                            </td>

                                            <td class="px-4 py-4">
                                                <button
                                                    type="button"
                                                    class="rounded-md bg-red-600 px-3 py-1 text-xs font-semibold text-white hover:bg-red-700 transition"
                                                    @click="eliminarResena(resena.id)"
                                                >
                                                    Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div
                            x-show="resenasFiltradas().length === 0"
                            class="mt-6 rounded-md border border-gray-300 dark:border-gray-700 p-4 text-center text-gray-500"
                            style="display: none;"
                        >
                            No hay reseñas registradas para este filtro.
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

</x-app-layout>