<div class="bg-white border border-gray-200 rounded-2xl p-4 hover:border-emerald-300 transition shadow-sm">

    <div class="flex items-start justify-between gap-4">

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

                    <x-icons.location class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" />

                    <span>
                        {{ $farmacia->direccion }}, {{ $farmacia->nombre_ciudad }}
                    </span>

                </p>

                {{-- Teléfono --}}
                @if($farmacia->telefono)

                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">

                    <x-icons.phone class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" />

                    <span>{{ $farmacia->telefono }}</span>

                </p>

                @endif

            </div>

        </div>


        {{-- Acciones --}}
        <div class="flex items-center gap-2 flex-shrink-0">

            <span class="hidden sm:inline-flex px-2 py-1 bg-emerald-100 text-emerald-700 font-semibold text-[10px] rounded-md whitespace-nowrap">
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
    @if(($puedeReportar ?? true) || (($farmacia->reportes_hoy ?? 0) > 0))

    <div class="mt-3 pt-3 border-t border-gray-100">

        <div class="flex flex-wrap items-center justify-between gap-2 sm:gap-3">

            {{-- Reportar: solo para el turno actual --}}
            @if($puedeReportar ?? true)

            <div class="flex items-center gap-2 min-w-0">

                <span class="text-xs text-slate-500 whitespace-nowrap">
                    ¿Está cerrada?
                </span>

                @auth

                <button
                    type="button"
                    @click="$dispatch('open-modal', 'confirm-report-{{ $farmacia->id_farmacia }}')"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-red-600 transition">

                    <x-icons.alert class="w-3.5 h-3.5" />

                    Reportar

                </button>

                @else

                <button
                    type="button"
                    @click="$dispatch('open-modal', 'login')"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-emerald-700 transition">

                    <x-icons.lock class="w-3.5 h-3.5" />

                    Reportar

                </button>

                @endauth

            </div>

            @endif

            {{-- Estado del reporte --}}
            @if(($farmacia->reportes_hoy ?? 0) > 0)

            {{-- Separador --}}
            @if($puedeReportar ?? true)

            <div class="h-4 w-px bg-gray-200 flex-shrink-0"></div>

            @endif

            <div class="flex items-center gap-2 flex-shrink-0">

                <x-icons.warning class="w-3.5 h-3.5 text-red-500" />

                <span class="text-xs font-medium text-red-600 whitespace-nowrap">
                    Reportada como cerrada
                </span>

            </div>

            @endif

        </div>

    </div>

    @endif

</div>

{{-- ============================================
                        MODAL DE CONFIRMACIÓN
                     ============================================ --}}
@auth
<x-modal
    name="confirm-report-{{ $farmacia->id_farmacia }}"
    maxWidth="md"
    focusable>

    <div class="px-5 py-5 text-center">

        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600">

            <x-icons.warning class="w-[26px] h-[26px]" />

        </div>

        <h5 class="text-lg font-semibold text-gray-900">
            ¿Confirmás que
            <strong>{{ $farmacia->nombre }}</strong>
            se encuentra
            <strong class="text-red-600">cerrada</strong>?
        </h5>

        <div class="mt-4 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3">

            <p class="text-xs leading-relaxed text-amber-800">
                Reportá la farmacia solamente si comprobaste que
                <strong>no está prestando servicio durante su turno</strong>.
                Tu reporte ayuda a otros usuarios a evitar un viaje innecesario.
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