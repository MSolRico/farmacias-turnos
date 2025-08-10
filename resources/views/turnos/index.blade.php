@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Buscar farmacias de turno</h2>
    <form action="{{ route('buscar') }}" method="GET" class="row g-3">
        <div class="col-md-4">
            <label>Fecha:</label>
            <input type="date" name="fecha" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label>Ciudad:</label>
            <select name="ciudad" class="form-control" required>
                <option value="">-- Elegir ciudad --</option>
                @foreach($ciudades as $ciudad)
                    <option value="{{ $ciudad->id_ciudad }}">{{ $ciudad->nombre_ciudad }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button class="btn btn-primary w-100">Buscar</button>
        </div>
    </form>
</div>
@endsection
