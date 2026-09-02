@extends('layouts.admin')

@section('title', 'Importaciones')

@section('header')
<h1 class="text-lg font-semibold text-slate-900 dark:text-white">
    Importaciones
</h1>
@endsection

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Encabezado --}}
    <div class="mb-6">

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Importaciones de turnos
        </h2>

        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Consultá el estado y resultado de cada importación mensual.
        </p>

    </div>


    {{-- Tabla --}}
    <div class="bg-white dark:bg-slate-900
                border border-slate-200 dark:border-slate-800
                rounded-2xl overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-slate-50 dark:bg-slate-800/50
                              border-b border-slate-200 dark:border-slate-800">

                    <tr>

                        <th class="px-5 py-4 text-left font-semibold
                                   text-slate-600 dark:text-slate-300">
                            Período
                        </th>

                        <th class="px-5 py-4 text-left font-semibold
                                   text-slate-600 dark:text-slate-300">
                            Estado
                        </th>

                        <th class="px-5 py-4 text-left font-semibold
                                   text-slate-600 dark:text-slate-300">
                            Último intento
                        </th>

                        <th class="px-5 py-4 text-center font-semibold
                                   text-slate-600 dark:text-slate-300">
                            Nuevas
                        </th>

                        <th class="px-5 py-4 text-center font-semibold
                                   text-slate-600 dark:text-slate-300">
                            Actualizadas
                        </th>

                        <th class="px-5 py-4 text-center font-semibold
                                   text-slate-600 dark:text-slate-300">
                            Rechazadas
                        </th>

                        <th class="px-5 py-4 text-right font-semibold
           text-slate-600 dark:text-slate-300">
                            Acción
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">

                    @forelse($importaciones as $importacion)

                    @php
                    $nombreMes = \Carbon\Carbon::create(
                    $importacion->anio,
                    $importacion->mes,
                    1
                    )->translatedFormat('F Y');
                    @endphp

                    <tr class="hover:bg-slate-50
                                   dark:hover:bg-slate-800/40
                                   transition">

                        {{-- Período --}}
                        <td class="px-5 py-4">

                            <p class="font-semibold text-slate-900
                                          dark:text-white capitalize">
                                {{ $nombreMes }}
                            </p>

                        </td>


                        {{-- Estado --}}
                        <td class="px-5 py-4">

                            @if($importacion->estado === 'completada')

                            <span class="inline-flex items-center gap-1.5
                                                 px-2.5 py-1 rounded-full
                                                 bg-emerald-50 text-emerald-700
                                                 dark:bg-emerald-950/40
                                                 dark:text-emerald-400
                                                 text-xs font-semibold">

                                <span class="w-1.5 h-1.5 rounded-full
                                                     bg-emerald-500"></span>

                                Completada

                            </span>

                            @elseif($importacion->estado === 'completada_con_advertencias')

                            <span class="inline-flex items-center gap-1.5
                                                 px-2.5 py-1 rounded-full
                                                 bg-amber-50 text-amber-700
                                                 dark:bg-amber-950/40
                                                 dark:text-amber-400
                                                 text-xs font-semibold">

                                ⚠️ Con advertencias

                            </span>

                            @elseif($importacion->estado === 'procesando')

                            <span class="inline-flex items-center gap-1.5
                                                 px-2.5 py-1 rounded-full
                                                 bg-blue-50 text-blue-700
                                                 dark:bg-blue-950/40
                                                 dark:text-blue-400
                                                 text-xs font-semibold">

                                Procesando

                            </span>

                            @elseif($importacion->estado === 'pendiente')

                            <span class="inline-flex items-center gap-1.5
                                                 px-2.5 py-1 rounded-full
                                                 bg-slate-100 text-slate-600
                                                 dark:bg-slate-800
                                                 dark:text-slate-300
                                                 text-xs font-semibold">

                                Pendiente

                            </span>

                            @else

                            <span class="inline-flex items-center gap-1.5
                                                 px-2.5 py-1 rounded-full
                                                 bg-red-50 text-red-700
                                                 dark:bg-red-950/40
                                                 dark:text-red-400
                                                 text-xs font-semibold">

                                Error

                            </span>

                            @endif

                        </td>


                        {{-- Último intento --}}
                        <td class="px-5 py-4 text-slate-600
                                       dark:text-slate-300">

                            @if($importacion->ultimo_intento)

                            {{ $importacion->ultimo_intento->format('d/m/Y H:i') }}

                            @else

                            —

                            @endif

                        </td>


                        {{-- Nuevas --}}
                        <td class="px-5 py-4 text-center
                                       font-medium text-slate-700
                                       dark:text-slate-300">

                            {{ $importacion->farmacias_nuevas }}

                        </td>


                        {{-- Actualizadas --}}
                        <td class="px-5 py-4 text-center
                                       font-medium text-slate-700
                                       dark:text-slate-300">

                            {{ $importacion->farmacias_actualizadas }}

                        </td>


                        {{-- Rechazadas --}}
                        <td class="px-5 py-4 text-center">

                            @if($importacion->farmacias_rechazadas > 0)

                            <span class="font-semibold text-red-600
                                                 dark:text-red-400">
                                {{ $importacion->farmacias_rechazadas }}
                            </span>

                            @else

                            <span class="text-slate-500
                                                 dark:text-slate-400">
                                0
                            </span>

                            @endif

                        </td>

                        <td class="px-5 py-4 text-right">

                            <a
                                href="{{ route('admin.importaciones.show', $importacion) }}"
                                class="inline-flex items-center gap-1.5
               px-3 py-1.5 rounded-lg
               text-xs font-semibold
               text-emerald-700 bg-emerald-50
               hover:bg-emerald-100
               dark:text-emerald-400
               dark:bg-emerald-950/40
               dark:hover:bg-emerald-950/70
               transition">
                                Ver
                                <span aria-hidden="true">→</span>
                            </a>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="7"
                            class="px-5 py-12 text-center
                                       text-slate-500 dark:text-slate-400">

                            No hay importaciones registradas.

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection