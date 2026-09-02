<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Turno;

class TurnoController extends Controller
{
    public function index()
    {
        $turnos = Turno::with(['ciudad', 'farmacias'])
            ->orderByDesc('fecha_hora_inicio')
            ->get();

        return view('admin.turnos.index', compact('turnos'));
    }

    public function show(Turno $turno)
    {
        $turno->load(['ciudad', 'farmacias']);

        return view('admin.turnos.show', compact('turno'));
    }
}