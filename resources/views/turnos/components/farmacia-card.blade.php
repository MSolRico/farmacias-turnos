<div class="bg-white border border-gray-200 rounded-2xl p-4 hover:border-emerald-300 transition shadow-sm">

    <div class="flex items-center justify-between gap-4">

        {{-- Información --}}
        <div class="flex items-start gap-3 min-w-0">

            {{-- Número --}}
            <span class="w-7 h-7 rounded-full bg-emerald-800 text-white flex items-center justify-center font-bold text-xs flex-shrink-0">
                {{ $index + 1 }}
            </span>

            <div class="min-w-0">

                <h4 class="font-bold text-slate-900 text-sm truncate">
                    {{ $farmacia->nombre }}
                </h4>

                {{-- Dirección + ciudad --}}
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">

                    <x-icons.location class="w-3.5 h-3.5 text-slate-400" />

                    <span>
                        {{ $farmacia->direccion }}, {{ $farmacia->nombre_ciudad }}
                    </span>

                </p>

                {{-- Teléfono --}}
                @if($farmacia->telefono)

                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">

                    <x-icons.phone class="w-3.5 h-3.5 text-slate-400" />

                    <span>{{ $farmacia->telefono }}</span>

                </p>

                @endif

            </div>

        </div>


        {{-- Acciones --}}
        <div class="flex items-center gap-2 flex-shrink-0">

            <span class="hidden sm:inline-flex px-2 py-1 bg-emerald-100 text-emerald-700 font-semibold text-[10px] rounded-md">
                De turno
            </span>

            @if(isset($farmacia->distancia_km))

                <span class="px-2 py-1 bg-blue-50 text-blue-600 border border-blue-100 font-semibold text-[10px] rounded-md whitespace-nowrap">
                    {{ number_format($farmacia->distancia_km, 2, ',', '.') }} km
                </span>

            @endif

            {{-- Centrar en mapa --}}
            @if($farmacia->lat && $farmacia->lng)

            <button
                type="button"
                onclick="centrarMapa({{ $farmacia->lat }}, {{ $farmacia->lng }})"
                class="p-2 text-slate-400 hover:text-emerald-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition"
                title="Ver en el mapa">

                <x-icons.location class="w-4 h-4" />

            </button>

            @endif

        </div>

    </div>

    {{-- Nota --}}
    @if($farmacia->notas)

    <div class="mt-3 bg-blue-50 border border-blue-100 rounded-xl px-3 py-2">

        <p class="text-xs text-blue-700">
            <strong>Nota:</strong>
            {{ $farmacia->notas }}
        </p>

    </div>

    @endif

    {{-- Reporte --}}
    <div class="mt-3">

        @auth

        <button
            type="button"
            @click="$dispatch('open-modal', 'confirm-report-{{ $farmacia->id_farmacia }}')"
            class="w-full inline-flex items-center justify-center gap-2 bg-slate-50 hover:bg-red-50 border border-gray-200 hover:border-red-200 text-slate-600 hover:text-red-600 rounded-xl py-2 text-xs font-semibold transition">

            <x-icons.alert class="w-3.5 h-3.5" />

            Reportar como cerrada

        </button>

        @else

        <button
            type="button"
            @click="$dispatch('open-modal', 'login')"
            class="w-full inline-flex items-center justify-center gap-2 bg-slate-50 hover:bg-emerald-50 border border-gray-200 hover:border-emerald-200 text-slate-600 hover:text-emerald-700 rounded-xl py-2 text-xs font-semibold transition">

            <x-icons.lock class="w-3.5 h-3.5" />

            Iniciar sesión para reportar

        </button>

        @endauth

    </div>

</div>

{{-- ============================================
                        MODAL DE CONFIRMACIÓN
                     ============================================ --}}
@auth
<x-modal
    name="confirm-report-{{ $farmacia->id_farmacia }}"
    maxWidth="md"
    focusable>

    {{-- Cabecera --}}
    <div class="border-b border-gray-100 px-6 py-5 text-center">

        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600">

            <x-icons.warning class="w-[26px] h-[26px]" />

        </div>

        <h5 class="text-lg font-semibold text-gray-900">
            Confirmar reporte
        </h5>

    </div>

    {{-- Contenido --}}
    <div class="px-6 py-5 text-center">

        <p class="text-sm leading-relaxed text-gray-700">
            ¿Confirmás que
            <strong>{{ $farmacia->nombre }}</strong>
            se encuentra
            <strong class="text-red-600">cerrada</strong>?
        </p>

        <div class="mt-4 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3">

            <p class="text-xs leading-relaxed text-amber-800">
                Reportá la farmacia solamente si comprobaste que
                <strong>no se encuentra prestando servicio durante su turno</strong>.
                Tu reporte ayudará a otros usuarios a evitar un viaje innecesario.
            </p>

        </div>

    </div>

    {{-- Acciones --}}
    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 px-6 py-4 sm:flex-row sm:justify-end">

        <button
            type="button"
            @click="$dispatch('close-modal', 'confirm-report-{{ $farmacia->id_farmacia }}')"
            class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

            Cancelar

        </button>

        <form
            action="{{ route('reportes.store') }}"
            method="POST">

            @csrf

            <input
                type="hidden"
                name="id_farmacia"
                value="{{ $farmacia->id_farmacia }}">

            <button
                type="submit"
                class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 sm:w-auto">

                Sí, reportar cierre

            </button>

        </form>

    </div>

</x-modal>
@endauth