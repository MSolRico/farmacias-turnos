@extends('layouts.admin')

@section('title', 'Detalle del reporte')

@section('content')

<div class="space-y-6">

    {{-- Encabezado --}}
    <div>
        <a href="{{ route('admin.reportes.index') }}"
           class="text-sm text-slate-500 hover:text-emerald-600 transition">
            ← Volver a reportes
        </a>

        <div class="mt-4">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                Detalle del reporte
            </h1>

            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Revisá la información del reporte y verificá la situación informada.
            </p>
        </div>
    </div>

    {{-- Mensaje --}}
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Información del reporte --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm">

            <div class="p-6 border-b border-slate-200 dark:border-slate-800">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                    Situación reportada
                </h2>
            </div>

            <div class="p-6 space-y-6">

                {{-- Farmacia --}}
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">
                        Farmacia
                    </p>

                    <p class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">
                        {{ $reporte->farmacia->nombre ?? '—' }}
                    </p>

                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ $reporte->farmacia->direccion ?? '—' }}
                    </p>
                </div>

                {{-- Turno --}}
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">
                        Turno reportado
                    </p>

                    @if($reporte->turno)

                        <div class="mt-2 rounded-xl bg-slate-50 dark:bg-slate-800 p-4">
                            <p class="font-medium text-slate-900 dark:text-white">
                                {{ $reporte->turno->nombre_turno ?? 'Turno' }}
                            </p>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ $reporte->turno->fecha_hora_inicio?->format('d/m/Y H:i') }}
                                —
                                {{ $reporte->turno->fecha_hora_fin?->format('d/m/Y H:i') }}
                            </p>
                        </div>

                    @else

                        <p class="mt-2 text-sm text-slate-500">
                            No se especificó un turno.
                        </p>

                    @endif
                </div>

                {{-- Comentario --}}
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">
                        Información aportada
                    </p>

                    <div class="mt-2 rounded-xl bg-slate-50 dark:bg-slate-800 p-4 text-sm text-slate-700 dark:text-slate-300">
                        {{ $reporte->comentario ?: 'El usuario no agregó ningún comentario.' }}
                    </div>
                </div>

                {{-- Fecha y usuario --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">
                            Fecha del reporte
                        </p>

                        <p class="mt-1 text-sm text-slate-900 dark:text-white">
                            {{ $reporte->fecha_reporte?->format('d/m/Y') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">
                            Reportado por
                        </p>

                        <p class="mt-1 text-sm text-slate-900 dark:text-white">
                            {{ $reporte->usuario->name ?? '—' }}
                        </p>
                    </div>

                </div>

            </div>
        </div>

        {{-- Estado y acciones --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm">

            <div class="p-6 border-b border-slate-200 dark:border-slate-800">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                    Estado
                </h2>
            </div>

            <div class="p-6">

                @if($reporte->estado === 'pendiente')

                    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-700">
                        Pendiente de revisión
                    </span>

                @elseif($reporte->estado === 'verificado')

                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-700">
                        Reporte verificado
                    </span>

                @elseif($reporte->estado === 'rechazado')

                    <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-700">
                        Reporte rechazado
                    </span>

                @endif

                @if($reporte->estado === 'pendiente')

                    <div class="mt-6 space-y-3">

                        <form method="POST"
                              action="{{ route('admin.reportes.update', $reporte) }}">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="estado" value="verificado">

                            <button type="submit"
                                    class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700 transition">
                                Confirmar reporte
                            </button>
                        </form>

                        <form method="POST"
                              action="{{ route('admin.reportes.update', $reporte) }}">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="estado" value="rechazado">

                            <button type="submit"
                                    class="w-full rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 hover:bg-red-100 transition">
                                Rechazar reporte
                            </button>
                        </form>

                    </div>

                @else

                    <p class="mt-5 text-sm text-slate-500 dark:text-slate-400">
                        Este reporte ya fue revisado.
                    </p>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection