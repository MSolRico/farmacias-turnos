@extends('layouts.app')

@section('title', 'Farmacias de turno')

@section('content')

<div class="bg-gray-50 text-slate-700 min-h-screen">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 w-full">

        {{-- =========================================================
             ENCABEZADO
        ========================================================= --}}
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

            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">
                Resultado de búsqueda
            </h1>

            <p class="mt-1 text-sm sm:text-base text-slate-500">
                Farmacias de turno en
                <span class="font-medium text-slate-700">
                    {{ $ciudad->nombre_ciudad }}
                </span>
                ·
                {{ \Carbon\Carbon::parse($fecha)->translatedFormat('l d \d\e F \d\e Y') }}
            </p>

        </div>


        {{-- =========================================================
             CONTENIDO PRINCIPAL
        ========================================================= --}}
        <div class="grid grid-cols-12 gap-6">


            {{-- =====================================================
                 LISTADO
            ====================================================== --}}
            <div class="col-span-12 lg:col-span-6">

                <div class="bg-white border border-gray-200 rounded-3xl shadow-sm">

                    {{-- Cabecera --}}
                    <div class="px-5 py-4 border-b border-gray-100">

                        <h2 class="font-bold text-slate-900">
                            Farmacias encontradas
                        </h2>

                        <p class="text-sm text-slate-500 mt-1">

                            @if($farmacias->count() === 1)

                            Se encontró 1 farmacia de turno.

                            @else

                            Se encontraron {{ $farmacias->count() }}
                            farmacias de turno.

                            @endif

                        </p>

                    </div>


                    {{-- Contenido --}}
                    <div class="p-4">

                        @if($farmacias->isEmpty())

                        @include('turnos.components.empty-state')

                        @else

                        <div class="space-y-3">

                            @foreach($farmacias as $index => $farmacia)

                            @include('turnos.components.farmacia-card', [
                            'farmacia' => $farmacia,
                            'index' => $index
                            ])

                            @endforeach

                        </div>

                        @endif

                    </div>

                </div>

            </div>


            {{-- =====================================================
                 MAPA
            ====================================================== --}}
            @include('turnos.components.mapa')

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