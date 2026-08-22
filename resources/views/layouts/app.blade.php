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
    @include('layouts.navbar')


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

    <main class="flex-1" x-data="{}">
        @yield('content')
    </main>


    {{-- =====================================================
         FOOTER
    ====================================================== --}}
    @include('layouts.footer')

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

</body>

</html>