@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1>Farmacias de turno</h1>
    <h2>Santa Fe y Santo Tomé - {{ $mes }} {{ $anio }}</h2>
    <hr>
    
    <div class="my-4">
        <h2>Buscar farmacias de turno</h2>
        <form action="{{ route('buscar') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label>Fecha:</label>
                <input type="date" name="fecha" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Ciudad:</label>
                <select name="ciudad" class="form-control" required>
                    <option value="">-- Elegir ciudad --</option>
                    @foreach($ciudades as $ciudad)
                        <option value="{{ $ciudad->id_ciudad }}">{{ $ciudad->nombre_ciudad }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100">Buscar</button>
            </div>
        </form>
    </div>

    @if($farmacias_turno_hoy->isEmpty())
        <p>No se encontraron farmacias de turno para hoy.</p>
    @else
        <div id="mapa" style="height: 500px; width: 100%;" class="my-4"></div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Notas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($farmacias_turno_hoy as $farmacia)
                    <tr>
                        <td>{{ $farmacia->nombre }}</td>
                        <td>{{ $farmacia->direccion }}</td>
                        <td>{{ $farmacia->telefono }}</td>
                        <td>{{ $farmacia->notas ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection

@section('scripts')
<script>
    var farmacias = @json($farmacias_turno_hoy);

    if (farmacias.length > 0) {
        var map = L.map('mapa');
        var primeraFarmacia = farmacias[0];
        map.setView([primeraFarmacia.lat, primeraFarmacia.lng], 13); 

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var markers = [];

        farmacias.forEach(function(farmacia) {
            if (farmacia.lat && farmacia.lng) {
                var marker = L.marker([farmacia.lat, farmacia.lng])
                    .addTo(map)
                    .bindPopup(`<b>${farmacia.nombre}</b><br>${farmacia.direccion}<br>Teléfono: ${farmacia.telefono}`);
                markers.push([farmacia.lat, farmacia.lng]);
            }
        });

        if (markers.length > 1) {
            map.fitBounds(markers);
        }
    }
</script>
@endsection