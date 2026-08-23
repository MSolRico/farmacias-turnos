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

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="flex-shrink-0">
                        <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>

                    <span>
                        {{ $farmacia->direccion }}, {{ $farmacia->nombre_ciudad }}
                    </span>

                </p>

                {{-- Teléfono --}}
                @if($farmacia->telefono)

                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>

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

            {{-- Centrar en mapa --}}
            @if($farmacia->lat && $farmacia->lng)

            <button
                type="button"
                onclick="centrarMapa({{ $farmacia->lat }}, {{ $farmacia->lng }})"
                class="p-2 text-slate-400 hover:text-emerald-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition"
                title="Ver en el mapa">

                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>

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

            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="15"
                height="15"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2">

                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />

            </svg>

            Reportar como cerrada

        </button>

        @else

        <button
            type="button"
            @click="$dispatch('open-modal', 'login')"
            class="w-full inline-flex items-center justify-center gap-2 bg-slate-50 hover:bg-emerald-50 border border-gray-200 hover:border-emerald-200 text-slate-600 hover:text-emerald-700 rounded-xl py-2 text-xs font-semibold transition">

            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="15"
                height="15"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2">

                <rect x="3" y="11" width="18" height="11" rx="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />

            </svg>

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

            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="26"
                height="26"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                aria-hidden="true">

                <path d="M10.3 2.86 1.82 17a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 2.86a2 2 0 0 0-3.4 0Z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />

            </svg>

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