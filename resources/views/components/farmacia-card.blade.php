<div class="card mb-3">
    <div class="card-body">

        <h5 class="card-title mb-1"><b>{{ $farmacia->nombre }}</b></h5>

        <ul class="list-group list-group-flush">

            {{-- Dirección --}}
            <li class="list-group-item border-0 py-1">
                Dirección: {{ $farmacia->direccion }}
            </li>

            {{-- Teléfono --}}
            <li class="list-group-item border-0 py-1">
                Teléfono: {{ $farmacia->telefono }}
            </li>

            {{-- Nota --}}
            @if(isset($farmacia->notas) && $farmacia->notas)
            <li class="list-group-item border-0 py-1">
                <div class="alert alert-info mb-0 py-2">
                    <small><strong>Nota:</strong> {{ $farmacia->notas }}</small>
                </div>
            </li>
            @endif

            @php
            use App\Models\Reporte;

            $userId = auth()->id();
            $hoy = now()->toDateString();

            $cantidadReportes = Reporte::where('id_farmacia', $farmacia->id_farmacia)->count();

            $yaReportoHoy = $userId
            ? Reporte::where('id_usuario', $userId)
            ->where('id_farmacia', $farmacia->id_farmacia)
            ->whereDate('created_at', $hoy)
            ->exists()
            : false;
            @endphp

            {{-- Aviso de reportes acumulados --}}
            @if($cantidadReportes > 0)
            <li class="list-group-item border-0 py-1">
                <div class="alert alert-warning mb-0 py-2">
                    <strong>Reportada como cerrada</strong>
                    ({{ $cantidadReportes }} reporte{{ $cantidadReportes > 1 ? 's' : '' }})
                </div>
            </li>
            @endif

            {{-- Botón de reporte funcional --}}
            @if(isset($isToday) && $isToday)
            <li class="list-group-item border-0 py-1">

                @auth

                @if($yaReportoHoy)
                <button class="btn btn-danger w-100" disabled>
                    Ya reportaste hoy
                </button>

                @else
                <form action="{{ route('reportar.cerrada') }}" method="POST" class="w-100">
                    @csrf
                    <input type="hidden" name="id_farmacia" value="{{ $farmacia->id_farmacia }}">

                    <button type="submit"
                        class="btn {{ $cantidadReportes > 0 ? 'btn-danger' : 'btn-warning' }} w-100">
                        @if($cantidadReportes > 0)
                        Reportada cerrada ({{ $cantidadReportes }})
                        @else
                        Reportar como Cerrado
                        @endif
                    </button>
                </form>
                @endif

                @else
                <button type="button"
                    class="btn btn-warning w-100"
                    @click="$dispatch('open-modal','login')">
                    Reportar como Cerrado
                </button>
                @endauth

            </li>
            @endif


        </ul>
    </div>
</div>