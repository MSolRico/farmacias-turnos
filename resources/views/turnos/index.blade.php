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

                    <x-modal name="confirm-report-{{ $farmacia->id_farmacia }}" maxWidth="md" focusable>
                        <div class="modal-header">
                            <h5 class="modal-title text-danger">
                                {{-- Ícono de Advertencia Rojo --}}
                                <svg width="40px" height="40px" viewBox="0 0 512 512" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#dc3545">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <title>warning-filled</title>
                                        <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <g id="add" fill="#dc3545" transform="translate(32.000000, 42.666667)">
                                                <path d="M246.312928,5.62892705 C252.927596,9.40873724 258.409564,14.8907053 262.189374,21.5053731 L444.667042,340.84129 C456.358134,361.300701 449.250007,387.363834 428.790595,399.054926 C422.34376,402.738832 415.04715,404.676552 407.622001,404.676552 L42.6666667,404.676552 C19.1025173,404.676552 7.10542736e-15,385.574034 7.10542736e-15,362.009885 C7.10542736e-15,354.584736 1.93772021,347.288125 5.62162594,340.84129 L188.099293,21.5053731 C199.790385,1.04596203 225.853517,-6.06216498 246.312928,5.62892705 Z M224,272 C208.761905,272 197.333333,283.264 197.333333,298.282667 C197.333333,313.984 208.415584,325.248 224,325.248 C239.238095,325.248 250.666667,313.984 250.666667,298.624 C250.666667,283.264 239.238095,272 224,272 Z M245.333333,106.666667 L202.666667,106.666667 L202.666667,234.666667 L245.333333,234.666667 L245.333333,106.666667 Z" id="Combined-Shape">
                                                </path>
                                            </g>
                                        </g>
                                    </g>
                                </svg>
                                Confirmar Reporte de Cierre
                                {{-- Ícono de Advertencia Rojo --}}
                                <svg width="40px" height="40px" viewBox="0 0 512 512" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#dc3545">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <title>warning-filled</title>
                                        <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <g id="add" fill="#dc3545" transform="translate(32.000000, 42.666667)">
                                                <path d="M246.312928,5.62892705 C252.927596,9.40873724 258.409564,14.8907053 262.189374,21.5053731 L444.667042,340.84129 C456.358134,361.300701 449.250007,387.363834 428.790595,399.054926 C422.34376,402.738832 415.04715,404.676552 407.622001,404.676552 L42.6666667,404.676552 C19.1025173,404.676552 7.10542736e-15,385.574034 7.10542736e-15,362.009885 C7.10542736e-15,354.584736 1.93772021,347.288125 5.62162594,340.84129 L188.099293,21.5053731 C199.790385,1.04596203 225.853517,-6.06216498 246.312928,5.62892705 Z M224,272 C208.761905,272 197.333333,283.264 197.333333,298.282667 C197.333333,313.984 208.415584,325.248 224,325.248 C239.238095,325.248 250.666667,313.984 250.666667,298.624 C250.666667,283.264 239.238095,272 224,272 Z M245.333333,106.666667 L202.666667,106.666667 L202.666667,234.666667 L245.333333,234.666667 L245.333333,106.666667 Z" id="Combined-Shape">
                                                </path>
                                            </g>
                                        </g>
                                    </g>
                                </svg>
                            </h5>
                        </div>

                        <div class="modal-body text-center">
                            <p class="mb-3">
                                ¿Está seguro que desea reportar <strong>{{ $farmacia->nombre }}</strong> como <strong>CERRADA</strong>?
                            </p>
                            <p class="text-sm text-muted">
                                Solo reporte si la farmacia <strong>no está en servicio</strong> en su horario.
                            </p>
                        </div>

                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-secondary" @click="$dispatch('close-modal', 'confirm-report-{{ $farmacia->id_farmacia }}')">
                                Cancelar
                            </button>

                            <form action="{{ route('reportar.cerrada') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="id_farmacia" value="{{ $farmacia->id_farmacia }}">

                                <button type="submit" class="btn btn-danger">
                                    Sí, Reportar Cierre
                                </button>
                            </form>
                        </div>
                    </x-modal>
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