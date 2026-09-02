@extends('layouts.admin')

@section('title', 'Reportes')

@section('content')

<div class="space-y-6 py-8">

    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
            Reportes
        </h1>

        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            {{ $reportes->count() }}
            {{ $reportes->count() === 1 ? 'reporte encontrado' : 'reportes encontrados' }}
        </p>

        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Revisá los reportes realizados por los usuarios.
        </p>
    </div>
    {{-- Filtros --}}
    <form method="GET"
        action="{{ route('admin.reportes.index') }}"
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Buscar farmacia --}}
            <div class="md:col-span-2">
                <label for="buscar"
                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                    Buscar farmacia
                </label>

                <input
                    type="text"
                    id="buscar"
                    name="buscar"
                    value="{{ $busqueda }}"
                    placeholder="Nombre o dirección..."
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-700
                       bg-white dark:bg-slate-800
                       px-4 py-2.5 text-sm
                       text-slate-900 dark:text-white
                       focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            {{-- Estado --}}
            <div>
                <label for="estado"
                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                    Estado
                </label>

                <select
                    id="estado"
                    name="estado"
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-700
                       bg-white dark:bg-slate-800
                       px-4 py-2.5 text-sm
                       text-slate-900 dark:text-white
                       focus:border-emerald-500 focus:ring-emerald-500">

                    <option value="">Todos</option>

                    <option value="pendiente" @selected($estado==='pendiente' )>
                        Pendientes
                    </option>

                    <option value="verificado" @selected($estado==='verificado' )>
                        Verificados
                    </option>

                    <option value="rechazado" @selected($estado==='rechazado' )>
                        Rechazados
                    </option>

                </select>
            </div>

        </div>

        <div class="mt-4 flex items-center gap-3">

            <button
                type="submit"
                class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white
                   hover:bg-emerald-700 transition">
                Filtrar
            </button>

            <a
                href="{{ route('admin.reportes.index') }}"
                class="rounded-xl border border-slate-300 dark:border-slate-700
                   px-4 py-2.5 text-sm font-medium
                   text-slate-600 dark:text-slate-300
                   hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                Limpiar
            </a>

        </div>

    </form>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">

        @if($reportes->isEmpty())

        <div class="px-6 py-12 text-center">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                No hay reportes registrados.
            </p>
        </div>

        @else

        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">
                            Fecha
                        </th>

                        <th class="px-6 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">
                            Farmacia
                        </th>

                        <th class="px-6 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">
                            Usuario
                        </th>

                        <th class="px-6 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">
                            Estado
                        </th>

                        <th class="px-6 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">
                            Acción
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">

                    @foreach($reportes as $reporte)

                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">

                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                            {{ $reporte->fecha_reporte?->format('d/m/Y') }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-900 dark:text-white">
                                {{ $reporte->farmacia->nombre ?? '—' }}
                            </div>

                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $reporte->farmacia->direccion ?? '' }}
                            </div>
                        </td>

                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                            {{ $reporte->usuario->name ?? '—' }}
                        </td>

                        <td class="px-6 py-4">

                            @if($reporte->estado === 'pendiente')

                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">
                                Pendiente
                            </span>

                            @elseif($reporte->estado === 'verificado')

                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                Verificado
                            </span>

                            @else

                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                {{ ucfirst($reporte->estado) }}
                            </span>

                            @endif

                        </td>

                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.reportes.show', $reporte) }}"
                                class="text-sm font-medium text-emerald-600 hover:text-emerald-700">
                                Ver →
                            </a>
                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>
        </div>

        @endif

    </div>

</div>

@endsection