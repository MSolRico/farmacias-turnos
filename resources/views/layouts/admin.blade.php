<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Administración') - Farmacias de Turno</title>

    <link rel="icon" href="{{ asset('capsule.svg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="bg-gray-50 text-slate-700 dark:bg-slate-950 dark:text-slate-200">

    <div class="min-h-screen flex">

        {{-- =====================================================
             BARRA LATERAL
        ====================================================== --}}
        <aside class="hidden lg:flex lg:flex-col w-64 bg-emerald-950 text-white shrink-0">

            {{-- Logo --}}
            <div class="h-20 flex items-center px-6 border-b border-emerald-900">

                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3">

                    <img
                        src="{{ asset('capsule.svg') }}"
                        alt="Farmacias de Turno"
                        class="w-8 h-8">

                    <div>
                        <p class="font-bold leading-tight">
                            Farmacias
                        </p>

                        <p class="text-xs text-emerald-300">
                            Administración
                        </p>
                    </div>

                </a>

            </div>


            {{-- Navegación --}}
            <nav class="flex-1 px-4 py-6 space-y-1">

                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl
                          bg-emerald-900/70 text-white">

                    <span class="text-lg">⌂</span>

                    <span class="text-sm font-medium">
                        Inicio
                    </span>

                </a>


                <a href="#"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl
                          text-emerald-100 hover:bg-emerald-900/60
                          transition">

                    <span class="text-lg">⌖</span>

                    <span class="text-sm font-medium">
                        Farmacias
                    </span>

                </a>


                <a href="#"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl
                          text-emerald-100 hover:bg-emerald-900/60
                          transition">

                    <span class="text-lg">▣</span>

                    <span class="text-sm font-medium">
                        Turnos
                    </span>

                </a>


                <a href="#"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl
                          text-emerald-100 hover:bg-emerald-900/60
                          transition">

                    <span class="text-lg">↻</span>

                    <span class="text-sm font-medium">
                        Importaciones
                    </span>

                </a>


                <a href="#"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl
                          text-emerald-100 hover:bg-emerald-900/60
                          transition">

                    <span class="text-lg">⚑</span>

                    <span class="text-sm font-medium">
                        Reportes
                    </span>

                </a>

            </nav>


            {{-- Usuario --}}
            <div class="p-4 border-t border-emerald-900">

                <div class="px-4 py-3 mb-2">

                    <p class="text-sm font-semibold truncate">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="text-xs text-emerald-300">
                        Administrador
                    </p>

                </div>

                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl
                          text-emerald-100 hover:bg-emerald-900/60
                          transition">

                    <span>←</span>

                    <span class="text-sm">
                        Volver al sitio
                    </span>

                </a>

            </div>

        </aside>


        {{-- =====================================================
             CONTENIDO
        ====================================================== --}}
        <div class="flex-1 min-w-0 flex flex-col">

            {{-- Header --}}
            <header class="h-20 bg-white dark:bg-slate-900
                           border-b border-slate-200 dark:border-slate-800
                           flex items-center justify-between px-6">

                <div class="lg:hidden">

                    <a href="{{ route('admin.dashboard') }}"
                       class="font-bold text-emerald-800 dark:text-emerald-400">
                        Farmacias de Turno
                    </a>

                </div>

                <div class="hidden lg:block">
                    @yield('header')
                </div>

                <div class="flex items-center gap-3">

                    <span class="hidden sm:block text-sm text-slate-500 dark:text-slate-400">
                        {{ auth()->user()->name }}
                    </span>

                    <div class="w-9 h-9 rounded-full bg-emerald-100
                                dark:bg-emerald-900/50
                                flex items-center justify-center
                                text-emerald-700 dark:text-emerald-400
                                font-semibold">

                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}

                    </div>

                </div>

            </header>


            {{-- Contenido de cada página --}}
            <main class="flex-1">
                @yield('content')
            </main>

        </div>

    </div>

    @stack('scripts')

</body>

</html>