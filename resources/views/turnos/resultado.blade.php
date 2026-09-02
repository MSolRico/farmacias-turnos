@extends('layouts.app')

@section('title', 'Farmacias de turno')

@section('content')

<div class="bg-gray-50 dark:bg-slate-950 text-slate-700 dark:text-slate-200 min-h-screen">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 w-full">

        {{-- =========================================================
             CONTENIDO PRINCIPAL
        ========================================================= --}}
        <div class="grid grid-cols-12 gap-6">

            {{-- =====================================================
             COLUMNA IZQUIERDA
        ====================================================== --}}
            <div class="col-span-12 lg:col-span-6 order-2 lg:order-1">

                {{-- ENCABEZADO --}}
                <div class="mb-6">

                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex items-center gap-2 text-sm font-medium text-emerald-700 hover:text-emerald-900 transition mb-4">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="17"
                            height="17"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2">

                            <path d="m15 18-6-6 6-6" />

                        </svg>

                        Volver al inicio

                    </a>

                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">
                        Resultado de búsqueda
                    </h1>

                    <p class="mt-1 text-sm sm:text-base text-slate-500 dark:text-slate-400">
                        Farmacias de turno en
                        <span class="font-medium text-slate-700 dark:text-slate-300">
                            {{ $ciudad->nombre_ciudad }}
                        </span>
                        ·
                        {{ \Carbon\Carbon::parse($fecha)->translatedFormat('l d \d\e F \d\e Y') }}
                    </p>

                </div>


                {{-- =====================================================
                     LISTADO
                ====================================================== --}}
                <div class="space-y-6">

                    {{-- Cabecera --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                        <div class="flex items-center gap-2">

                            <h3 id="titulo-listado" class="font-bold text-slate-900 dark:text-slate-100 text-base">
                                Farmacias de turno
                            </h3>

                            <span id="cantidad-farmacias" class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 text-xs font-bold rounded-full">
                                {{ $farmacias->count() }}
                            </span>

                        </div>

                        <span id="subtitulo-listado" class="text-xs text-slate-400">
                            {{ $ciudad->nombre_ciudad }}
                        </span>

                    </div>

                    {{-- Lista de farmacias --}}
                    @if($farmacias->count())

                    <div id="lista-farmacias" class="space-y-3">

                        @foreach($farmacias as $index => $farmacia)

                        @include('turnos.components.farmacia-card', [
                        'farmacia' => $farmacia,
                        'index' => $index,
                        'puedeReportar' => false
                        ])

                        @endforeach

                    </div>

                    @else

                    {{-- Sin resultados --}}
                    @include('turnos.components.empty-state')

                    @endif

                </div>

            </div>


            {{-- =====================================================
                 MAPA
            ====================================================== --}}
            <div class="col-span-12 lg:col-span-6 order-1 lg:order-2">

                @include('turnos.components.mapa', ['mostrarCercanas' => false])

            </div>

        </div>

    </div>

    {{-- =========================================================
         CARACTERÍSTICAS
         ========================================================= --}}
    @include('turnos.components.caracteristicas')

</div>

@endsection

{{-- =============================================================
     LEAFLET
     ============================================================= --}}
@push('scripts')
@vite('resources/js/turnos/mapa.js')
@endpush