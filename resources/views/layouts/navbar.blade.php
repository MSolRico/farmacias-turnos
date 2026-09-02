<header class="bg-[#fdfdfd] dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700 sticky top-0 z-50">

    <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">

        {{-- LOGO --}}
        <a
            href="{{ route('dashboard') }}" class="flex items-center space-x-3 no-underline">
            <div
                class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-lg font-bold">
                <x-application-logo />
            </div>

            <h1 class="text-lg font-bold leading-tight text-slate-900 dark:text-white m-0">
                Farmacias
                <br>
                <span class="font-medium text-emerald-700 dark:text-emerald-400">
                    de Turno
                </span>
            </h1>
        </a>


        {{-- NAVEGACIÓN --}}
        <nav class="hidden md:flex items-center space-x-8 text-sm font-medium">

            <a
                href="{{ route('dashboard') }}"
                class="text-emerald-700 dark:text-emerald-400 font-semibold border-b-3 border-emerald-600 dark:border-emerald-400 py-5 no-underline">
                Inicio
            </a>

            <a 
                href="{{ route('dashboard') }}#buscar"
                class="text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 transition no-underline">
                Buscar
            </a>

            <a
                href="#informacion"
                class="text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 transition no-underline">
                Información
            </a>

            <a
                href="#contacto"
                class="text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 transition no-underline">
                Contacto
            </a>

        </nav>


        {{-- ACCIONES DEL USUARIO --}}
        <div class="flex items-center space-x-3 text-xs font-semibold">

            {{-- Tema --}}
            <button
                type="button"
                @click="toggleTema()"
                class="w-8 h-8 rounded-full bg-gray-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-slate-700 transition"
                aria-label="Cambiar tema">

                {{-- Luna --}}
                <svg class="w-4 h-4 dark:hidden" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 22C17.5228 22 22 17.5228 22 12C22 11.5373 21.3065 11.4608 21.0672 11.8568C19.9289 13.7406 17.8615 15 15.5 15C11.9101 15 9 12.0899 9 8.5C9 6.13845 10.2594 4.07105 12.1432 2.93276C12.5392 2.69347 12.4627 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill="currentColor"></path>
                </svg>

                {{-- Sol --}}
                <svg class="hidden dark:block w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                    class="w-9 h-9 rounded-full bg-gray-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-slate-700 transition"
                    aria-label="Menú de usuario">
                    <x-icons.user />
                </button>


                {{-- DROPDOWN --}}
                <div
                    x-show="open"
                    x-cloak
                    @click.away="open = false"
                    x-transition
                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-lg overflow-hidden z-50">

                    @auth

                    <a
                        href="{{ route('profile.edit') }}"
                        class="flex items-center gap-2 px-4 py-3 text-sm text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700 no-underline">
                        <x-icons.user />
                        Perfil
                    </a>

                    <form
                        method="POST"
                        action="{{ route('logout') }}">
                        @csrf

                        <button
                            type="submit"
                            class="w-full flex items-center gap-2 px-4 py-3 text-sm text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700">
                            <x-icons.login />
                            Cerrar sesión
                        </button>
                    </form>

                    @else

                    @if(Route::has('login'))
                    <a
                        href="{{ route('login') }}"
                        class="flex items-center gap-2 px-4 py-3 text-sm text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700 no-underline">
                        <x-icons.login />
                        Iniciar sesión
                    </a>
                    @endif

                    @if(Route::has('register'))
                    <a
                        href="{{ route('register') }}"
                        class="flex items-center gap-2 px-4 py-3 text-sm text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700 no-underline">
                        <x-icons.register />
                        Registrarse
                    </a>
                    @endif

                    @endauth

                </div>

            </div>

        </div>

    </div>

</header>