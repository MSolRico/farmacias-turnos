@extends('layouts.admin')

@section('title', 'Detalle de importación')

@section('header')
    <h1 class="text-lg font-semibold text-slate-900 dark:text-white">
        Detalle de importación
    </h1>
@endsection

@section('content')

@php
    $nombreMes = \Carbon\Carbon::create(
        $importacion->anio,
        $importacion->mes,
        1
    )->translatedFormat('F Y');
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Volver --}}
    <div class="mb-6">

        <a
            href="{{ route('admin.importaciones.index') }}"
            class="inline-flex items-center gap-2
                   text-sm font-medium
                   text-slate-500 hover:text-emerald-600
                   dark:text-slate-400
                   dark:hover:text-emerald-400
                   transition"
        >
            ← Volver a importaciones
        </a>

    </div>


    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row
                sm:items-start sm:justify-between
                gap-4 mb-6">

        <div>

            <p class="text-xs font-semibold uppercase
                      tracking-wide text-emerald-600
                      dark:text-emerald-400">
                Importación mensual
            </p>

            <h2 class="mt-1 text-2xl font-bold
                       text-slate-900 dark:text-white capitalize">
                {{ $nombreMes }}
            </h2>

        </div>


        {{-- Estado --}}
        @if($importacion->estado === 'completada')

            <span class="inline-flex items-center gap-2
                         px-3 py-1.5 rounded-full
                         bg-emerald-50 text-emerald-700
                         dark:bg-emerald-950/40
                         dark:text-emerald-400
                         text-xs font-semibold">

                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>

                Completada

            </span>

        @elseif($importacion->estado === 'completada_con_advertencias')

            <span class="inline-flex items-center gap-2
                         px-3 py-1.5 rounded-full
                         bg-amber-50 text-amber-700
                         dark:bg-amber-950/40
                         dark:text-amber-400
                         text-xs font-semibold">

                ⚠️ Completada con advertencias

            </span>

        @elseif($importacion->estado === 'procesando')

            <span class="inline-flex items-center gap-2
                         px-3 py-1.5 rounded-full
                         bg-blue-50 text-blue-700
                         dark:bg-blue-950/40
                         dark:text-blue-400
                         text-xs font-semibold">

                Procesando

            </span>

        @elseif($importacion->estado === 'pendiente')

            <span class="inline-flex items-center gap-2
                         px-3 py-1.5 rounded-full
                         bg-slate-100 text-slate-600
                         dark:bg-slate-800
                         dark:text-slate-300
                         text-xs font-semibold">

                Pendiente

            </span>

        @else

            <span class="inline-flex items-center gap-2
                         px-3 py-1.5 rounded-full
                         bg-red-50 text-red-700
                         dark:bg-red-950/40
                         dark:text-red-400
                         text-xs font-semibold">

                Error

            </span>

        @endif

    </div>


    {{-- Información general --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

        {{-- Último intento --}}
        <div class="bg-white dark:bg-slate-900
                    border border-slate-200 dark:border-slate-800
                    rounded-2xl p-6">

            <p class="text-xs font-semibold uppercase tracking-wide
                      text-slate-500 dark:text-slate-400">
                Último intento
            </p>

            <p class="mt-2 text-lg font-semibold
                      text-slate-900 dark:text-white">

                @if($importacion->ultimo_intento)

                    {{ $importacion->ultimo_intento->format('d/m/Y H:i') }}

                @else

                    —

                @endif

            </p>

        </div>


        {{-- PDF --}}
        <div class="bg-white dark:bg-slate-900
                    border border-slate-200 dark:border-slate-800
                    rounded-2xl p-6">

            <p class="text-xs font-semibold uppercase tracking-wide
                      text-slate-500 dark:text-slate-400">
                PDF utilizado
            </p>

            @if($importacion->pdf_url)

                <a
                    href="{{ $importacion->pdf_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2
                           mt-2 text-sm font-semibold
                           text-emerald-600
                           hover:text-emerald-700
                           dark:text-emerald-400"
                >
                    Abrir PDF →
                </a>

            @else

                <p class="mt-2 text-sm text-slate-500
                          dark:text-slate-400">
                    No disponible
                </p>

            @endif

        </div>

    </div>


    {{-- Estadísticas --}}
    <div class="bg-white dark:bg-slate-900
                border border-slate-200 dark:border-slate-800
                rounded-2xl overflow-hidden mb-6">

        <div class="px-6 py-5 border-b
                    border-slate-200 dark:border-slate-800">

            <h3 class="font-semibold text-slate-900 dark:text-white">
                Resultado de la importación
            </h3>

        </div>


        <div class="grid grid-cols-2 md:grid-cols-3
                    lg:grid-cols-6">

            <div class="p-5 border-b md:border-r
                        border-slate-200 dark:border-slate-800">

                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Farmacias nuevas
                </p>

                <p class="mt-2 text-2xl font-bold
                          text-slate-900 dark:text-white">
                    {{ $importacion->farmacias_nuevas }}
                </p>

            </div>


            <div class="p-5 border-b lg:border-r
                        border-slate-200 dark:border-slate-800">

                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Actualizadas
                </p>

                <p class="mt-2 text-2xl font-bold
                          text-slate-900 dark:text-white">
                    {{ $importacion->farmacias_actualizadas }}
                </p>

            </div>


            <div class="p-5 border-b md:border-r
                        border-slate-200 dark:border-slate-800">

                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Rechazadas
                </p>

                <p class="mt-2 text-2xl font-bold
                          {{ $importacion->farmacias_rechazadas > 0
                              ? 'text-red-600 dark:text-red-400'
                              : 'text-slate-900 dark:text-white' }}">
                    {{ $importacion->farmacias_rechazadas }}
                </p>

            </div>


            <div class="p-5 border-b lg:border-r
                        border-slate-200 dark:border-slate-800">

                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Turnos nuevos
                </p>

                <p class="mt-2 text-2xl font-bold
                          text-slate-900 dark:text-white">
                    {{ $importacion->turnos_nuevos }}
                </p>

            </div>


            <div class="p-5 border-b md:border-r
                        border-slate-200 dark:border-slate-800">

                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Asignaciones
                </p>

                <p class="mt-2 text-2xl font-bold
                          text-slate-900 dark:text-white">
                    {{ $importacion->asignaciones_creadas }}
                </p>

            </div>


            <div class="p-5 border-b
                        border-slate-200 dark:border-slate-800">

                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Columnas con error
                </p>

                <p class="mt-2 text-2xl font-bold
                          {{ $importacion->columnas_con_error > 0
                              ? 'text-amber-600 dark:text-amber-400'
                              : 'text-slate-900 dark:text-white' }}">
                    {{ $importacion->columnas_con_error }}
                </p>

            </div>

        </div>

    </div>


    {{-- Mensaje --}}
    @if($importacion->mensaje)

        <div class="bg-amber-50 dark:bg-amber-950/20
                    border border-amber-200 dark:border-amber-900/50
                    rounded-2xl p-5">

            <p class="text-sm font-semibold
                      text-amber-800 dark:text-amber-300">
                Información de la importación
            </p>

            <p class="mt-1 text-sm
                      text-amber-700 dark:text-amber-400">
                {{ $importacion->mensaje }}
            </p>

        </div>

    @endif

</div>

@endsection