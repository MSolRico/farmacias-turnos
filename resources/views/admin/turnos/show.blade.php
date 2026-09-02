@extends('layouts.admin')

@section('title', 'Detalle del turno')

@section('header')
    <h1 class="text-lg font-semibold text-slate-900 dark:text-white">
        Detalle del turno
    </h1>
@endsection

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Volver --}}
    <div class="mb-6">
        <a
            href="{{ route('admin.turnos.index') }}"
            class="inline-flex items-center gap-2 text-sm font-medium
                   text-slate-500 hover:text-emerald-600
                   dark:text-slate-400 dark:hover:text-emerald-400
                   transition"
        >
            ← Volver a turnos
        </a>
    </div>


    {{-- Información del turno --}}
    <div class="bg-white dark:bg-slate-900
                border border-slate-200 dark:border-slate-800
                rounded-2xl p-6 mb-6">

        <div class="flex flex-col sm:flex-row
                    sm:items-start sm:justify-between gap-4">

            <div>

                <p class="text-xs font-semibold uppercase tracking-wide
                          text-emerald-600 dark:text-emerald-400">
                    Turno
                </p>

                <h2 class="mt-1 text-2xl font-bold
                           text-slate-900 dark:text-white">
                    {{ $turno->nombre_turno }}
                </h2>

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {{ $turno->ciudad->nombre_ciudad ?? 'Sin ciudad' }}
                </p>

            </div>

            @php
                $inicio = \Carbon\Carbon::parse($turno->fecha_hora_inicio);
                $fin = \Carbon\Carbon::parse($turno->fecha_hora_fin);
                $activo = $inicio <= now() && $fin > now();
            @endphp

            @if($activo)

                <span class="inline-flex items-center gap-2
                             px-3 py-1.5 rounded-full
                             bg-emerald-50 text-emerald-700
                             dark:bg-emerald-950/40
                             dark:text-emerald-400
                             text-xs font-semibold">

                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>

                    Activo

                </span>

            @endif

        </div>


        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">

            <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-4">

                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                    Inicio
                </p>

                <p class="mt-1 font-semibold text-slate-900 dark:text-white">
                    {{ $inicio->format('d/m/Y H:i') }}
                </p>

            </div>

            <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-4">

                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                    Fin
                </p>

                <p class="mt-1 font-semibold text-slate-900 dark:text-white">
                    {{ $fin->format('d/m/Y H:i') }}
                </p>

            </div>

        </div>

    </div>


    {{-- Farmacias asignadas --}}
    <div class="bg-white dark:bg-slate-900
                border border-slate-200 dark:border-slate-800
                rounded-2xl overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-200
                    dark:border-slate-800">

            <h3 class="font-semibold text-slate-900 dark:text-white">
                Farmacias asignadas
            </h3>

            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ $turno->farmacias->count() }}
                {{ $turno->farmacias->count() === 1 ? 'farmacia asignada' : 'farmacias asignadas' }}
            </p>

        </div>


        @if($turno->farmacias->isNotEmpty())

            <div class="divide-y divide-slate-100 dark:divide-slate-800">

                @foreach($turno->farmacias as $farmacia)

                    <div class="px-6 py-4 flex flex-col sm:flex-row
                                sm:items-center sm:justify-between gap-3">

                        <div>

                            <p class="font-semibold text-slate-900 dark:text-white">
                                {{ $farmacia->nombre }}
                            </p>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ $farmacia->direccion }}
                            </p>

                        </div>

                        <div class="text-sm text-slate-500 dark:text-slate-400">

                            @if($farmacia->lat !== null && $farmacia->lng !== null)

                                Coordenadas completas

                            @else

                                <span class="text-amber-600 dark:text-amber-400">
                                    Sin coordenadas
                                </span>

                            @endif

                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <div class="px-6 py-12 text-center
                        text-sm text-slate-500 dark:text-slate-400">

                No hay farmacias asignadas a este turno.

            </div>

        @endif

    </div>

</div>

@endsection