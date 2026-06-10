<x-app-layout>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <div class="flex min-h-screen">

            {{-- Sidebar --}}
            <x-app-sidebar />

            {{-- Contenido principal --}}
            <main class="flex-1 min-w-0 lg:ml-20">
                <div class="py-8 px-4 sm:px-6 lg:px-8"
                     data-categorias-admin>

                    {{-- Encabezado --}}
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Administración de categorías
                        </h1>

                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                            Desde esta sección podés crear, editar, activar y desactivar las categorías disponibles en GendarApp.
                        </p>
                    </div>

                    {{-- Mensajes --}}
                    <div id="mensajeCategorias" class="hidden mb-4 rounded-lg px-4 py-3 text-sm"></div>

                    {{-- Formulario --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 mb-6">
                        <h2 id="tituloFormularioCategoria"
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Nueva categoría
                        </h2>

                        <form id="formCategoria" class="space-y-4">
                            <input type="hidden" id="categoriaId">

                            <div>
                                <label for="nombreCategoria"
                                       class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nombre
                                </label>

                                <input type="text"
                                       id="nombreCategoria"
                                       name="nombre"
                                       required
                                       maxlength="100"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Ej: Psicología, Nutrición, Masajes">
                            </div>

                            <div>
                                <label for="descripcionCategoria"
                                       class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Descripción
                                </label>

                                <textarea id="descripcionCategoria"
                                          name="descripcion"
                                          rows="3"
                                          maxlength="500"
                                          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                                          placeholder="Breve descripción de la categoría"></textarea>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                                <button type="button"
                                        id="btnCancelarEdicionCategoria"
                                        class="hidden px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                    Cancelar edición
                                </button>

                                <button type="submit"
                                        id="btnGuardarCategoria"
                                        data-requires-online
                                        class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700">
                                    Guardar categoría
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Listado --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Categorías existentes
                            </h2>

                            <button type="button"
                                    id="btnRecargarCategorias"
                                    class="px-3 py-2 rounded-lg text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                Recargar
                            </button>
                        </div>

                        <div id="estadoCargaCategorias"
                             class="text-sm text-gray-500 dark:text-gray-400">
                            Cargando categorías...
                        </div>

                        <div id="contenedorTablaCategorias" class="hidden overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Nombre
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Descripción
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Estado
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>

                                <tbody id="tablaCategorias"
                                       class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    {{-- El JS carga las categorías acá --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>
</x-app-layout>
