@extends('layouts.admin')

@section('title', 'Farmacias')

@section('header')
<h1 class="text-lg font-semibold text-slate-900 dark:text-white">
    Farmacias
</h1>
@endsection

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Encabezado --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Farmacias registradas
        </h2>

        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Administrá la información y las coordenadas de las farmacias.
        </p>
    </div>


    {{-- Mensaje de éxito --}}
    @if(session('success'))

    <div class="mb-5 flex items-center justify-between gap-4
                rounded-xl border border-emerald-200
                bg-emerald-50 px-4 py-3
                text-sm text-emerald-700
                dark:border-emerald-900/50
                dark:bg-emerald-950/30
                dark:text-emerald-400">

        <div class="flex items-center gap-2">

            <span class="font-semibold">
                ✓
            </span>

            <span>
                {{ session('success') }}
            </span>

        </div>

    </div>

    @endif

    {{-- Buscador --}}
    <form
        method="GET"
        action="{{ route('admin.farmacias.index') }}"
        class="mb-5">

        <div class="flex flex-col sm:flex-row gap-3">

            <div class="relative flex-1">

                <input
                    type="search"
                    name="buscar"
                    value="{{ $busqueda }}"
                    placeholder="Buscar por nombre o dirección..."
                    class="w-full rounded-xl border border-slate-300
                       dark:border-slate-700
                       bg-white dark:bg-slate-900
                       text-slate-900 dark:text-white
                       pl-4 pr-4 py-2.5
                       focus:outline-none
                       focus:ring-2 focus:ring-emerald-500">

            </div>

            <select
                name="ciudad"
                class="rounded-xl border border-slate-300
           dark:border-slate-700
           bg-white dark:bg-slate-900
           text-slate-900 dark:text-white
           px-4 py-2.5
           focus:outline-none
           focus:ring-2 focus:ring-emerald-500">

                <option value="">
                    Todas las ciudades
                </option>

                @foreach($ciudades as $ciudad)

                <option
                    value="{{ $ciudad->id_ciudad }}"
                    @selected($ciudadId==$ciudad->id_ciudad)>

                    {{ $ciudad->nombre_ciudad }}

                </option>

                @endforeach

            </select>

            <select
                name="coordenadas"
                class="rounded-xl border border-slate-300
           dark:border-slate-700
           bg-white dark:bg-slate-900
           text-slate-900 dark:text-white
           px-4 py-2.5
           focus:outline-none
           focus:ring-2 focus:ring-emerald-500">

                <option value="">
                    Todas las coordenadas
                </option>

                <option
                    value="completas"
                    @selected($coordenadas==='completas' )>
                    Coordenadas completas
                </option>

                <option
                    value="incompletas"
                    @selected($coordenadas==='incompletas' )>
                    Coordenadas incompletas
                </option>

            </select>

            <button
                type="submit"
                class="inline-flex items-center justify-center
                   px-5 py-2.5 rounded-xl
                   bg-emerald-700 hover:bg-emerald-800
                   text-white text-sm font-semibold
                   transition">

                Buscar

            </button>

            @if($busqueda || $ciudadId || $coordenadas)

            <a
                href="{{ route('admin.farmacias.index') }}"
                class="inline-flex items-center justify-center
                       px-5 py-2.5 rounded-xl
                       border border-slate-300
                       dark:border-slate-700
                       text-slate-700 dark:text-slate-300
                       hover:bg-slate-50 dark:hover:bg-slate-800
                       text-sm font-medium transition">

                Limpiar

            </a>

            @endif

        </div>

    </form>

    {{-- Resumen de resultados --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between
            gap-2 mb-4">

        <p class="text-sm text-slate-500 dark:text-slate-400">

            <span class="font-semibold text-slate-700 dark:text-slate-200">
                {{ $farmacias->count() }}
            </span>

            {{ $farmacias->count() === 1 ? 'farmacia encontrada' : 'farmacias encontradas' }}

        </p>


        @php
        $sinCoordenadas = $farmacias->filter(function ($farmacia) {
        return $farmacia->lat === null || $farmacia->lng === null;
        })->count();
        @endphp

        @if($sinCoordenadas > 0)

        <span class="text-sm text-amber-600 dark:text-amber-400">
            {{ $sinCoordenadas }}
            {{ $sinCoordenadas === 1 ? 'sin coordenadas' : 'sin coordenadas' }}
        </span>

        @endif

    </div>

    {{-- Tabla --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200
                dark:border-slate-800 rounded-2xl overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-slate-50 dark:bg-slate-800/50
                              border-b border-slate-200 dark:border-slate-800">

                    <tr>

                        <th class="px-5 py-4 text-left font-semibold text-slate-600 dark:text-slate-300">
                            Farmacia
                        </th>

                        <th class="px-5 py-4 text-left font-semibold text-slate-600 dark:text-slate-300">
                            Ciudad
                        </th>

                        <th class="px-5 py-4 text-left font-semibold text-slate-600 dark:text-slate-300">
                            Dirección
                        </th>

                        <th class="px-5 py-4 text-left font-semibold text-slate-600 dark:text-slate-300">
                            Teléfono
                        </th>

                        <th class="px-5 py-4 text-left font-semibold text-slate-600 dark:text-slate-300">
                            Coordenadas
                        </th>

                        <th class="px-5 py-4 text-right font-semibold text-slate-600 dark:text-slate-300">
                            Acción
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">

                    @forelse($farmacias as $farmacia)

                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">

                        {{-- Farmacia --}}
                        <td class="px-5 py-4">

                            <p class="font-semibold text-slate-900 dark:text-white">
                                {{ $farmacia->nombre }}
                            </p>

                        </td>


                        {{-- Ciudad --}}
                        <td class="px-5 py-4 text-slate-600 dark:text-slate-300">

                            {{ $farmacia->ciudad->nombre_ciudad ?? '—' }}

                        </td>


                        {{-- Dirección --}}
                        <td class="px-5 py-4 text-slate-600 dark:text-slate-300">

                            {{ $farmacia->direccion ?: '—' }}

                        </td>


                        {{-- Teléfono --}}
                        <td class="px-5 py-4 text-slate-600 dark:text-slate-300">

                            {{ $farmacia->telefono ?: '—' }}

                        </td>


                        {{-- Coordenadas --}}
                        <td class="px-5 py-4">

                            @if($farmacia->lat !== null && $farmacia->lng !== null)

                            <span class="inline-flex items-center gap-1.5
                                                 px-2.5 py-1 rounded-full
                                                 text-xs font-medium
                                                 bg-emerald-100 text-emerald-700
                                                 dark:bg-emerald-950
                                                 dark:text-emerald-400">

                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>

                                Completas

                            </span>

                            @else

                            <span class="inline-flex items-center gap-1.5
                                                 px-2.5 py-1 rounded-full
                                                 text-xs font-medium
                                                 bg-amber-100 text-amber-700
                                                 dark:bg-amber-950
                                                 dark:text-amber-400">

                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>

                                Sin coordenadas

                            </span>

                            @endif

                        </td>


                        {{-- Acción --}}
                        <td class="px-5 py-4 text-right">


                            <a
                                href="{{ route('admin.farmacias.edit', $farmacia) }}"
                                class="inline-flex items-center gap-2
           px-3 py-2 rounded-lg
           text-sm font-medium
           text-emerald-700
           hover:bg-emerald-50
           dark:text-emerald-400
           dark:hover:bg-emerald-950/50
           transition">

                                Editar

                            </a>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="6"
                            class="px-5 py-12 text-center
                                       text-slate-500 dark:text-slate-400">

                            No hay farmacias registradas.

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection