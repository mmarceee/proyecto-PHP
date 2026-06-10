<x-app-layout>
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <x-app-sidebar />

        {{-- Contenido principal --}}
        <main x-data="paquetesVendidosProfesional" class="flex-1 min-w-0 lg:ml-20 bg-gray-900 text-gray-100">
            <div class="border-b border-gray-700 bg-gray-800">
                <div class="max-w-7xl mx-auto pl-14 pr-6 py-6 sm:px-6 lg:px-8">
                    <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                        {{ __('Paquetes vendidos') }}
                    </h2>
                </div>
            </div>

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                    <div x-show="cargando" class="text-center py-12 text-gray-400 italic">
                        Cargando paquetes vendidos...
                    </div>

                    <div x-show="error" x-transition class="p-4 bg-red-950/40 border border-red-800 text-red-200 text-sm rounded-xl" x-text="error" style="display: none;"></div>

                    <template x-if="!cargando && !error && ventas.length === 0">
                        <div class="p-12 text-center bg-gray-800 border border-dashed border-gray-700 rounded-2xl text-gray-400">
                            Todavía no vendiste ningún paquete.
                        </div>
                    </template>

                    <div x-show="!cargando && !error && ventas.length > 0" class="bg-gray-800 border border-gray-700 rounded-2xl overflow-hidden" style="display: none;">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-700">
                                <thead class="bg-gray-900/60">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-400">
                                            Cliente
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-400">
                                            Paquete
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-400">
                                            Servicio
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-400">
                                            Sesiones
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-400">
                                            Estado
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-400">
                                            Compra
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-700">
                                    <template x-for="venta in ventas" :key="venta.id">
                                        <tr class="hover:bg-gray-700/40 transition">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="font-semibold text-white" x-text="nombreCliente(venta)"></div>
                                                <div class="text-xs text-gray-500" x-text="correoCliente(venta)"></div>
                                            </td>

                                            <td class="px-6 py-4">
                                                <div class="font-semibold text-white" x-text="nombrePaquete(venta)"></div>
                                            </td>

                                            <td class="px-6 py-4 text-gray-300" x-text="nombreServicio(venta)"></td>

                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-300">
                                                    <span class="font-bold text-white" x-text="venta.sesiones_disponibles"></span>
                                                    disponibles
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    <span x-text="venta.sesiones_consumidas"></span>
                                                    consumidas
                                                </div>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex rounded-md px-2 py-1 text-xs font-bold uppercase tracking-wider"
                                                    :class="venta.estado_paquete === 'activo'
                                                        ? 'bg-green-900/50 text-green-300 border border-green-700'
                                                        : 'bg-gray-700 text-gray-300 border border-gray-600'"
                                                    x-text="venta.estado_paquete">
                                                </span>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-gray-300" x-text="fechaCompra(venta)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</x-app-layout>