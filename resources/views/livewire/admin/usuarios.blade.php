<x-app-layout>
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <x-app-sidebar/>

        {{-- Contenido principal --}}
        <main
            class="flex-1 min-w-0 lg:ml-20"
            x-data="gestionUsuarios('{{ route('api.admin.usuarios.index') }}', '{{ url('/api/admin/usuarios') }}')"
            x-init="cargarUsuarios()"
        >
            <div class="border-b border-gray-700 bg-gray-800">
                <div class="max-w-7xl mx-auto pl-14 pr-6 py-6 sm:px-6 lg:px-8">
                    <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                        {{ __('Gestión de usuarios') }}
                    </h2>
                </div>
            </div>

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                    {{-- Encabezado --}}
                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Usuarios registrados
                            </h3>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Desde este panel el administrador puede consultar los usuarios del sistema y gestionar su estado.
                            </p>
                        </div>
                    </div>

                    {{-- Mensaje de carga --}}
                    <div
                        x-show="cargando"
                        class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg"
                    >
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Cargando usuarios...
                        </p>
                    </div>

                    {{-- Mensaje de error --}}
                    <div
                        x-show="error"
                        class="p-4 sm:p-8 bg-red-50 dark:bg-red-900/30 border border-red-300 dark:border-red-700 shadow sm:rounded-lg"
                    >
                        <p class="text-sm text-red-700 dark:text-red-300" x-text="error"></p>
                    </div>

                    {{-- Tabla de usuarios --}}
                    <div
                        x-show="!cargando"
                        class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg"
                    >
                        <div class="overflow-x-auto">
                            <table class="min-w-[900px] divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                            ID
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                            Nombre
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                            Email
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                            Teléfono
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                            Estado
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                            Perfiles
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="usuario in usuarios" :key="usuario.id">
                                        <tr class="bg-white dark:bg-gray-800">
                                            <td class="px-4 py-3 text-gray-900 dark:text-gray-100" x-text="usuario.id"></td>

                                            <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                                <span x-text="usuario.name"></span>
                                                <span x-text="usuario.apellido"></span>
                                            </td>

                                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300" x-text="usuario.email"></td>

                                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300" x-text="usuario.telefono ?? '-'"></td>

                                            <td class="px-4 py-3">
                                                <span
                                                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                                    :class="usuario.estado_usuario === 'activo'
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                                                    x-text="usuario.estado_usuario"
                                                ></span>
                                            </td>

                                            <td class="px-4 py-3">
                                                <div class="flex flex-wrap gap-2">
                                                    <span
                                                        x-show="usuario.es_cliente"
                                                        class="inline-flex rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-2 py-1 text-xs font-semibold"
                                                    >
                                                        Cliente
                                                    </span>

                                                    <span
                                                        x-show="usuario.es_profesional"
                                                        class="inline-flex rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 px-2 py-1 text-xs font-semibold"
                                                    >
                                                        Profesional
                                                    </span>

                                                    <span
                                                        x-show="usuario.es_admin"
                                                        class="inline-flex rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 px-2 py-1 text-xs font-semibold"
                                                    >
                                                        Admin
                                                    </span>
                                                </div>
                                            </td>

                                            <td class="px-4 py-3">
                                                <div class="flex flex-wrap gap-2">
                                                    <button
                                                        x-show="usuario.estado_usuario === 'activo'"
                                                        type="button"
                                                        class="rounded-md bg-red-600 px-3 py-1 text-xs font-semibold text-white hover:bg-red-700"
                                                        @click="bloquearUsuario(usuario.id)"
                                                    >
                                                        Bloquear
                                                    </button>

                                                    <button
                                                        x-show="usuario.estado_usuario === 'bloqueado'"
                                                        type="button"
                                                        class="rounded-md bg-green-600 px-3 py-1 text-xs font-semibold text-white hover:bg-green-700"
                                                        @click="desbloquearUsuario(usuario.id)"
                                                    >
                                                        Desbloquear
                                                    </button>

                                                    <button
                                                        x-show="!usuario.es_admin"
                                                        type="button"
                                                        class="rounded-md bg-gray-700 px-3 py-1 text-xs font-semibold text-white hover:bg-gray-600"
                                                        @click="hacerAdmin(usuario.id)"
                                                    >
                                                        Hacer admin
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div
                            x-show="usuarios.length === 0"
                            class="mt-6 rounded-md border border-gray-300 dark:border-gray-700 p-4"
                        >
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                No hay usuarios registrados.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</x-app-layout>