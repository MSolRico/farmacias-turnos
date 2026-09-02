@extends('layouts.admin')

@section('title', 'Editar farmacia')

@section('header')
    <h1 class="text-lg font-semibold text-slate-900 dark:text-white">
        Editar farmacia
    </h1>
@endsection

@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">

    {{-- Encabezado --}}
    <div class="mb-6">

        <a href="{{ route('admin.farmacias.index') }}"
           class="inline-flex items-center gap-2 text-sm text-emerald-700
                  dark:text-emerald-400 hover:underline mb-4">

            ← Volver a farmacias

        </a>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Editar farmacia
        </h2>

        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Modificá los datos de la farmacia y sus coordenadas.
        </p>

    </div>


    {{-- Mensajes de validación --}}
    @if($errors->any())

        <div class="mb-6 rounded-xl border border-red-200
                    bg-red-50 dark:border-red-900/50
                    dark:bg-red-950/30 p-4">

            <p class="font-semibold text-red-700 dark:text-red-400 mb-2">
                Revisá los siguientes datos:
            </p>

            <ul class="list-disc list-inside text-sm text-red-600
                       dark:text-red-400 space-y-1">

                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

        </div>

    @endif


    {{-- Formulario --}}
    <form
        method="POST"
        action="{{ route('admin.farmacias.update', $farmacia) }}"
        class="bg-white dark:bg-slate-900 border border-slate-200
               dark:border-slate-800 rounded-2xl p-6 sm:p-8">

        @csrf
        @method('PUT')


        {{-- Datos generales --}}
        <div class="mb-8">

            <h3 class="text-base font-semibold text-slate-900
                       dark:text-white mb-4">
                Datos de la farmacia
            </h3>


            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                {{-- Nombre --}}
                <div class="sm:col-span-2">

                    <label for="nombre"
                           class="block text-sm font-medium text-slate-700
                                  dark:text-slate-300 mb-1.5">

                        Nombre

                    </label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        value="{{ old('nombre', $farmacia->nombre) }}"
                        required
                        class="w-full rounded-xl border border-slate-300
                               dark:border-slate-700
                               bg-white dark:bg-slate-950
                               text-slate-900 dark:text-white
                               px-4 py-2.5 focus:outline-none
                               focus:ring-2 focus:ring-emerald-500">

                </div>


                {{-- Dirección --}}
                <div class="sm:col-span-2">

                    <label for="direccion"
                           class="block text-sm font-medium text-slate-700
                                  dark:text-slate-300 mb-1.5">

                        Dirección

                    </label>

                    <input
                        type="text"
                        id="direccion"
                        name="direccion"
                        value="{{ old('direccion', $farmacia->direccion) }}"
                        required
                        class="w-full rounded-xl border border-slate-300
                               dark:border-slate-700
                               bg-white dark:bg-slate-950
                               text-slate-900 dark:text-white
                               px-4 py-2.5 focus:outline-none
                               focus:ring-2 focus:ring-emerald-500">

                </div>


                {{-- Teléfono --}}
                <div>

                    <label for="telefono"
                           class="block text-sm font-medium text-slate-700
                                  dark:text-slate-300 mb-1.5">

                        Teléfono

                    </label>

                    <input
                        type="text"
                        id="telefono"
                        name="telefono"
                        value="{{ old('telefono', $farmacia->telefono) }}"
                        class="w-full rounded-xl border border-slate-300
                               dark:border-slate-700
                               bg-white dark:bg-slate-950
                               text-slate-900 dark:text-white
                               px-4 py-2.5 focus:outline-none
                               focus:ring-2 focus:ring-emerald-500">

                </div>


                {{-- Ciudad --}}
                <div>

                    <label class="block text-sm font-medium text-slate-700
                                  dark:text-slate-300 mb-1.5">

                        Ciudad

                    </label>

                    <div class="w-full rounded-xl border border-slate-200
                                dark:border-slate-700
                                bg-slate-50 dark:bg-slate-950/50
                                px-4 py-2.5 text-slate-600
                                dark:text-slate-400">

                        {{ $farmacia->ciudad->nombre_ciudad ?? 'Sin ciudad' }}

                    </div>

                    <p class="mt-1.5 text-xs text-slate-400">
                        La ciudad se mantiene desde el registro actual.
                    </p>

                </div>

            </div>

        </div>


        {{-- Coordenadas --}}
        <div class="border-t border-slate-200 dark:border-slate-800 pt-8">

            <div class="mb-5">

                <h3 class="text-base font-semibold text-slate-900
                           dark:text-white">

                    Coordenadas

                </h3>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Podés ingresarlas manualmente si la geocodificación automática
                    no encuentra la ubicación.
                </p>

            </div>


            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                {{-- Latitud --}}
                <div>

                    <label for="lat"
                           class="block text-sm font-medium text-slate-700
                                  dark:text-slate-300 mb-1.5">

                        Latitud

                    </label>

                    <input
                        type="number"
                        id="lat"
                        name="lat"
                        value="{{ old('lat', $farmacia->lat) }}"
                        step="any"
                        min="-90"
                        max="90"
                        placeholder="-31.6333"
                        class="w-full rounded-xl border border-slate-300
                               dark:border-slate-700
                               bg-white dark:bg-slate-950
                               text-slate-900 dark:text-white
                               px-4 py-2.5 focus:outline-none
                               focus:ring-2 focus:ring-emerald-500">

                </div>


                {{-- Longitud --}}
                <div>

                    <label for="lng"
                           class="block text-sm font-medium text-slate-700
                                  dark:text-slate-300 mb-1.5">

                        Longitud

                    </label>

                    <input
                        type="number"
                        id="lng"
                        name="lng"
                        value="{{ old('lng', $farmacia->lng) }}"
                        step="any"
                        min="-180"
                        max="180"
                        placeholder="-60.7000"
                        class="w-full rounded-xl border border-slate-300
                               dark:border-slate-700
                               bg-white dark:bg-slate-950
                               text-slate-900 dark:text-white
                               px-4 py-2.5 focus:outline-none
                               focus:ring-2 focus:ring-emerald-500">

                </div>

            </div>

        </div>


        {{-- Acciones --}}
        <div class="border-t border-slate-200 dark:border-slate-800
                    mt-8 pt-6 flex flex-col-reverse sm:flex-row
                    sm:justify-end gap-3">

            <a href="{{ route('admin.farmacias.index') }}"
               class="inline-flex justify-center items-center
                      px-5 py-2.5 rounded-xl border border-slate-300
                      dark:border-slate-700 text-sm font-medium
                      text-slate-700 dark:text-slate-300
                      hover:bg-slate-50 dark:hover:bg-slate-800
                      transition">

                Cancelar

            </a>

            <button
                type="submit"
                class="inline-flex justify-center items-center
                       px-5 py-2.5 rounded-xl bg-emerald-700
                       hover:bg-emerald-800 text-white text-sm
                       font-semibold transition">

                Guardar cambios

            </button>

        </div>

    </form>

</div>

@endsection