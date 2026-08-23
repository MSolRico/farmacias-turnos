<section class="bg-[#f1f6f5] border border-slate-100 rounded-3xl p-6 relative overflow-visible">

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
                <div class="flex items-center bg-white border border-gray-200 rounded-xl px-3 shadow-sm flex-1 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200 transition">

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
                        class="w-full border-0 bg-transparent text-sm text-slate-700 font-medium px-2 py-3 focus:ring-0 focus:outline-none cursor-pointer">
                </div>

                {{-- Ciudad Custom Dropdown (Alpine) --}}
                <div
                    x-data="{ 
                        open: false, 
                        selected: '{{ request('ciudad', '') }}', 
                        label: '{{ request('ciudad') ? ($ciudades->firstWhere('id_ciudad', request('ciudad'))->nombre_ciudad ?? 'Elegir ciudad') : 'Elegir ciudad' }}' 
                    }"
                    class="relative flex-1">

                    {{-- Botón Selector --}}
                    <button
                        type="button"
                        @click="open = !open"
                        @click.away="open = false"
                        class="w-full h-full flex items-center justify-between bg-white border border-gray-200 rounded-xl px-3 py-3 shadow-sm text-sm font-medium focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition">

                        <div class="flex items-center gap-2 truncate">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 flex-shrink-0">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span x-text="label" :class="selected ? 'text-slate-700' : 'text-gray-400'"></span>
                        </div>

                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    {{-- Field Oculto para que viaje en el GET --}}
                    <input type="hidden" name="ciudad" :value="selected" required>

                    {{-- Lista Flotante Estilizada --}}
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute z-[100] left-0 right-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-xl max-h-56 overflow-y-auto p-1.5 focus:outline-none"
                        style="display: none;">

                        @foreach($ciudades as $ciudad)
                        <button
                            type="button"
                            @click="selected = '{{ $ciudad->id_ciudad }}'; label = '{{ $ciudad->nombre_ciudad }}'; open = false"
                            class="w-full text-left px-3.5 py-2.5 text-sm font-medium rounded-xl transition-colors flex items-center justify-between"
                            :class="selected == '{{ $ciudad->id_ciudad }}' ? 'bg-emerald-50 text-emerald-800' : 'text-slate-700 hover:bg-slate-50'">
                            <span>{{ $ciudad->nombre_ciudad }}</span>

                            <template x-if="selected == '{{ $ciudad->id_ciudad }}'">
                                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </template>
                        </button>
                        @endforeach
                    </div>
                </div>

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