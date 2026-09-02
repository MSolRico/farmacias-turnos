<!DOCTYPE html>
<html lang="es" x-data="tema">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Administración') · Farmacias de Turno</title>

    <link rel="icon" href="{{ asset('capsule.svg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    <script>
        if (
            localStorage.getItem('tema') === 'dark' ||
            (!localStorage.getItem('tema') &&
                window.matchMedia('(prefers-color-scheme: dark)').matches)
        ) {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>

<body class="bg-gray-50 text-slate-700 dark:bg-slate-950 dark:text-slate-200 antialiased">

    {{-- =========================================================
         HEADER
         ========================================================= --}}
    <header x-data="{ menuAbierto: false }" class="bg-[#fdfdfd] dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700 sticky top-0 z-50">

        <div class="max-w-7xl mx-auto px-6 h-15 flex items-center justify-between gap-6">

            {{-- =====================================================
                 MARCA
             ===================================================== --}}
            <div class="flex items-center">

                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 no-underline">

                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-lg font-bold">
                        <x-application-logo />
                    </div>

                    <h1 class="text-lg font-bold leading-tight text-slate-900 dark:text-white m-0">
                        Panel
                        <br>
                        <span class="font-medium text-emerald-700 dark:text-emerald-400">
                            de control
                        </span>
                    </h1>

                </a>

            </div>


            {{-- =====================================================
             NAVEGACIÓN
             ===================================================== --}}
            <nav class="hidden md:flex items-center gap-6 lg:gap-8 text-sm font-medium">

                <a href="{{ route('admin.dashboard') }}"
                    class="{{ request()->routeIs('admin.dashboard')
                    ? 'text-emerald-700 dark:text-emerald-400 font-semibold border-b-2 border-emerald-600 dark:border-emerald-400'
                    : 'text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400'
                }} py-5 transition no-underline">
                    Inicio
                </a>

                <a href="{{ route('admin.farmacias.index') }}"
                    class="{{ request()->routeIs('admin.farmacias.*')
                    ? 'text-emerald-700 dark:text-emerald-400 font-semibold border-b-2 border-emerald-600 dark:border-emerald-400'
                    : 'text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400'
                }} py-5 transition no-underline">
                    Farmacias
                </a>

                <a href="{{ route('admin.turnos.index') }}"
                    class="{{ request()->routeIs('admin.turnos.*')
                    ? 'text-emerald-700 dark:text-emerald-400 font-semibold border-b-2 border-emerald-600 dark:border-emerald-400'
                    : 'text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400'
                }} py-5 transition no-underline">
                    Turnos
                </a>

                <a href="{{ route('admin.importaciones.index') }}"
                    class="{{ request()->routeIs('admin.importaciones.*')
                    ? 'text-emerald-700 dark:text-emerald-400 font-semibold border-b-2 border-emerald-600 dark:border-emerald-400'
                    : 'text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400'
                }} py-5 transition no-underline">
                    Importaciones
                </a>

                <a href="{{ route('admin.reportes.index') }}"
                    class="{{ request()->routeIs('admin.reportes.*')
                    ? 'text-emerald-700 dark:text-emerald-400 font-semibold border-b-2 border-emerald-600 dark:border-emerald-400'
                    : 'text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400'
                }} py-5 transition no-underline">
                    Reportes
                </a>

            </nav>


            {{-- =====================================================
             ACCIONES
             ===================================================== --}}
            <div class="flex items-center gap-3">

                <button
                    type="button"
                    @click="menuAbierto = !menuAbierto"
                    class="md:hidden w-9 h-9 rounded-full
           bg-gray-100 dark:bg-slate-800
           flex items-center justify-center
           text-slate-600 dark:text-slate-300
           hover:bg-gray-200 dark:hover:bg-slate-700
           transition"
                    aria-label="Abrir menú">

                    <svg
                        x-show="!menuAbierto"
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2">

                        <path d="M4 6h16"></path>
                        <path d="M4 12h16"></path>
                        <path d="M4 18h16"></path>

                    </svg>

                    <svg
                        x-show="menuAbierto"
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2">

                        <path d="M6 6l12 12"></path>
                        <path d="M18 6l-12 12"></path>

                    </svg>

                </button>

                {{-- TEMA --}}
                <button
                    type="button"
                    @click="toggleTema()"
                    class="w-8 h-8 rounded-full bg-gray-100 dark:bg-slate-800
                       flex items-center justify-center
                       text-slate-600 dark:text-slate-300
                       hover:bg-gray-200 dark:hover:bg-slate-700
                       transition"
                    aria-label="Cambiar tema">

                    <svg
                        class="w-4 h-4 dark:hidden"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg">

                        <path
                            d="M12 22C17.5228 22 22 17.5228 22 12C22 11.5373 21.3065 11.4608 21.0672 11.8568C19.9289 13.7406 17.8615 15 15.5 15C11.9101 15 9 12.0899 9 8.5C9 6.13845 10.2594 4.07105 12.1432 2.93276C12.5392 2.69347 12.4627 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                            fill="currentColor">
                        </path>

                    </svg>

                    <svg
                        class="hidden dark:block w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2">

                        <circle cx="12" cy="12" r="4"></circle>
                        <path d="M12 2v2"></path>
                        <path d="M12 20v2"></path>
                        <path d="m4.93 4.93 1.41 1.41"></path>
                        <path d="m17.66 17.66 1.41 1.41"></path>
                        <path d="M2 12h2"></path>
                        <path d="M20 12h2"></path>
                        <path d="m6.34 17.66-1.41 1.41"></path>
                        <path d="m19.07 4.93-1.41 1.41"></path>

                    </svg>

                </button>


                {{-- USUARIO --}}
                <div
                    x-data="{ open: false }"
                    class="relative">

                    <button
                        type="button"
                        @click="open = !open"
                        class="w-9 h-9 rounded-full bg-gray-100 dark:bg-slate-800
                           flex items-center justify-center
                           text-slate-600 dark:text-slate-300
                           hover:bg-gray-200 dark:hover:bg-slate-700
                           transition"
                        aria-label="Menú de usuario">

                        <x-icons.user />

                    </button>


                    {{-- DROPDOWN --}}
                    <div
                        x-show="open"
                        x-cloak
                        @click.away="open = false"
                        x-transition
                        class="absolute right-0 mt-2 w-52
                           bg-white dark:bg-slate-800
                           border border-gray-200 dark:border-slate-700
                           rounded-xl shadow-lg overflow-hidden z-50">

                        <div class="px-4 py-3 border-b border-gray-100 dark:border-slate-700">

                            <p class="text-sm font-semibold text-slate-800 dark:text-white">
                                {{ auth()->user()->name }}
                            </p>

                            <p class="text-xs text-emerald-600 dark:text-emerald-400">
                                Administrador
                            </p>

                        </div>


                        <a
                            href="{{ route('profile.edit') }}"
                            class="flex items-center gap-2 px-4 py-3 text-sm
                               text-slate-700 dark:text-slate-200
                               hover:bg-gray-50 dark:hover:bg-slate-700
                               no-underline">

                            <x-icons.user />

                            Perfil

                        </a>

                        <form
                            method="POST"
                            action="{{ route('logout') }}">

                            @csrf

                            <button
                                type="submit"
                                class="w-full flex items-center gap-2 px-4 py-3 text-sm
                                   text-slate-700 dark:text-slate-200
                                   hover:bg-gray-50 dark:hover:bg-slate-700">

                                <x-icons.login />

                                Cerrar sesión

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

        <div
            x-show="menuAbierto"
            x-cloak
            x-transition
            class="md:hidden border-t border-gray-200 dark:border-slate-700
           bg-[#fdfdfd] dark:bg-slate-900">

            <nav class="max-w-7xl mx-auto px-6 py-3 space-y-1">

                <a
                    href="{{ route('admin.dashboard') }}"
                    @click="menuAbierto = false"
                    class="block px-4 py-3 rounded-xl text-sm font-medium no-underline
                   {{ request()->routeIs('admin.dashboard')
                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                        : 'text-slate-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                    Inicio
                </a>

                <a
                    href="{{ route('admin.farmacias.index') }}"
                    @click="menuAbierto = false"
                    class="block px-4 py-3 rounded-xl text-sm font-medium no-underline
                   {{ request()->routeIs('admin.farmacias.*')
                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                        : 'text-slate-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                    Farmacias
                </a>

                <a
                    href="{{ route('admin.turnos.index') }}"
                    @click="menuAbierto = false"
                    class="block px-4 py-3 rounded-xl text-sm font-medium no-underline
                   {{ request()->routeIs('admin.turnos.*')
                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                        : 'text-slate-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                    Turnos
                </a>

                <a
                    href="{{ route('admin.importaciones.index') }}"
                    @click="menuAbierto = false"
                    class="block px-4 py-3 rounded-xl text-sm font-medium no-underline
                   {{ request()->routeIs('admin.importaciones.*')
                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                        : 'text-slate-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                    Importaciones
                </a>

                <a
                    href="{{ route('admin.reportes.index') }}"
                    @click="menuAbierto = false"
                    class="block px-4 py-3 rounded-xl text-sm font-medium no-underline
                   {{ request()->routeIs('admin.reportes.*')
                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                        : 'text-slate-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
                    Reportes
                </a>

            </nav>

        </div>

    </header>


    {{-- =========================================================
         CONTENIDO
         ========================================================= --}}
    <main class="max-w-7xl mx-auto px-6">

        @yield('content')

    </main>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('tema', () => ({
                toggleTema() {
                    const html = document.documentElement;

                    html.classList.toggle('dark');

                    localStorage.setItem(
                        'tema',
                        html.classList.contains('dark') ?
                        'dark' :
                        'light'
                    );
                }
            }));
        });
    </script>

    @stack('scripts')

</body>

</html>