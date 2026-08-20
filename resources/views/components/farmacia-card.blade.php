<div class="mb-4 rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="p-4">

        {{-- Nombre --}}
        <h5 class="mb-2 text-lg font-semibold text-gray-900">
            {{ $farmacia->nombre }}
        </h5>

        <ul class="space-y-1">

            {{-- Dirección --}}
            <li class="flex items-center border-0 py-1 text-sm text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#6cde58" class="mr-2 shrink-0" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10" />
                    <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6" />
                </svg>

                {{ $farmacia->direccion }}
            </li>

            {{-- Teléfono --}}
            <li class="flex items-center border-0 py-1 text-sm text-gray-700">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    fill="#6cde58"
                    class="mr-2 shrink-0"
                    viewBox="0 0 16 16"
                    aria-hidden="true">
                    <path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z" />
                </svg>

                {{ $farmacia->telefono }}
            </li>

            {{-- Nota --}}
            @if(isset($farmacia->notas) && $farmacia->notas)
            <li class="py-1">
                <div class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-800">
                    <strong>Nota:</strong>
                    {{ $farmacia->notas }}
                </div>
            </li>
            @endif

            {{-- Aviso de reportes acumulados --}}
            @if($farmacia->reportes_hoy > 0)
            <li class="py-1">
                <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                    <strong>Reportada como cerrada</strong>

                    <span>
                        {{ $farmacia->reportes_hoy }}
                        {{ $farmacia->reportes_hoy == 1 ? 'persona la reportó' : 'personas la reportaron' }}
                        hoy.
                    </span>
                </div>
            </li>
            @endif

            @if($farmacia->ultimo_reporte)
            <div class="mt-1 text-xs text-gray-500">
                Último reporte:
                {{ \Carbon\Carbon::parse($farmacia->ultimo_reporte)->diffForHumans() }}
            </div>
            @endif

            {{-- Botón de reporte --}}
            @if(isset($isToday) && $isToday)
            <li class="py-1">

                @auth

                @if($yaReportoHoy)

                <button
                    type="button"
                    class="w-full cursor-not-allowed rounded-full bg-red-500 px-4 py-2 text-white opacity-70"
                    disabled>
                    Ya reportaste hoy
                </button>

                @else

                <button type="button"
                    @click="$dispatch('open-modal', 'confirm-report-{{ $farmacia->id_farmacia }}')"
                    class="tw-report-btn">

                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm1 14h-2v-2h2Zm0-4h-2V7h2Z" />
                    </svg>

                    Reportar como Cerrado
                </button>

                @endif

                @else

                <button
                    type="button"
                    @click="$dispatch('open-modal', 'login')"
                    class="w-full inline-flex items-center justify-center gap-2 bg-slate-50 hover:bg-red-50 border border-gray-200 hover:border-red-200 text-slate-600 hover:text-red-600 rounded-xl py-2 text-xs font-semibold transition">

                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm1 14h-2v-2h2Zm0-4h-2V7h2Z" />
                    </svg>

                    Reportar como Cerrado

                </button>

                @endauth

            </li>
            @endif

        </ul>
    </div>
</div>