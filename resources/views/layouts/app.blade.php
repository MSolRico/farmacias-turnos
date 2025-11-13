<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Farmacias de Turno</title>
    <link rel="icon" href="{{ asset('capsule.svg') }}" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="d-flex flex-column h-100" style="background-color: rgb(242, 245, 248);">
    <main class="flex-shrink-0">
        <nav class="tw-nav">
            <div class="tw-nav-inner tw-whitespace-nowrap">
                <a href="{{ route('dashboard') }}" class="tw-brand">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#66b5f5" class="bi bi-capsule me-2" viewBox="0 0 16 16">
                        <path d="M1.828 8.9 8.9 1.827a4 4 0 1 1 5.657 5.657l-7.07 7.071A4 4 0 1 1 1.827 8.9Zm9.128.771 2.893-2.893a3 3 0 1 0-4.243-4.242L6.713 5.429z" />
                    </svg>
                    <h1 class="tw-brand-title">Farmacias de Turno</h1>
                </a>

                <div class="d-flex align-items-center tw-nav-actions tw-flex-nowrap">

                    <form action="{{ route('buscar') }}" method="GET" class="tw-flex tw-items-center tw-space-x-2 tw-flex-nowrap">
                        <input type="date" name="fecha" class="tw-h-10 tw-w-5 tw-px-2 tw-py-2 tw-bg-[#f2f5f8] tw-border-gray-300 tw-rounded-lg" style="padding-left: 6px; padding-right: 6px;" required>
                        <select name="ciudad" class="form-select tw-inline-block tw-w-auto tw-bg-[#f2f5f8] tw-border-gray-300" required>
                            <option value="">Elegir ciudad</option>
                            @foreach($ciudades as $ciudad)
                            <option value="{{ $ciudad->id_ciudad }}">{{ $ciudad->nombre_ciudad }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="tw-nav-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                            </svg>
                        </button>
                    </form>

                    <div x-data="{ open: false }" class="tw-relative">
                        <button @click="open = !open" class="tw-avatar-btn">
                            <svg class="tw-w-5 tw-h-5" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#7c7f83">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <title>profile_round [#1342]</title>
                                    <desc>Created with Sketch.</desc>
                                    <defs> </defs>
                                    <g id="Page-1" stroke="none" stroke-width="1" fill="#7c7f83" fill-rule="evenodd">
                                        <g id="Dribbble-Light-Preview" transform="translate(-140.000000, -2159.000000)" fill="#7c7f83">
                                            <g id="icons" transform="translate(56.000000, 160.000000)">
                                                <path d="M100.562548,2016.99998 L87.4381713,2016.99998 C86.7317804,2016.99998 86.2101535,2016.30298 86.4765813,2015.66198 C87.7127655,2012.69798 90.6169306,2010.99998 93.9998492,2010.99998 C97.3837885,2010.99998 100.287954,2012.69798 101.524138,2015.66198 C101.790566,2016.30298 101.268939,2016.99998 100.562548,2016.99998 M89.9166645,2004.99998 C89.9166645,2002.79398 91.7489936,2000.99998 93.9998492,2000.99998 C96.2517256,2000.99998 98.0830339,2002.79398 98.0830339,2004.99998 C98.0830339,2007.20598 96.2517256,2008.99998 93.9998492,2008.99998 C91.7489936,2008.99998 89.9166645,2007.20598 89.9166645,2004.99998 M103.955674,2016.63598 C103.213556,2013.27698 100.892265,2010.79798 97.837022,2009.67298 C99.4560048,2008.39598 100.400241,2006.33098 100.053171,2004.06998 C99.6509769,2001.44698 97.4235996,1999.34798 94.7348224,1999.04198 C91.0232075,1998.61898 87.8750721,2001.44898 87.8750721,2004.99998 C87.8750721,2006.88998 88.7692896,2008.57398 90.1636971,2009.67298 C87.1074334,2010.79798 84.7871636,2013.27698 84.044024,2016.63598 C83.7745338,2017.85698 84.7789973,2018.99998 86.0539717,2018.99998 L101.945727,2018.99998 C103.221722,2018.99998 104.226185,2017.85698 103.955674,2016.63598" id="profile_round-[#1342]"> </path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </button>
                        <ul x-show="open"
                            x-cloak
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="tw-dropdown">
                            @auth
                            <li><a href="{{ route('profile.edit') }}" class="tw-dropdown-link">Perfil</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="tw-dropdown-link">Cerrar Sesión</button>
                                </form>
                            </li>
                            @else
                            @if (Route::has('login'))
                            <li><a href="{{ route('login') }}" class="tw-dropdown-link">Iniciar Sesión</a></li>
                            @endif
                            @if (Route::has('register'))
                            <li><a href="{{ route('register') }}" class="tw-dropdown-link">Registrarse</a></li>
                            @endif
                            @endauth
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        @endif

        @if(session('warning'))
        <div class="container mt-3">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        @endif

        @yield('content')

        <hr class="my-4">
        <footer class="text-center my-4">
            <div class="container">
                <span class="text-muted">Farmacias de Turno © 2025</span>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>

        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            var map = L.map('mapa');

            // Centrar el mapa en Santa Fe por defecto
            var lat_sf = -31.649;
            var lng_sf = -60.700;
            map.setView([lat_sf, lng_sf], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            @yield('map_script')
        </script>
    </main>

    <x-modal name="login" focusable maxWidth="md">
        <div class="modal-header">
            <h5 class="modal-title">Iniciar sesión requerida</h5>
            <button
                type="button"
                class="btn-close"
                @click="$dispatch('close-modal', 'login')"
                aria-label="Close">
            </button>
        </div>
        <div class="modal-body text-center">
            <p class="mb-4">
                Para poder <strong>reportar una farmacia como cerrada</strong>, primero debés iniciar sesión con tu cuenta.
            </p>

            <a href="{{ route('login') }}"
                class="btn btn-primary px-4">
                Ir al inicio de sesión
            </a>
        </div>
    </x-modal>

    <x-modal name="confirmReport" focusable maxWidth="md">
        <div
            x-data="{ 
            farmaciaId: null, 
            farmaciaNombre: 'Farmacia',
            init() {
                this.$watch('$store.selectedFarmacia', (value) => {
                    if (value) {
                        this.farmaciaId = value.id;
                        this.farmaciaNombre = value.nombre;
                    }
                });
                // Escuchar evento personalizado
                window.addEventListener('farmacia-selected', (e) => {
                    this.farmaciaId = e.detail.id;
                    this.farmaciaNombre = e.detail.nombre;
                });
            }
        }">

            <div class="modal-header">
                <h5 class="modal-title">
                    Confirmar Reporte de Cierre
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    @click="$dispatch('close-modal', 'confirmReport')"
                    aria-label="Close">
                </button>
            </div>

            <form method="POST" action="{{ route('reportes.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <p class="mb-3">
                            ¿Está seguro que desea reportar la siguiente farmacia como <strong>CERRADA</strong>?
                        </p>

                        <div class="alert alert-light">
                            <h6 class="mb-0" x-text="farmaciaNombre"></h6>
                        </div>
                    </div>

                    {{-- Input oculto con ID de farmacia --}}
                    <input type="hidden" name="farmacia_id" x-bind:value="farmaciaId">

                    {{-- Campo opcional de comentario --}}
                    <div class="mb-3">
                        <label for="comentario" class="form-label">Comentario (opcional)</label>
                        <textarea
                            class="form-control"
                            id="comentario"
                            name="comentario"
                            rows="3"
                            placeholder="Ej: Estoy en el lugar y está cerrada, no hay personal..."></textarea>
                        <small class="text-muted">Este comentario ayudará a otros usuarios</small>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <small>
                            <strong>Nota:</strong> Los reportes falsos pueden resultar en la suspensión de tu cuenta.
                        </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-circle me-1" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                            <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" />
                        </svg>
                        Sí, Reportar Cierre
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

</body>

</html>