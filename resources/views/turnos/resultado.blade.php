@extends('layouts.app')

@section('content')
<div class="container d-flex flex-column h-100">
    <div class="row g-4 d-flex flex-grow-1">
        <div class="col-12 col-lg-5 d-flex flex-column">
            <div class="container p-4 my-4 border bg-white rounded-4 d-flex flex-column flex-grow-1">
                <h2 style="font-size:25px;">En {{ $ciudad->nombre_ciudad }} para {{ \Carbon\Carbon::parse($fecha)->translatedFormat('d F') }}</h2>
                <div class="overflow-auto pe-3 flex-grow-1 mt-2">
                    @php
                    $isToday = (\Carbon\Carbon::parse($fecha)->isToday());
                    @endphp
                    @if($farmacias->isEmpty())
                    <p>No se encontraron farmacias de turno para esta fecha.</p>
                    @else
                    @foreach($farmacias as $farmacia)
                    @include('components.farmacia-card', ['farmacia' => $farmacia, 'isToday' => $isToday])
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-7 d-flex flex-column">
            <div class="container p-4 my-4 border bg-white rounded-4 flex-grow-1">
                @include('components.mapa')
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection

@section('map_script')
var farmacias = @json($farmacias);
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