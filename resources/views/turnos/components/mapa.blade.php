<div class="lg:sticky lg:top-24">

    <div id="mapa-contenedor" class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-3xl overflow-hidden shadow-sm h-[350px] lg:h-[calc(100vh-8rem)] relative">

        <div
            id="map"
            class="w-full h-full"
            data-farmacias='@json($farmacias)'>
        </div>


        @if($mostrarCercanas ?? true)

            <button
                type="button"
                id="btn-farmacias-cercanas"
                class="absolute top-4 left-1/2 -translate-x-1/2 z-[1000] inline-flex items-center gap-2
                       bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700
                       rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200
                       shadow-md hover:bg-emerald-50 dark:hover:bg-emerald-950
                       hover:border-emerald-200 dark:hover:border-emerald-800 transition">

                <x-icons.location class="w-4 h-4 text-emerald-700 dark:text-emerald-400" />

                <span id="texto-farmacias-cercanas">
                    Buscar farmacias cerca de mí
                </span>

            </button>

        @endif


        {{-- PANTALLA COMPLETA Solo móvil --}}
        <button
            type="button"
            id="btn-pantalla-completa"
            class="lg:hidden absolute bottom-4 right-4 z-[1000] w-10 h-10 inline-flex items-center justify-center
                   bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700
                   rounded-xl text-slate-600 dark:text-slate-300 shadow-md
                   hover:bg-emerald-50 dark:hover:bg-emerald-950
                   hover:text-emerald-700 dark:hover:text-emerald-400 transition"
            aria-label="Ver mapa en pantalla completa">

            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M8 3H5a2 2 0 0 0-2 2v3" />
                <path d="M16 3h3a2 2 0 0 1 2 2v3" />
                <path d="M21 16v3a2 2 0 0 1-2 2h-3" />
                <path d="M8 21H5a2 2 0 0 1-2-2v-3" />
            </svg>
        </button>


        {{-- CERRAR PANTALLA COMPLETA --}}
        <button
            type="button"
            id="btn-cerrar-mapa"
            class="hidden absolute top-4 right-4 z-[1001] w-10 h-10 items-center justify-center
                   bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700
                   rounded-xl text-slate-600 dark:text-slate-300 shadow-md
                   hover:bg-gray-50 dark:hover:bg-slate-800 transition"
            aria-label="Cerrar mapa">

            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <path d="M18 6 6 18" />
                <path d="m6 6 12 12" />
            </svg>
        </button>

    </div>
</div>