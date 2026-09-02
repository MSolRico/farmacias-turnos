<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farmacia;
use App\Models\Turno;
use Illuminate\Http\Request;

class TurnoController extends Controller
{
    public function index(Request $request)
    {
        $fecha = $request->input('fecha');

        $turnos = Turno::with(['ciudad', 'farmacias'])
            ->when($fecha, function ($query) use ($fecha) {
                $query->whereDate('fecha_hora_inicio', $fecha);
            })
            ->orderByDesc('fecha_hora_inicio')
            ->get();

        return view('admin.turnos.index', compact('turnos', 'fecha'));
    }

    public function show(Turno $turno)
    {
        $turno->load(['ciudad', 'farmacias']);

        $farmaciasDisponibles = Farmacia::where(
            'id_ciudad',
            $turno->id_ciudad
        )
        ->orderBy('nombre')
        ->get();

        return view('admin.turnos.show', compact(
            'turno',
            'farmaciasDisponibles'
        ));
    }

    public function updateFarmacias(Request $request, Turno $turno)
    {
        $datos = $request->validate([
            'farmacias' => ['nullable', 'array'],
            'farmacias.*' => ['integer', 'exists:farmacias,id_farmacia'],
        ]);

        $farmacias = Farmacia::where('id_ciudad', $turno->id_ciudad)
            ->whereIn(
                'id_farmacia',
                $datos['farmacias'] ?? []
            )
            ->pluck('id_farmacia');

        $turno->farmacias()->sync($farmacias);

        return redirect()
            ->route('admin.turnos.show', $turno)
            ->with('success', 'Farmacias del turno actualizadas correctamente.');
    }
}