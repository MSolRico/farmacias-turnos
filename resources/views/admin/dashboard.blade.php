@extends('layouts.admin')

@section('title', 'Panel de administración')

@section('content')

<div class="min-h-screen bg-gray-50 dark:bg-slate-950">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

        {{-- Encabezado --}}
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">
                Panel de administración
            </h1>

            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Estado general del sistema
            </p>
        </div>


        {{-- Indicadores --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

            {{-- Farmacias --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Farmacias
                </p>

                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                    {{ $totalFarmacias }}
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    registradas
                </p>
            </div>


            {{-- Sin coordenadas --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Sin coordenadas
                </p>

                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                    {{ $farmaciasSinCoordenadas }}
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    requieren revisión
                </p>
            </div>


            {{-- Reportes --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Reportes pendientes
                </p>

                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                    {{ $reportesPendientes }}
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    de usuarios
                </p>
            </div>


            {{-- Última importación --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">

                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Última importación
                </p>

                @if($ultimaImportacion)

                    <p class="mt-2 text-lg font-bold text-slate-900 dark:text-white">
                        {{ ucfirst(\Carbon\Carbon::create(
                            $ultimaImportacion->anio,
                            $ultimaImportacion->mes,
                            1
                        )->translatedFormat('F Y')) }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Estado:
                        {{ ucfirst($ultimaImportacion->estado) }}
                    </p>

                @else

                    <p class="mt-2 text-lg font-bold text-slate-900 dark:text-white">
                        Sin datos
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        todavía no hay importaciones
                    </p>

                @endif

            </div>

        </div>


        {{-- Última importación --}}
        @if($ultimaImportacion)

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">

                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                            Última importación
                        </h2>

                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ ucfirst(\Carbon\Carbon::create(
                                $ultimaImportacion->anio,
                                $ultimaImportacion->mes,
                                1
                            )->translatedFormat('F Y')) }}
                        </p>
                    </div>

                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">
                        {{ ucfirst($ultimaImportacion->estado) }}
                    </span>

                </div>


                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">

                    <div>
                        <p class="text-xs text-slate-400">
                            Nuevas
                        </p>

                        <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">
                            {{ $ultimaImportacion->farmacias_nuevas }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            Actualizadas
                        </p>

                        <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">
                            {{ $ultimaImportacion->farmacias_actualizadas }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            Rechazadas
                        </p>

                        <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">
                            {{ $ultimaImportacion->farmacias_rechazadas }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            Turnos nuevos
                        </p>

                        <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">
                            {{ $ultimaImportacion->turnos_nuevos }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            Asignaciones
                        </p>

                        <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">
                            {{ $ultimaImportacion->asignaciones_creadas }}
                        </p>
                    </div>

                </div>

            </div>

        @endif

    </div>

</div>

@endsection