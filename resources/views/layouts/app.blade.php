<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Farmacias de Turno')</title>

    <link rel="icon" href="{{ asset('capsule.svg') }}" type="image/svg+xml">

    {{-- Fuente Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Leaflet --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="bg-gray-50 text-slate-700 antialiased flex flex-col min-h-screen">

    {{-- =====================================================
         NAVBAR
    ====================================================== --}}
    <header class="bg-[#fdfdfd] border-b border-gray-200 sticky top-0 z-50">

        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">

            {{-- LOGO --}}
            <a
                href="{{ route('dashboard') }}" class="flex items-center space-x-3 no-underline">
                <div
                    class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-lg font-bold">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100%" height="100%">
                        <!-- Marco exterior con esquinas redondeadas -->
                        <rect x="8" y="8" width="84" height="84" rx="22" ry="22" fill="none" stroke="#007A53" stroke-width="3.5" opacity="0.35" />

                        <!-- Esquinas acentuadas del marco -->
                        <path d="M 28 8 H 18 A 10 10 0 0 0 8 18 V 28" fill="none" stroke="#007A53" stroke-width="4.5" stroke-linecap="round" />
                        <path d="M 72 8 H 82 A 10 10 0 0 1 92 18 V 28" fill="none" stroke="#007A53" stroke-width="4.5" stroke-linecap="round" />
                        <path d="M 8 72 V 82 A 10 10 0 0 0 18 92 H 28" fill="none" stroke="#007A53" stroke-width="4.5" stroke-linecap="round" />
                        <path d="M 92 72 V 82 A 10 10 0 0 1 82 92 H 72" fill="none" stroke="#007A53" stroke-width="4.5" stroke-linecap="round" />

                        <!-- Cruz Verde Central -->
                        <path d="M 38 22 H 62 V 38 H 78 V 62 H 62 V 78 H 38 V 62 H 22 V 38 H 38 Z" fill="#007A53" rx="3" />
                    </svg>
                </div>

                <h1 class="text-lg font-bold leading-tight text-slate-900 m-0">
                    Farmacias
                    <br>
                    <span class="font-medium text-emerald-700">
                        de Turno
                    </span>
                </h1>
            </a>


            {{-- NAVEGACIÓN --}}
            <nav class="hidden md:flex items-center space-x-8 text-sm font-medium">

                <a
                    href="{{ route('dashboard') }}"
                    class="text-emerald-700 font-semibold border-b-3 border-emerald-600 py-5 no-underline">
                    Inicio
                </a>

                <a
                    href="{{ route('buscar') }}"
                    class="text-slate-600 hover:text-emerald-600 transition no-underline">
                    Buscar
                </a>

                <a
                    href="#mapa"
                    class="text-slate-600 hover:text-emerald-600 transition no-underline">
                    Mapa
                </a>

                <a
                    href="#informacion"
                    class="text-slate-600 hover:text-emerald-600 transition no-underline">
                    Información
                </a>

                <a
                    href="#contacto"
                    class="text-slate-600 hover:text-emerald-600 transition no-underline">
                    Contacto
                </a>

            </nav>


            {{-- ACCIONES DEL USUARIO --}}
            <div class="flex items-center space-x-3 text-xs font-semibold">

                {{-- Tema --}}
                <button
                    type="button"
                    class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-slate-600 hover:bg-gray-200 transition"
                    aria-label="Cambiar tema">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 11.5373 21.3065 11.4608 21.0672 11.8568C19.9289 13.7406 17.8615 15 15.5 15C11.9101 15 9 12.0899 9 8.5C9 6.13845 10.2594 4.07105 12.1432 2.93276C12.5392 2.69347 12.4627 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill="#000"></path>
                        </g>
                    </svg>
                </button>


                {{-- USUARIO --}}
                <div
                    x-data="{ open: false }"
                    class="relative">

                    <button
                        type="button"
                        @click="open = !open"
                        class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-slate-600 hover:bg-gray-200 transition"
                        aria-label="Menú de usuario">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <circle cx="12" cy="6" r="4" stroke="#000" stroke-width="1.5"></circle>
                                <path d="M19.9975 18C20 17.8358 20 17.669 20 17.5C20 15.0147 16.4183 13 12 13C7.58172 13 4 15.0147 4 17.5C4 19.9853 4 22 12 22C14.231 22 15.8398 21.8433 17 21.5634" stroke="#000" stroke-width="1.5" stroke-linecap="round"></path>
                            </g>
                        </svg>
                    </button>


                    {{-- DROPDOWN --}}
                    <div
                        x-show="open"
                        x-cloak
                        @click.away="open = false"
                        x-transition
                        class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden z-50">

                        @auth

                        <a
                            href="{{ route('profile.edit') }}"
                            class="flex items-center gap-2 px-4 py-3 text-sm text-slate-700 hover:bg-gray-50 no-underline">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <circle cx="12" cy="6" r="4" stroke="#000" stroke-width="1.5"></circle>
                                    <path d="M19.9975 18C20 17.8358 20 17.669 20 17.5C20 15.0147 16.4183 13 12 13C7.58172 13 4 15.0147 4 17.5C4 19.9853 4 22 12 22C14.231 22 15.8398 21.8433 17 21.5634" stroke="#000" stroke-width="1.5" stroke-linecap="round"></path>
                                </g>
                            </svg>
                            Perfil
                        </a>

                        <form
                            method="POST"
                            action="{{ route('logout') }}">
                            @csrf

                            <button
                                type="submit"
                                class="flex items-center gap-2 px-4 py-3 text-sm text-slate-700 hover:bg-gray-50">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path d="M11.0005 15.9995L15.0005 11.9995M15.0005 11.9995L11.0005 7.99951M15.0005 11.9995H3.00049M11.0005 2.99951H17.7997C18.9198 2.99951 19.4799 2.99951 19.9077 3.2175C20.284 3.40925 20.59 3.71521 20.7817 4.09153C20.9997 4.51935 20.9997 5.07941 20.9997 6.19951V17.7995C20.9997 18.9196 20.9997 19.4797 20.7817 19.9075C20.59 20.2838 20.284 20.5898 19.9077 20.7815C19.4799 20.9995 18.9198 20.9995 17.7997 20.9995H11.0005" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </g>
                                </svg>
                                Cerrar sesión
                            </button>
                        </form>

                        @else

                        @if(Route::has('login'))
                        <a
                            href="{{ route('login') }}"
                            class="flex items-center gap-2 px-4 py-3 text-sm text-slate-700 hover:bg-gray-50 no-underline">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path d="M11.0005 15.9995L15.0005 11.9995M15.0005 11.9995L11.0005 7.99951M15.0005 11.9995H3.00049M11.0005 2.99951H17.7997C18.9198 2.99951 19.4799 2.99951 19.9077 3.2175C20.284 3.40925 20.59 3.71521 20.7817 4.09153C20.9997 4.51935 20.9997 5.07941 20.9997 6.19951V17.7995C20.9997 18.9196 20.9997 19.4797 20.7817 19.9075C20.59 20.2838 20.284 20.5898 19.9077 20.7815C19.4799 20.9995 18.9198 20.9995 17.7997 20.9995H11.0005" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </g>
                            </svg>
                            Iniciar sesión
                        </a>
                        @endif

                        @if(Route::has('register'))
                        <a
                            href="{{ route('register') }}"
                            class="flex items-center gap-2 px-4 py-3 text-sm text-slate-700 hover:bg-gray-50 no-underline">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <circle cx="12" cy="6" r="4" stroke="#000000" stroke-width="1.5"></circle>
                                    <path d="M20.4141 18.5H18.9999M18.9999 18.5H17.5857M18.9999 18.5L18.9999 17.0858M18.9999 18.5L18.9999 19.9142" stroke="#000000" stroke-width="1.5" stroke-linecap="round"></path>
                                    <path d="M12 13C14.6083 13 16.8834 13.8152 18.0877 15.024M15.5841 20.4366C14.5358 20.7944 13.3099 21 12 21C8.13401 21 5 19.2091 5 17C5 15.6407 6.18652 14.4398 8 13.717" stroke="#000000" stroke-width="1.5" stroke-linecap="round"></path>
                                </g>
                            </svg>
                            Registrarse
                        </a>
                        @endif

                        @endauth

                    </div>

                </div>

            </div>

        </div>

    </header>


    {{-- =====================================================
         MENSAJES DE SESIÓN
    ====================================================== --}}

    @if(session('success'))
    <div class="max-w-7xl mx-auto px-6 pt-4 w-full">

        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm">
            {{ session('success') }}
        </div>

    </div>
    @endif


    @if(session('warning'))
    <div class="max-w-7xl mx-auto px-6 pt-4 w-full">

        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl px-4 py-3 text-sm">
            {{ session('warning') }}
        </div>

    </div>
    @endif


    @if(session('error'))
    <div class="max-w-7xl mx-auto px-6 pt-4 w-full">

        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
            {{ session('error') }}
        </div>

    </div>
    @endif


    {{-- =====================================================
         CONTENIDO
    ====================================================== --}}

    <main class="flex-1">
        @yield('content')
    </main>


    {{-- =====================================================
         FOOTER
    ====================================================== --}}

    <footer
        id="contacto"
        class="bg-emerald-950 text-white text-xs py-5 border-t border-emerald-900">

        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">

            <p class="text-emerald-200/60 mb-0">
                © {{ date('Y') }} Farmacias de Turno.
                Todos los derechos reservados.
            </p>

            <div class="flex items-center space-x-6 text-emerald-200/80 font-medium">

                <a
                    href="#"
                    class="hover:text-white transition no-underline text-inherit">
                    Términos y condiciones
                </a>

                <span class="text-emerald-800">|</span>

                <a
                    href="#"
                    class="hover:text-white transition no-underline text-inherit">
                    Política de privacidad
                </a>

            </div>

            <div class="flex items-center space-x-4 text-emerald-200/80 text-sm">

                <a href="#" class="hover:text-white no-underline text-inherit">
                    <svg class="w-5 h-5" fill="#ffffff" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path d="M12 2.03998C6.5 2.03998 2 6.52998 2 12.06C2 17.06 5.66 21.21 10.44 21.96V14.96H7.9V12.06H10.44V9.84998C10.44 7.33998 11.93 5.95998 14.22 5.95998C15.31 5.95998 16.45 6.14998 16.45 6.14998V8.61998H15.19C13.95 8.61998 13.56 9.38998 13.56 10.18V12.06H16.34L15.89 14.96H13.56V21.96C15.9164 21.5878 18.0622 20.3855 19.6099 18.57C21.1576 16.7546 22.0054 14.4456 22 12.06C22 6.52998 17.5 2.03998 12 2.03998Z"></path>
                        </g>
                    </svg>
                </a>

                <a href="#" class="hover:text-white no-underline text-inherit">
                    <svg class="w-4 h-4" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <title>instagram [#fff167]</title>
                            <desc>Created with Sketch.</desc>
                            <defs> </defs>
                            <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <g id="Dribbble-Light-Preview" transform="translate(-340.000000, -7439.000000)" fill="#fff">
                                    <g id="icons" transform="translate(56.000000, 160.000000)">
                                        <path d="M289.869652,7279.12273 C288.241769,7279.19618 286.830805,7279.5942 285.691486,7280.72871 C284.548187,7281.86918 284.155147,7283.28558 284.081514,7284.89653 C284.035742,7285.90201 283.768077,7293.49818 284.544207,7295.49028 C285.067597,7296.83422 286.098457,7297.86749 287.454694,7298.39256 C288.087538,7298.63872 288.809936,7298.80547 289.869652,7298.85411 C298.730467,7299.25511 302.015089,7299.03674 303.400182,7295.49028 C303.645956,7294.859 303.815113,7294.1374 303.86188,7293.08031 C304.26686,7284.19677 303.796207,7282.27117 302.251908,7280.72871 C301.027016,7279.50685 299.5862,7278.67508 289.869652,7279.12273 M289.951245,7297.06748 C288.981083,7297.0238 288.454707,7296.86201 288.103459,7296.72603 C287.219865,7296.3826 286.556174,7295.72155 286.214876,7294.84312 C285.623823,7293.32944 285.819846,7286.14023 285.872583,7284.97693 C285.924325,7283.83745 286.155174,7282.79624 286.959165,7281.99226 C287.954203,7280.99968 289.239792,7280.51332 297.993144,7280.90837 C299.135448,7280.95998 300.179243,7281.19026 300.985224,7281.99226 C301.980262,7282.98483 302.473801,7284.28014 302.071806,7292.99991 C302.028024,7293.96767 301.865833,7294.49274 301.729513,7294.84312 C300.829003,7297.15085 298.757333,7297.47145 289.951245,7297.06748 M298.089663,7283.68956 C298.089663,7284.34665 298.623998,7284.88065 299.283709,7284.88065 C299.943419,7284.88065 300.47875,7284.34665 300.47875,7283.68956 C300.47875,7283.03248 299.943419,7282.49847 299.283709,7282.49847 C298.623998,7282.49847 298.089663,7283.03248 298.089663,7283.68956 M288.862673,7288.98792 C288.862673,7291.80286 291.150266,7294.08479 293.972194,7294.08479 C296.794123,7294.08479 299.081716,7291.80286 299.081716,7288.98792 C299.081716,7286.17298 296.794123,7283.89205 293.972194,7283.89205 C291.150266,7283.89205 288.862673,7286.17298 288.862673,7288.98792 M290.655732,7288.98792 C290.655732,7287.16159 292.140329,7285.67967 293.972194,7285.67967 C295.80406,7285.67967 297.288657,7287.16159 297.288657,7288.98792 C297.288657,7290.81525 295.80406,7292.29716 293.972194,7292.29716 C292.140329,7292.29716 290.655732,7290.81525 290.655732,7288.98792" id="instagram-[#fff167]"> </path>
                                    </g>
                                </g>
                            </g>
                        </g>
                    </svg>
                </a>

                <a href="#" class="hover:text-white no-underline text-inherit">
                    <svg class="w-4 h-4" fill="#ffffff" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <title>whatsapp</title>
                            <path d="M26.576 5.363c-2.69-2.69-6.406-4.354-10.511-4.354-8.209 0-14.865 6.655-14.865 14.865 0 2.732 0.737 5.291 2.022 7.491l-0.038-0.070-2.109 7.702 7.879-2.067c2.051 1.139 4.498 1.809 7.102 1.809h0.006c8.209-0.003 14.862-6.659 14.862-14.868 0-4.103-1.662-7.817-4.349-10.507l0 0zM16.062 28.228h-0.005c-0 0-0.001 0-0.001 0-2.319 0-4.489-0.64-6.342-1.753l0.056 0.031-0.451-0.267-4.675 1.227 1.247-4.559-0.294-0.467c-1.185-1.862-1.889-4.131-1.889-6.565 0-6.822 5.531-12.353 12.353-12.353s12.353 5.531 12.353 12.353c0 6.822-5.53 12.353-12.353 12.353h-0zM22.838 18.977c-0.371-0.186-2.197-1.083-2.537-1.208-0.341-0.124-0.589-0.185-0.837 0.187-0.246 0.371-0.958 1.207-1.175 1.455-0.216 0.249-0.434 0.279-0.805 0.094-1.15-0.466-2.138-1.087-2.997-1.852l0.010 0.009c-0.799-0.74-1.484-1.587-2.037-2.521l-0.028-0.052c-0.216-0.371-0.023-0.572 0.162-0.757 0.167-0.166 0.372-0.434 0.557-0.65 0.146-0.179 0.271-0.384 0.366-0.604l0.006-0.017c0.043-0.087 0.068-0.188 0.068-0.296 0-0.131-0.037-0.253-0.101-0.357l0.002 0.003c-0.094-0.186-0.836-2.014-1.145-2.758-0.302-0.724-0.609-0.625-0.836-0.637-0.216-0.010-0.464-0.012-0.712-0.012-0.395 0.010-0.746 0.188-0.988 0.463l-0.001 0.002c-0.802 0.761-1.3 1.834-1.3 3.023 0 0.026 0 0.053 0.001 0.079l-0-0.004c0.131 1.467 0.681 2.784 1.527 3.857l-0.012-0.015c1.604 2.379 3.742 4.282 6.251 5.564l0.094 0.043c0.548 0.248 1.25 0.513 1.968 0.74l0.149 0.041c0.442 0.14 0.951 0.221 1.479 0.221 0.303 0 0.601-0.027 0.889-0.078l-0.031 0.004c1.069-0.223 1.956-0.868 2.497-1.749l0.009-0.017c0.165-0.366 0.261-0.793 0.261-1.242 0-0.185-0.016-0.366-0.047-0.542l0.003 0.019c-0.092-0.155-0.34-0.247-0.712-0.434z"></path>
                        </g>
                    </svg>
                </a>

            </div>

        </div>

    </footer>

    {{-- =====================================================
         MODAL LOGIN
    ====================================================== --}}
    <x-modal name="login" focusable maxWidth="md">

        {{-- Encabezado --}}
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">

            <h5 class="text-lg font-semibold text-gray-900">
                Inicio de sesión requerido
            </h5>

            <button
                type="button"
                @click="$dispatch('close-modal', 'login')"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                aria-label="Cerrar">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    aria-hidden="true">
                    <path
                        d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414z" />
                </svg>
            </button>

        </div>

        <div class="px-6 py-6 text-center">

            <p class="mb-6 text-sm leading-relaxed text-gray-600">
                Para poder
                <strong class="font-semibold text-gray-900">
                    reportar una farmacia como cerrada
                </strong>,
                primero debés iniciar sesión con tu cuenta.
            </p>

            {{-- Botón --}}
            <a
                href="{{ route('login') }}"
                class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white no-underline shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                Ir al inicio de sesión
            </a>

        </div>

    </x-modal>

    {{-- =====================================================
         LEAFLET
    ====================================================== --}}

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @stack('scripts')

    @yield('map_script')

</body>

</html>