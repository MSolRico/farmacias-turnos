@extends('layouts.app')

@section('content')

<div class="bg-gray-50 text-slate-700">

    {{-- =========================================================
         CONTENIDO PRINCIPAL
         ========================================================= --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 w-full">

        <div class="grid grid-cols-12 gap-6">

            {{-- =================================================
                 COLUMNA IZQUIERDA
                 ================================================= --}}
            <div class="col-span-12 lg:col-span-6 space-y-6">

                {{-- HERO --}}
                <section class="bg-[#f1f6f5] border border-slate-100 rounded-3xl p-6 relative overflow-hidden">

                    <div class="relative z-10 max-w-xl">

                        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 leading-tight">
                            Encontrá la
                            <span class="block text-emerald-800">farmacia de turno</span>
                            <span class="block">más cercana</span>
                        </h2>

                        <p class="text-xs text-slate-500 leading-relaxed mt-3 max-w-2xs">
                            Información actualizada todos los días para que siempre encuentres la farmacia que está de turno.
                        </p>

                        {{-- BUSCADOR --}}
                        <form
                            action="{{ route('buscar') }}"
                            method="GET"
                            class="mt-5">
                            <div class="flex flex-col sm:flex-row gap-2">

                                {{-- Fecha --}}
                                <div class="flex items-center bg-white border border-gray-200 rounded-xl px-3 shadow-sm flex-1">

                                    {{-- SVG calendario --}}
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="text-gray-400 flex-shrink-0">
                                        <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>

                                    <input
                                        type="date"
                                        name="fecha"
                                        value="{{ now()->format('Y-m-d') }}"
                                        required
                                        class="w-full border-0 bg-transparent text-sm text-slate-700 px-2 py-3 focus:ring-0 focus:outline-none">
                                </div>

                                {{-- Ciudad --}}
                                <select
                                    name="ciudad"
                                    required
                                    class="bg-white border border-gray-200 rounded-xl px-3 py-3 text-sm text-slate-700 shadow-sm focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 outline-none">
                                    <option value="">Elegir ciudad</option>

                                    @foreach($ciudades as $ciudad)
                                    <option value="{{ $ciudad->id_ciudad }}">
                                        {{ $ciudad->nombre_ciudad }}
                                    </option>
                                    @endforeach
                                </select>

                                {{-- Botón --}}
                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl px-4 py-3 text-sm font-semibold transition shadow-sm">
                                    Buscar

                                    {{-- SVG lupa --}}
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                </button>

                            </div>
                        </form>

                        {{-- Volver a hoy --}}
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-emerald-600 mt-3 font-medium transition">
                            {{-- SVG actualizar --}}
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="13"
                                height="13"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2">
                                <path d="M3 12a9 9 0 0 1 15.6-6"></path>
                                <polyline points="18 3 18 7 14 7"></polyline>
                                <path d="M21 12a9 9 0 0 1-15.6 6"></path>
                                <polyline points="6 21 6 17 10 17"></polyline>
                            </svg>

                            Ver farmacias de hoy
                        </a>

                    </div>

                    {{-- ILUSTRACIÓN --}}
                    <div class="hidden sm:flex absolute right-6 bottom-21 w-58 h-58 rounded-3xl items-center justify-center text-emerald-600">

                        <img src="{{ asset('images/farmacia.png') }}" alt="ilustracion farmacia">

                    </div>

                </section>


                {{-- =================================================
                     CABECERA DE LISTADO
                     ================================================= --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                    <div class="flex items-center gap-2">

                        <h3 class="font-bold text-slate-900 text-base">
                            Farmacias de turno
                        </h3>

                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full">
                            {{ $farmacias->count() }}
                        </span>

                    </div>

                    <span class="text-xs text-slate-400">
                        {{ $ciudad_santa_fe->nombre_ciudad ?? 'Santa Fe' }}
                    </span>

                </div>


                {{-- =================================================
                     LISTA DE FARMACIAS
                     ================================================= --}}
                @if($farmacias->count())

                <div class="space-y-3">

                    @foreach($farmacias as $index => $farmacia)

                    <div class="bg-white border border-gray-200 rounded-2xl p-4 hover:border-emerald-300 transition shadow-sm">

                        <div class="flex items-center justify-between gap-4">

                            {{-- Información --}}
                            <div class="flex items-start gap-3 min-w-0">

                                {{-- Número --}}
                                <span class="w-7 h-7 rounded-full bg-emerald-800 text-white flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    {{ $index + 1 }}
                                </span>

                                <div class="min-w-0">

                                    <h4 class="font-bold text-slate-900 text-sm truncate">
                                        {{ $farmacia->nombre }}
                                    </h4>

                                    {{-- Dirección --}}
                                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="13"
                                            height="13"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="flex-shrink-0">
                                            <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>

                                        <span>{{ $farmacia->direccion }}</span>

                                    </p>

                                    {{-- Teléfono --}}
                                    @if($farmacia->telefono)

                                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="13"
                                            height="13"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                        </svg>

                                        <span>{{ $farmacia->telefono }}</span>

                                    </p>

                                    @endif

                                </div>

                            </div>


                            {{-- Acciones --}}
                            <div class="flex items-center gap-2 flex-shrink-0">

                                <span class="hidden sm:inline-flex px-2 py-1 bg-emerald-100 text-emerald-700 font-semibold text-[10px] rounded-md">
                                    De turno
                                </span>

                                {{-- Centrar en mapa --}}
                                @if($farmacia->lat && $farmacia->lng)

                                <button
                                    type="button"
                                    onclick="centrarMapa({{ $farmacia->lat }}, {{ $farmacia->lng }})"
                                    class="p-2 text-slate-400 hover:text-emerald-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition"
                                    title="Ver en el mapa">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>

                                </button>

                                @endif

                            </div>

                        </div>

                        {{-- Nota --}}
                        @if($farmacia->notas)

                        <div class="mt-3 bg-blue-50 border border-blue-100 rounded-xl px-3 py-2">

                            <p class="text-xs text-blue-700">
                                <strong>Nota:</strong>
                                {{ $farmacia->notas }}
                            </p>

                        </div>

                        @endif

                        {{-- Reporte --}}
                        <div class="mt-3">

                            @auth

                            <button
                                type="button"
                                @click="$dispatch('open-modal', 'confirm-report-{{ $farmacia->id_farmacia }}')"
                                class="w-full inline-flex items-center justify-center gap-2 bg-slate-50 hover:bg-red-50 border border-gray-200 hover:border-red-200 text-slate-600 hover:text-red-600 rounded-xl py-2 text-xs font-semibold transition">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="15"
                                    height="15"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2">

                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" y1="8" x2="12" y2="12" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" />

                                </svg>

                                Reportar como cerrada

                            </button>

                            @else

                            <button
                                type="button"
                                @click="$dispatch('open-modal', 'login')"
                                class="w-full inline-flex items-center justify-center gap-2 bg-slate-50 hover:bg-emerald-50 border border-gray-200 hover:border-emerald-200 text-slate-600 hover:text-emerald-700 rounded-xl py-2 text-xs font-semibold transition">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="15"
                                    height="15"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2">

                                    <rect x="3" y="11" width="18" height="11" rx="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />

                                </svg>

                                Iniciar sesión para reportar

                            </button>

                            @endauth

                        </div>

                    </div>

                    {{-- ============================================
                        MODAL DE CONFIRMACIÓN
                     ============================================ --}}
                    @auth
                    <x-modal
                        name="confirm-report-{{ $farmacia->id_farmacia }}"
                        maxWidth="md"
                        focusable>

                        {{-- Cabecera --}}
                        <div class="border-b border-gray-100 px-6 py-5 text-center">

                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="26"
                                    height="26"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    aria-hidden="true">

                                    <path d="M10.3 2.86 1.82 17a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 2.86a2 2 0 0 0-3.4 0Z" />
                                    <line x1="12" y1="9" x2="12" y2="13" />
                                    <line x1="12" y1="17" x2="12.01" y2="17" />

                                </svg>

                            </div>

                            <h5 class="text-lg font-semibold text-gray-900">
                                Confirmar reporte
                            </h5>

                        </div>

                        {{-- Contenido --}}
                        <div class="px-6 py-5 text-center">

                            <p class="text-sm leading-relaxed text-gray-700">
                                ¿Confirmás que
                                <strong>{{ $farmacia->nombre }}</strong>
                                se encuentra
                                <strong class="text-red-600">cerrada</strong>?
                            </p>

                            <div class="mt-4 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3">

                                <p class="text-xs leading-relaxed text-amber-800">
                                    Reportá la farmacia solamente si comprobaste que
                                    <strong>no se encuentra prestando servicio durante su turno</strong>.
                                    Tu reporte ayudará a otros usuarios a evitar un viaje innecesario.
                                </p>

                            </div>

                        </div>

                        {{-- Acciones --}}
                        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 px-6 py-4 sm:flex-row sm:justify-end">

                            <button
                                type="button"
                                @click="$dispatch('close-modal', 'confirm-report-{{ $farmacia->id_farmacia }}')"
                                class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                                Cancelar

                            </button>

                            <form
                                action="{{ route('reportes.store') }}"
                                method="POST">

                                @csrf

                                <input
                                    type="hidden"
                                    name="id_farmacia"
                                    value="{{ $farmacia->id_farmacia }}">

                                <button
                                    type="submit"
                                    class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 sm:w-auto">

                                    Sí, reportar cierre

                                </button>

                            </form>

                        </div>

                    </x-modal>
                    @endauth

                    @endforeach

                </div>

                @else

                {{-- Sin resultados --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center">

                    <div class="w-12 h-12 mx-auto bg-gray-100 rounded-full flex items-center justify-center text-gray-400">

                        <svg
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9"></circle>
                            <line x1="8" y1="8" x2="16" y2="16"></line>
                            <line x1="16" y1="8" x2="8" y2="16"></line>
                        </svg>

                    </div>

                    <h3 class="mt-3 font-semibold text-slate-800">
                        No hay farmacias de turno
                    </h3>

                    <p class="text-xs text-slate-400 mt-1">
                        No encontramos farmacias de turno para la fecha seleccionada.
                    </p>

                </div>

                @endif

            </div>


            {{-- =================================================
                 COLUMNA DERECHA - MAPA
                 ================================================= --}}
            <div class="col-span-12 lg:col-span-6">

                <div
                    class="bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-sm sticky top-24"
                    style="height: 650px;">

                    <div
                        id="map"
                        class="w-full h-full"></div>

                </div>
            </div>
        </div>

    </main>


    {{-- =========================================================
         CARACTERÍSTICAS
         ========================================================= --}}
    <section class="border-t border-gray-200 bg-white py-8 mt-4">

        <div class="max-w-7xl mx-auto px-4 sm:px-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">

                {{-- Actualización --}}
                <div class="flex items-start gap-3">

                    <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl flex-shrink-0">

                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>

                    </div>

                    <div>
                        <h5 class="font-bold text-slate-800">
                            Información actualizada
                        </h5>

                        <p class="text-gray-400 text-xs mt-1">
                            Consultá las farmacias de turno disponibles para cada día.
                        </p>
                    </div>

                </div>


                {{-- Reportes --}}
                <div class="flex items-start gap-3">

                    <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl flex-shrink-0">

                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            <polyline points="9 12 11 14 15 10"></polyline>
                        </svg>

                    </div>

                    <div>
                        <h5 class="font-bold text-slate-800">
                            Reportes de usuarios
                        </h5>

                        <p class="text-gray-400 text-xs mt-1">
                            Los usuarios pueden informar si una farmacia está cerrada.
                        </p>
                    </div>

                </div>


                {{-- Mapa --}}
                <div class="flex items-start gap-3">

                    <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl flex-shrink-0">

                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21 3 6"></polygon>
                            <line x1="9" y1="3" x2="9" y2="18"></line>
                            <line x1="15" y1="6" x2="15" y2="21"></line>
                        </svg>

                    </div>

                    <div>
                        <h5 class="font-bold text-slate-800">
                            Ubicación en el mapa
                        </h5>

                        <p class="text-gray-400 text-xs mt-1">
                            Encontrá rápidamente las farmacias disponibles en tu zona.
                        </p>
                    </div>

                </div>

            </div>
        </div>

    </section>

</div>
@endsection


{{-- =============================================================
     LEAFLET
     ============================================================= --}}
@section('map_script')

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Coordenadas de Santa Fe como centro inicial
        const centroSantaFe = [-31.6333, -60.7000];

        const map = L.map('map').setView(centroSantaFe, 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const farmacias = @json($farmacias);

        const markers = [];

        farmacias.forEach(function(farmacia, index) {

            if (!farmacia.lat || !farmacia.lng) {
                return;
            }

            const lat = parseFloat(farmacia.lat);
            const lng = parseFloat(farmacia.lng);

            if (isNaN(lat) || isNaN(lng)) {
                return;
            }

            const marker = L.marker([lat, lng])
                .addTo(map)
                .bindPopup(`
                    <div style="min-width: 190px;">
                        <strong>${escapeHtml(farmacia.nombre)}</strong>
                        <br>
                        <span>${escapeHtml(farmacia.direccion ?? '')}</span>
                        <br>
                        <span>${escapeHtml(farmacia.telefono ?? '')}</span>
                    </div>
                `);

            markers.push(marker);
        });


        // Ajustar el mapa para mostrar todas las farmacias
        if (markers.length > 0) {

            const group = L.featureGroup(markers);

            map.fitBounds(group.getBounds(), {
                padding: [30, 30]
            });

        }


        // Función global para los botones "Ver en el mapa"
        window.centrarMapa = function(lat, lng) {

            lat = parseFloat(lat);
            lng = parseFloat(lng);

            if (isNaN(lat) || isNaN(lng)) {
                return;
            }

            map.setView([lat, lng], 17);

        };


        // Evita insertar HTML proveniente de la base de datos
        function escapeHtml(value) {

            if (value === null || value === undefined) {
                return '';
            }

            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

        }

    });
</script>

@endsection