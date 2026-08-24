<div class="h-full">

    <div class="bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-sm lg:sticky lg:top-24 h-[650px] relative">

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
                   bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold
                   shadow-md hover:bg-emerald-50 hover:border-emerald-200 transition" >

            <x-icons.location class="w-4 h-4 text-emerald-700" />

            <span id="texto-farmacias-cercanas">
                Buscar farmacias cerca de mí
            </span>

        </button>

        @endif

    </div>
</div>