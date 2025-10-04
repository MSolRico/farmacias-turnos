<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Farmacias de Turno</title>
    <link rel="icon" href="{{ asset('capsule.svg') }}" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="d-flex flex-column h-100" style="background-color: rgb(242, 245, 248);">
    <main class="flex-shrink-0">
        <nav class="navbar navbar-expand-lg navbar-light bg-white">
            <div class="container">
                <div class="d-flex justify-content-between w-100">
                    <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#66b5f5" class="bi bi-capsule me-2" viewBox="0 0 16 16">
                            <path d="M1.828 8.9 8.9 1.827a4 4 0 1 1 5.657 5.657l-7.07 7.071A4 4 0 1 1 1.827 8.9Zm9.128.771 2.893-2.893a3 3 0 1 0-4.243-4.242L6.713 5.429z" />
                        </svg>
                        <h2 class="d-none d-md-block" style="font-size:30px">Farmacias de Turno</h2>
                    </a>

                    <div class="d-flex align-items-center">
                        
                        <form action="{{ route('buscar') }}" method="GET" class="row g-3 align-items-center mx-3" style="max-width: 500px;">
                            <div class="col-5">
                                <input type="date" name="fecha" class="form-control" style="background-color:rgb(242, 245, 248);" required>
                            </div>
                            <div class="col-5">
                                <select name="ciudad" class="form-select" style="background-color:rgb(242, 245, 248);" required>
                                    <option value="">Elegir ciudad</option>
                                    @foreach($ciudades as $ciudad)
                                    <option value="{{ $ciudad->id_ciudad }}">{{ $ciudad->nombre_ciudad }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2 d-flex align-items-end">
                                <button class="btn d-flex justify-content-center align-items-center" style="background:#66b5f5; height:38px;" type="submit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                                    </svg>
                                </button>
                            </div>
                        </form>

                        <div class="dropdown" >
                            <button class="btn btn-light rounded-circle p-1" type="button" id="authDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background-color:rgb(242, 245, 248)">
                                <i class="bi bi-person fs-4"></i> 
                            </button>
                            
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="authDropdown" >
                                @auth
                                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Perfil</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">Cerrar Sesión</button>
                                        </form>
                                    </li>
                                @else
                                    @if (Route::has('login'))
                                        <li><a class="dropdown-item" href="{{ route('login') }}">Iniciar Sesión</a></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    @if (Route::has('register'))
                                        <li><a class="dropdown-item" href="{{ route('register') }}">Registrarse</a></li>
                                    @endif
                                @endauth
                            </ul>
                        </div>
                    </div>
                    </div>
            </div>
        </nav>
        @yield('content')

        <hr class="my-4">
        <footer class="text-center my-4">
            <div class="container">
                <span class="text-muted">Farmacias de Turno © 2025</span>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
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
</body>

</html>