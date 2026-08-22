<section class="bg-[#f1f6f5] border border-slate-100 rounded-3xl p-6 relative overflow-hidden">

    <div class="relative z-10 max-w-xl">

        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 leading-tight">
            Encontrá la
            <span class="block text-emerald-800">farmacia de turno</span>
            <span class="block">más cercana</span>
        </h2>

        <p class="text-xs text-slate-500 leading-relaxed mt-3 max-w-2xs">
            Información actualizada todos los días para que siempre encuentres la farmacia que está de turno.
        </p>

        {{-- BUSCADOR --}}
        <form
            action="{{ route('buscar') }}"
            method="GET"
            class="mt-5">
            <div class="flex flex-col sm:flex-row gap-2">

                {{-- Fecha --}}
                <div class="flex items-center bg-white border border-gray-200 rounded-xl px-3 shadow-sm flex-1">

                    {{-- SVG calendario --}}
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="text-gray-400 flex-shrink-0">
                        <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>

                    <input
                        type="date"
                        name="fecha"
                        value="{{ now()->format('Y-m-d') }}"
                        required
                        class="w-full border-0 bg-transparent text-sm text-slate-700 px-2 py-3 focus:ring-0 focus:outline-none">
                </div>

                {{-- Ciudad --}}
                <select
                    name="ciudad"
                    required
                    class="bg-white border border-gray-200 rounded-xl px-3 py-3 text-sm text-slate-700 shadow-sm focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 outline-none">
                    <option value="">Elegir ciudad</option>

                    @foreach($ciudades as $ciudad)
                    <option value="{{ $ciudad->id_ciudad }}">
                        {{ $ciudad->nombre_ciudad }}
                    </option>
                    @endforeach
                </select>

                {{-- Botón --}}
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl px-4 py-3 text-sm font-semibold transition shadow-sm">
                    Buscar

                    {{-- SVG lupa --}}
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>

            </div>
        </form>

        {{-- Volver a hoy --}}
        <a
            href="{{ route('dashboard') }}"
            class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-emerald-600 mt-3 font-medium transition">
            {{-- SVG actualizar --}}
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="13"
                height="13"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2">
                <path d="M3 12a9 9 0 0 1 15.6-6"></path>
                <polyline points="18 3 18 7 14 7"></polyline>
                <path d="M21 12a9 9 0 0 1-15.6 6"></path>
                <polyline points="6 21 6 17 10 17"></polyline>
            </svg>

            Ver farmacias de hoy
        </a>

    </div>

    {{-- ILUSTRACIÓN --}}
    <div class="hidden sm:flex absolute right-6 bottom-21 w-58 h-58 rounded-3xl items-center justify-center text-emerald-600">

        <img src="{{ asset('images/farmacia.png') }}" alt="ilustracion farmacia">

    </div>

</section>