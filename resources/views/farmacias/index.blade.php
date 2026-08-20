@extends('layouts.app')

@section('title', 'Farmacias de Turno')

@section('content')

<div class="max-w-7xl mx-auto px-6 py-6">

    <div class="grid grid-cols-12 gap-6">

        {{-- =====================================================
             COLUMNA IZQUIERDA
        ====================================================== --}}

        <div class="col-span-12 lg:col-span-6 space-y-6">

            {{-- HERO --}}
            <section
                id="buscar"
                class="bg-slate-50 border border-slate-100 rounded-3xl p-6 relative overflow-hidden"
            >

                <div class="max-w-md">

                    <h2 class="text-2xl font-bold text-slate-900 leading-tight">
                        Encontrá la farmacia de turno
                        <span class="text-emerald-600">
                            más cercana
                        </span>
                    </h2>

                    <p class="text-sm text-slate-500 leading-relaxed mt-3">
                        Información actualizada todos los días para que
                        siempre encuentres la farmacia que está de turno.
                    </p>


                    {{-- BUSCADOR --}}
                    <form
                        action="{{ route('buscar') }}"
                        method="GET"
                        class="mt-5 space-y-3"
                    >

                        {{-- Fecha --}}
                        <div class="flex items-center bg-white border border-gray-200 rounded-xl p-1 shadow-sm">

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="17"
                                height="17"
                                fill="currentColor"
                                class="text-gray-400 ml-2 mr-1"
                                viewBox="0 0 16 16"
                            >
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h.5A1.5 1.5 0 0 1 15 2.5v11A1.5 1.5 0 0 1 13.5 15h-11A1.5 1.5 0 0 1 1 13.5v-11A1.5 1.5 0 0 1 2.5 1H3V.5a.5.5 0 0 1 .5-.5"/>
                                <path d="M2 5h12v8.5a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5z"/>
                            </svg>

                            <input
                                type="date"
                                name="fecha"
                                value="{{ request('fecha', now()->toDateString()) }}"
                                class="w-full text-sm text-slate-800 font-medium px-2 py-2 bg-transparent border-0 focus:ring-0 focus:outline-none"
                                required
                            >

                        </div>


                        {{-- Ciudad --}}
                        <select
                            name="ciudad"
                            class="w-full bg-white border border-gray-200 rounded-xl px-3 py-3 text-sm text-slate-700 focus:ring-emerald-500 focus:border-emerald-500"
                            required
                        >

                            <option value="">
                                Elegir ciudad
                            </option>

                            @foreach($ciudades as $ciudad)

                                <option
                                    value="{{ $ciudad->id_ciudad }}"
                                    @selected(request('ciudad') == $ciudad->id_ciudad)
                                >
                                    {{ $ciudad->nombre_ciudad }}
                                </option>

                            @endforeach

                        </select>


                        {{-- Botón --}}
                        <button
                            type="submit"
                            class="w-full px-4 py-3 bg-emerald-800 hover:bg-emerald-900 text-white rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition shadow-sm"
                        >
                            Buscar farmacias

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="15"
                                height="15"
                                fill="currentColor"
                                viewBox="0 0 16 16"
                            >
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                            </svg>
                        </button>

                    </form>


                    {{-- Volver a hoy --}}
                    @if(Route::has('dashboard'))

                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex items-center gap-1 text-xs text-slate-400 hover:text-emerald-600 mt-3 font-medium"
                        >

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="13"
                                height="13"
                                fill="currentColor"
                                viewBox="0 0 16 16"
                            >
                                <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 1 1 .908-.418A6 6 0 1 1 8 2z"/>
                                <path d="M8 4.5a.5.5 0 0 1 .5.5v3.5H12a.5.5 0 0 1 0 1H8a.5.5 0 0 1-.5-.5V5a.5.5 0 0 1 .5-.5"/>
                            </svg>

                            Ver farmacias de hoy

                        </a>

                    @endif

                </div>

            </section>


            {{-- =================================================
                 ENCABEZADO LISTA
            ================================================== --}}

            <div class="flex items-center justify-between">

                <div class="flex items-center gap-2">

                    <h3 class="font-bold text-slate-900 text-sm">
                        Farmacias de turno
                    </h3>

                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full">
                        {{ $farmacias->count() }}
                    </span>

                </div>

            </div>


            {{-- =================================================
                 LISTADO
            ================================================== --}}

            <div class="space-y-3">

                @forelse($farmacias as $index => $farmacia)

                    @include('farmacias.partials.card', [
                        'farmacia' => $farmacia,
                        'isToday' => $isToday ?? false
                    ])

                @empty

                    <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center">

                        <div class="text-slate-300 text-4xl mb-3">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="42"
                                height="42"
                                fill="currentColor"
                                class="mx-auto"
                                viewBox="0 0 16 16"
                            >
                                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16M8 4a.93.93 0 1 1 0 1.86A.93.93 0 0 1 8 4m1 4v4H7V8z"/>
                            </svg>
                        </div>

                        <h3 class="font-semibold text-slate-800">
                            No se encontraron farmacias
                        </h3>

                        <p class="text-sm text-slate-500 mt-1">
                            Probá seleccionando otra fecha o ciudad.
                        </p>

                    </div>

                @endforelse

            </div>

        </div>


        {{-- =====================================================
             COLUMNA DERECHA - MAPA
        ====================================================== --}}

        <div
            id="mapa"
            class="col-span-12 lg:col-span-6"
        >

            <div
                class="bg-slate-200 rounded-3xl overflow-hidden border border-gray-200 relative h-[600px]"
            >

                <div
                    id="map"
                    class="absolute inset-0 z-0"
                ></div>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     INFORMACIÓN
========================================================= --}}

<section
    id="informacion"
    class="border-t border-gray-200 bg-white py-8 mt-4"
>

    <div class="max-w-7xl mx-auto px-6">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="flex gap-3">

                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl h-fit">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        fill="currentColor"
                        viewBox="0 0 16 16"
                    >
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.5-13v5.793l3.354 3.353-.708.708L7.5 9.207V3z"/>
                    </svg>
                </div>

                <div>

                    <h5 class="font-bold text-slate-800">
                        Información actualizada
                    </h5>

                    <p class="text-xs text-gray-400 mt-1">
                        Consultá las farmacias de turno según la fecha seleccionada.
                    </p>

                </div>

            </div>


            <div class="flex gap-3">

                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl h-fit">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        fill="currentColor"
                        viewBox="0 0 16 16"
                    >
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m3.5-9.5a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H8.707l2.147 2.146a.5.5 0 0 1-.708.708L8 9.207l-2.146 2.147a.5.5 0 0 1-.708-.708L7.293 8.5H4.5A.5.5 0 0 1 4 8V7.5a.5.5 0 0 1 .5-.5h3.293L5.646 4.854a.5.5 0 1 1 .708-.708L8 6.293l2.146-2.147a.5.5 0 0 1 .708.708L8.707 7H11.5z"/>
                    </svg>
                </div>

                <div>

                    <h5 class="font-bold text-slate-800">
                        Reportes de usuarios
                    </h5>

                    <p class="text-xs text-gray-400 mt-1">
                        Los usuarios pueden informar si una farmacia se encuentra cerrada.
                    </p>

                </div>

            </div>


            <div class="flex gap-3">

                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl h-fit">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        fill="currentColor"
                        viewBox="0 0 16 16"
                    >
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m2.5-8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                    </svg>
                </div>

                <div>

                    <h5 class="font-bold text-slate-800">
                        Ubicación en el mapa
                    </h5>

                    <p class="text-xs text-gray-400 mt-1">
                        Encontrá las farmacias y consultá su ubicación.
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const farmacias = @json($farmacias);

    if (!document.getElementById('map')) {
        return;
    }

    /*
     * Coordenadas iniciales.
     * Santa Fe capital.
     */
    const map = L.map('map').setView(
        [-31.6333, -60.7000],
        13
    );


    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }
    ).addTo(map);


    const markers = [];


    farmacias.forEach(function (farmacia) {

        /*
         * Adaptar estos nombres a las columnas reales
         * de tu tabla si fueran diferentes.
         */
        const lat = parseFloat(farmacia.latitud);
        const lng = parseFloat(farmacia.longitud);

        if (
            Number.isNaN(lat) ||
            Number.isNaN(lng)
        ) {
            return;
        }


        const marker = L.marker([lat, lng])
            .addTo(map)
            .bindPopup(`
                <strong>${farmacia.nombre ?? ''}</strong>
                <br>
                ${farmacia.direccion ?? ''}
            `);


        markers.push(marker);

    });


    /*
     * Si hay farmacias con coordenadas,
     * ajustar automáticamente el mapa.
     */
    if (markers.length > 0) {

        const group = L.featureGroup(markers);

        map.fitBounds(
            group.getBounds().pad(0.15)
        );

    }

});

</script>

@endpush