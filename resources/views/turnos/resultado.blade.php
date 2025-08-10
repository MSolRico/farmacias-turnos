<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Farmacias de Turno</title>
</head>
<body>
    <h1>Farmacias de turno en {{ $ciudad->nombre }} para {{ $fecha }}</h1>

    @if($farmacias->isEmpty())
        <p>No se encontraron farmacias de turno para esta fecha.</p>
    @else
        <table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Notas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($farmacias as $farmacia)
                    <tr>
                        <td>{{ $farmacia->nombre }}</td>
                        <td>{{ $farmacia->direccion }}</td>
                        <td>{{ $farmacia->telefono }}</td>
                        <td>{{ $farmacia->notas ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p><a href="{{ route('home') }}">Volver</a></p>
</body>
</html>
