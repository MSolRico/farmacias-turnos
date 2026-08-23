<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Farmacias de Turno')</title>

    <link rel="icon" href="{{ asset('capsule.svg') }}" type="image/svg+xml">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Styles -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f9f9fb] text-slate-700 antialiased">

    <main class="min-h-screen flex items-center justify-center px-4 py-10">

        <div class="w-full max-w-md">
            {{-- LOGO --}}
            <div class="flex justify-center mb-6">
                <a href="{{ route('dashboard') }}" class="no-underline">
                    <div class="w-16 h-16">
                        <x-application-logo />
                    </div>
                </a>
            </div>

            {{-- TARJETA --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-8">
                {{ $slot }}
            </div>
        </div>

    </main>

</body>

</html>