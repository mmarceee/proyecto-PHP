<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @auth
            <meta name="user-id" content="{{ auth()->id() }}">
            <meta name="profesional-id" content="{{ auth()->user()->profesional?->id ?? '' }}">
        @endauth
        
        @stack('meta-dinamica')

        <title>{{ config('app.name', 'GendarApp') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <link rel="manifest" href="/manifest.webmanifest">
        <meta name="theme-color" content="#0f172a">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div
            id="offline-banner"
            class="hidden fixed top-0 left-0 right-0 z-50 bg-red-600 text-white text-sm font-semibold text-center px-4 py-3 shadow-lg"
        >
            Sin conexion. Puedes ver informacion ya cargada, pero no realizar reservas ni cambios.
        </div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <livewire:layout.navigation />

            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>