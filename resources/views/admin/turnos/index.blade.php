@extends('layouts.admin')

@section('title', 'Turnos')

@section('header')
<h1 class="text-lg font-semibold text-slate-900 dark:text-white">
    Turnos
</h1>
@endsection

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Encabezado --}}
    <div class="mb-6">

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Turnos registrados
        </h2>

        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Consultá los turnos y las farmacias asignadas.
        </p>

    </div>


    {{-- Filtro por fecha --}}
    <form
        method="GET"
        action="{{ route('admin.turnos.index') }}"
        class="mb-6 flex flex-col sm:flex-row sm:items-end gap-3">

        <div class="w-full sm:w-auto">

            <label
                for="fecha"
                class="block text-xs font-medium text-slate-600
                       dark:text-slate-300 mb-1.5">
                Buscar por fecha
            </label>

            <input
                type="date"
                id="fecha"
                name="fecha"
                value="{{ $fecha }}"
                class="w-full sm:w-52 rounded-xl
                       border border-slate-200 dark:border-slate-700
                       bg-white dark:bg-slate-900
                       text-slate-700 dark:text-slate-200
                       px-3 py-2.5 text-sm
                       focus:border-emerald-500 focus:ring-emerald-500">

        </div>

        <div class="flex items-center gap-2">

            <button
                type="submit"
                class="inline-flex items-center justify-center
                       px-4 py-2.5 rounded-xl
                       bg-emerald-600 hover:bg-emerald-700
                       text-white text-sm font-semibold
                       transition">
                Buscar
            </button>

            @if($fecha)

            <a
                href="{{ route('admin.turnos.index') }}"
                class="inline-flex items-center justify-center
                           px-4 py-2.5 rounded-xl
                           bg-slate-100 hover:bg-slate-200
                           text-slate-600
                           dark:bg-slate-800
                           dark:hover:bg-slate-700
                           dark:text-slate-300
                           text-sm font-semibold
                           no-underline transition">
                Limpiar
            </a>

            @endif

        </div>

    </form>


    {{-- Tabla --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200
                dark:border-slate-800 rounded-2xl overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-slate-50 dark:bg-slate-800/50
                              border-b border-slate-200 dark:border-slate-800">

                    <tr>

                        <th class="px-5 py-4 text-left font-semibold
                                   text-slate-600 dark:text-slate-300">
                            Turno
                        </th>

                        <th class="px-5 py-4 text-left font-semibold
                                   text-slate-600 dark:text-slate-300">
                            Ciudad
                        </th>

                        <th class="px-5 py-4 text-left font-semibold
                                   text-slate-600 dark:text-slate-300">
                            Inicio
                        </th>

                        <th class="px-5 py-4 text-left font-semibold
                                   text-slate-600 dark:text-slate-300">
                            Fin
                        </th>

                        <th class="px-5 py-4 text-center font-semibold
                                   text-slate-600 dark:text-slate-300">
                            Farmacias
                        </th>

                        <th class="px-5 py-4 text-right font-semibold
           text-slate-600 dark:text-slate-300">
                            Acción
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">

                    @forelse($turnos as $turno)

                    @php
                    $inicio = \Carbon\Carbon::parse($turno->fecha_hora_inicio);
                    $fin = \Carbon\Carbon::parse($turno->fecha_hora_fin);
                    $ahora = now();

                    $activo = $inicio <= $ahora && $fin> $ahora;
                        @endphp

                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40
                                   transition">

                            {{-- Turno --}}
                            <td class="px-5 py-4">

                                <p class="font-semibold text-slate-900
                                          dark:text-white">
                                    {{ $turno->nombre_turno }}
                                </p>

                                @if($activo)

                                <span class="inline-flex items-center gap-1.5
                                                 mt-1 text-xs font-medium
                                                 text-emerald-600
                                                 dark:text-emerald-400">

                                    <span class="w-1.5 h-1.5 rounded-full
                                                     bg-emerald-500"></span>

                                    Activo

                                </span>

                                @endif

                            </td>


                            {{-- Ciudad --}}
                            <td class="px-5 py-4 text-slate-600
                                       dark:text-slate-300">

                                {{ $turno->ciudad->nombre_ciudad ?? '—' }}

                            </td>


                            {{-- Inicio --}}
                            <td class="px-5 py-4 text-slate-600
                                       dark:text-slate-300">

                                {{ $inicio->format('d/m/Y H:i') }}

                            </td>


                            {{-- Fin --}}
                            <td class="px-5 py-4 text-slate-600
                                       dark:text-slate-300">

                                {{ $fin->format('d/m/Y H:i') }}

                            </td>


                            {{-- Farmacias --}}
                            <td class="px-5 py-4 text-center">

                                <span class="inline-flex items-center justify-center
                                             min-w-8 px-2.5 py-1 rounded-full
                                             bg-slate-100 text-slate-700
                                             dark:bg-slate-800
                                             dark:text-slate-300
                                             text-xs font-semibold">

                                    {{ $turno->farmacias->count() }}

                                </span>

                            </td>

                            <td class="px-5 py-4 text-right">

                                <a
                                    href="{{ route('admin.turnos.show', $turno) }}"
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

                            <td colspan="6"
                                class="px-5 py-12 text-center
                                       text-slate-500 dark:text-slate-400">

                                No hay turnos registrados.

                            </td>

                        </tr>

                        @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection