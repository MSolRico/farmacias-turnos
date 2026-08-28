@extends('layouts.app')

@section('content')

<div class="bg-gray-50 text-slate-700">

    {{-- =========================================================
         CONTENIDO PRINCIPAL
         ========================================================= --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 w-full">

        <div class="grid grid-cols-12 gap-6">

            {{-- =================================================
                 HERO
                 ================================================= --}}
            <div class="col-span-12 lg:col-span-6 order-1 lg:col-start-1 lg:row-start-1">

                @include('turnos.components.hero')

            </div>

            {{-- =================================================
                 MAPA
                 ================================================= --}}
            <div class="col-span-12 lg:col-span-6 order-2 lg:col-start-7 lg:row-start-1 lg:row-span-2">

                @include('turnos.components.mapa')

            </div>

            {{-- =================================================
                 CABECERA DE LISTADO
                 ================================================= --}}
            <div class="col-span-12 lg:col-span-6 order-3 lg:col-start-1 lg:row-start-2">

                <div class="space-y-6">

                    {{-- Cabecera --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                        <div class="flex items-center gap-2">

                            <h3 id="titulo-listado" class="font-bold text-slate-900 text-base">
                                Farmacias de turno
                            </h3>

                            <span id="cantidad-farmacias" class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full">
                                {{ $farmacias->count() }}
                            </span>

                        </div>

                        <span id="subtitulo-listado" class="text-xs text-slate-400">
                            Santa Fe - Santo Tomé
                        </span>

                    </div>


                    {{-- =================================================
                     LISTA DE FARMACIAS
                     ================================================= --}}

                    @if($farmacias->count())

                    <div id="lista-farmacias" class="space-y-3">

                        @foreach($farmacias as $index => $farmacia)

                        @include('turnos.components.farmacia-card', [
                        'farmacia' => $farmacia,
                        'index' => $index,
                        'puedeReportar' => true
                        ])

                        @endforeach

                    </div>

                    @else

                    {{-- Sin resultados --}}
                    @include('turnos.components.empty-state')

                    @endif

                </div>

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