<!DOCTYPE html>
<html lang="es" x-data="tema">

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

    <script>
        if (
            localStorage.getItem('tema') === 'dark' ||
            (
                !localStorage.getItem('tema') &&
                window.matchMedia('(prefers-color-scheme: dark)').matches
            )
        ) {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>

<body class="bg-[#f9f9fb] dark:bg-slate-950 text-slate-700 dark:text-slate-200 antialiased">

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
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-100 dark:border-slate-700 shadow-sm p-8">
                {{ $slot }}
            </div>
        </div>

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

</body>

</html>