@extends('layouts.app')

@section('content')
<div class="container mt-4 d-flex flex-column h-100">
    <div class="row g-4 d-flex flex-grow-1">
        <div class="col-12 col-lg-5 d-flex flex-column">
            <div class="container p-4 my-4 border bg-white rounded-4 d-flex flex-column flex-grow-1">
                <h2 style="font-size:25px;">Actualmente Abierto</h2>
                <div class="overflow-auto pe-3 flex-grow-1">
                    @if($farmacias_turno_hoy->isEmpty())
                    <p>No se encontraron farmacias de turno para esta fecha.</p>
                    @else
                    @foreach($farmacias_turno_hoy as $farmacia)
                    @include('componentes.farmacia-card', ['farmacia' => $farmacia])
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-7 d-flex flex-column">
            <div class="container p-4 my-4 border bg-white rounded-4 flex-grow-1">
                @include('componentes.mapa')
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection

@section('map_script')
var farmacias = @json($farmacias_turno_hoy);
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
} else if (markers.length === 1) {
map.setView(markers[0], 13);
}
@endsection