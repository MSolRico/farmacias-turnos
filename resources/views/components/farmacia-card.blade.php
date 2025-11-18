<div class="card mb-3" x-data="{ farmaciaId: {{ $farmacia->id_farmacia }}, farmaciaNombre: '{{ $farmacia->nombre }}' }">
    <div class="card-body">
        <h5 class="card-title mb-1">
            <b>{{ $farmacia->nombre }}</b>
        </h5>
        <ul class="list-group list-group-flush">
            <li class="list-group-item border-0 py-1 d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#6cde58" class="bi bi-geo-alt svg-16 me-2" viewBox="0 0 16 16">
                    <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10" />
                    <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6" />
                </svg>
                <span>Dirección: {{ $farmacia->direccion }}</span>
            </li>
            <li class="list-group-item border-0 py-1 d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#6cde58" class="bi bi-telephone svg-16 me-2" viewBox="0 0 16 16">
                    <path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z" />
                </svg>
                <span>Teléfono: {{ $farmacia->telefono }}</span>
            </li>
            
            @if(isset($farmacia->notas) && $farmacia->notas)
                <li class="list-group-item border-0 py-1">
                    <div class="alert alert-info mb-0 py-2">
                        <small><strong>Nota:</strong> {{ $farmacia->notas }}</small>
                    </div>
                </li>
            @endif

            @if(isset($farmacia->reportada_cerrada) && $farmacia->reportada_cerrada)
                <li class="list-group-item border-0 py-1">
                    <div class="alert alert-warning mb-0 py-2 d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle me-2" viewBox="0 0 16 16">
                            <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
                            <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                        </svg>
                        <small><strong>Reportada como cerrada por la Comunidad</strong> ({{ $farmacia->total_reportes ?? 1 }} reporte{{ ($farmacia->total_reportes ?? 1) > 1 ? 's' : '' }})</small>
                    </div>
                </li>
            @endif

            @if (isset($isToday) && $isToday)
                <li class="list-group-item border-0 py-1">
                    @auth
                        <button 
                            type="button" class="tw-report-btn"
                            @click="window.selectedFarmacia = { id: farmaciaId, nombre: farmaciaNombre }; $dispatch('open-modal', 'confirmReport')">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm1 14h-2v-2h2Zm0-4h-2V7h2Z"/>
                            </svg>
                            Reportar como Cerrado
                        </button>
                    @else
                        <button type="button" class="tw-report-btn" @click="$dispatch('open-modal', 'login')">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm1 14h-2v-2h2Zm0-4h-2V7h2Z"/>
                            </svg>
                            Reportar como Cerrado
                        </button>
                    @endauth
                </li>
            @endif
        </ul>
    </div>
</div>