@extends('layouts.app')

@section('content')
<div class="container d-flex flex-column h-100">
    <div class="row g-4 d-flex flex-grow-1">
        <div class="col-12 col-lg-5 d-flex flex-column">
            <div class="container p-4 my-4 border bg-white rounded-4 d-flex flex-column flex-grow-1">
                <h2 style="font-size:25px;">Actualmente Abierto</h2>
                <div class="overflow-auto pe-3 flex-grow-1 mt-2">
                    @if($farmacias->isEmpty())
                    <p>No se encontraron farmacias de turno para esta fecha.</p>
                    @else
                    @foreach($farmacias as $farmacia)
                    {{-- Convertir array a objeto --}}
                    <x-farmacia-card :farmacia="(object)$farmacia" :isToday="true" />
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-7 d-flex flex-column">
            <div class="container p-4 my-4 border bg-white rounded-4 flex-grow-1">
                <x-mapa />
            </div>
        </div>
    </div>
</div>
@endsection

@section('map_script')
    var farmacias = @json($farmacias);
    var markers = [];
    farmacias.forEach(function(farmacia) {
        if (farmacia.lat && farmacia.lng) {
            var marker = L.marker([farmacia.lat, farmacia.lng])
                .addTo(map)
                .bindPopup(`
                    <div style="padding: 8px;">
                        <b>${farmacia.nombre}</b><br>
                        ${farmacia.direccion}<br>
                        Teléfono: ${farmacia.telefono}
                        ${farmacia.reportada_cerrada ? '<br><span style="color: #ff6b00; font-weight: bold;">⚠️ Reportada como cerrada</span>' : ''}
                    </div>
                `);
            markers.push([farmacia.lat, farmacia.lng]);
        }
    });

    if (markers.length > 1) {
        map.fitBounds(markers, { padding: [50, 50] });
    } else if (markers.length === 1) {
        map.setView(markers[0], 13);
    }
@endsection