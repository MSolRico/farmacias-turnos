@extends('layouts.app')

@section('content')

<div class="bg-gray-50 text-slate-700">

    {{-- =========================================================
         CONTENIDO PRINCIPAL
         ========================================================= --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 w-full">

        <div class="grid grid-cols-12 gap-6">

            {{-- =================================================
                 COLUMNA IZQUIERDA
                 ================================================= --}}
            <div class="col-span-12 lg:col-span-6 space-y-6">

                {{-- HERO --}}
                @include('turnos.components.hero')


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

                    @include('turnos.components.farmacia-card', [
                    'farmacia' => $farmacia,
                    'index' => $index
                    ])

                    @endforeach

                </div>

                @else

                {{-- Sin resultados --}}
                @include('turnos.components.empty-state')

                @endif

            </div>


            {{-- =================================================
                 COLUMNA DERECHA - MAPA
                 ================================================= --}}
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