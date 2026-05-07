<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Búsqueda de Servicios Profesionales') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <form class="max-w-2xl mx-auto" x-data="{ open: false }">
                        <div class="flex shadow-xs rounded-base -space-x-0.5">
                            <label for="search-dropdown" class="block mb-2.5 text-sm font-medium text-heading sr-only">Search</label>
                            
                            <button id="dropdown-button" @click="open = !open" type="button" class="inline-flex items-center shrink-0 z-10 text-body bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 px-4 py-2.5 rounded-s-lg hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none">
                                <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.143 4H4.857A.857.857 0 0 0 4 4.857v4.286c0 .473.384.857.857.857h4.286A.857.857 0 0 0 10 9.143V4.857A.857.857 0 0 0 9.143 4Zm10 0h-4.286a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286A.857.857 0 0 0 20 9.143V4.857A.857.857 0 0 0 19.143 4Zm-10 10H4.857a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286a.857.857 0 0 0 .857-.857v-4.286A.857.857 0 0 0 9.143 14Zm10 0h-4.286a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286a.857.857 0 0 0 .857-.857v-4.286a.857.857 0 0 0-.857-.857Z"/></svg>
                                Todas las categorías
                                <svg class="w-4 h-4 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute z-10 mt-12 bg-white border border-gray-300 rounded-lg shadow-lg w-44">
                                <ul class="p-2 text-sm text-gray-700">
                                    <li><a href="#" class="block p-2 hover:bg-gray-100">Consultoría</a></li>
                                    <li><a href="#" class="block p-2 hover:bg-gray-100">Salud no clínica</a></li>
                                    <li><a href="#" class="block p-2 hover:bg-gray-100">Servicios Técnicos</a></li>
                                    <li><a href="#" class="block p-2 hover:bg-gray-100">Entrenamiento</a></li>
                                </ul>
                            </div>
                            <input type="search" id="search-dropdown" class="px-3 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-sm block w-full placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500" placeholder="Buscar consultoría, entrenamiento, salud..." required>
                            
                            <button type="submit" class="inline-flex items-center text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-e-lg text-sm px-4 py-2.5 focus:outline-none">
                                <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                                Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
