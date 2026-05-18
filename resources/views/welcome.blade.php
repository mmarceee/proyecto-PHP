<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>GendarApp</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans">
        <div class="relative min-h-screen overflow-hidden bg-black text-white">
            <img 
                id="background" 
                class="fixed inset-0 z-0 h-screen w-screen object-cover opacity-50" 
                src="/fondoGendar.png" 
                alt="Fondo GendarApp" 
            />
            <div class="fixed inset-0 -z-10 bg-black/60"></div>

            <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-blue-500 selection:text-white">
                    <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                        <div class="flex items-center justify-center lg:col-start-2">
                        <div class="rounded-2xl border border-white/20 bg-black/35 p-3 shadow-lg backdrop-blur-md">
                            <img 
                                src="/gendarSinFondo.png" 
                                class="h-20 w-auto md:h-24" 
                                alt="GendarApp Logo"
                            >
                        </div>
                    </div>
                        <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                                <a 
                                    href="{{ route('login') }}"
                                    class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-blue-700"
                                >
                                    Iniciar sesión
                                </a>

                                <a 
                                    href="{{ route('register') }}"
                                    class="rounded-lg border border-white/30 bg-white/10 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/20"
                                >
                                    Registrarse
                                </a>
                            </div>
                    </header>

                    <main class="mt-10">
                        <section class="mx-auto max-w-5xl text-center">
                            <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl">
                                Seguridad, confianza y servicios al alcance de todos
                            </h1>

                            <p class="mx-auto mt-6 max-w-2xl text-lg text-gray-200">
                                GendarApp conecta clientes con profesionales capacitados, permitiendo consultar servicios,
                                contratar paquetes y gestionar solicitudes de forma simple y segura.
                            </p>
                        </section>

                        <section class="mx-auto mt-16 grid max-w-8xl gap-6 px-6 md:grid-cols-2 lg:px-8">
                            <div class="group rounded-xl border border-white/10 bg-black/50 p-6 shadow-xl backdrop-blur transition duration-300 hover:-translate-y-2 hover:border-blue-500/60 hover:bg-black/70 hover:shadow-blue-500/20">
                                <h2 class="text-xl font-semibold text-white">Para clientes</h2>
                                <p class="mt-4 text-sm leading-relaxed text-gray-300">
                                    Busca profesionales, consulta servicios disponibles y contrata paquetes según tus necesidades.
                                </p>
                            </div>

                            <div class="group rounded-xl border border-white/10 bg-black/50 p-6 shadow-xl backdrop-blur transition duration-300 hover:-translate-y-2 hover:border-blue-500/60 hover:bg-black/70 hover:shadow-blue-500/20">
                                <h2 class="text-xl font-semibold text-white">Para profesionales</h2>
                                <p class="mt-4 text-sm leading-relaxed text-gray-300">
                                    Gestiona tu perfil, disponibilidad, lugares de atención y los servicios que ofreces.
                                </p>
                            </div>
                        </section>
                    </main>

                    <footer class="py-10 text-center text-sm text-gray-300">
                        © {{ date('Y') }} GendarApp. Todos los derechos reservados.
                    </footer>
                </div>
            </div>
        </div>
    </body>
</html>
