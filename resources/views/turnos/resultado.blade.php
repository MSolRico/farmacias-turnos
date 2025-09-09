@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h1>Farmacias de turno en {{ $ciudad->nombre }} para {{ $fecha }}</h1>

        @if($farmacias->isEmpty())
            <p>No se encontraron farmacias de turno para esta fecha.</p>
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
                    @foreach($farmacias as $farmacia)
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
        
        <p class="mt-4"><a href="{{ route('home') }}">Volver</a></p>
    </div>
@endsection

@section('scripts')
<script>
    var farmacias = @json($farmacias);

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