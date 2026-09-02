<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farmacia;
use App\Models\ImportacionTurno;
use App\Models\Reporte;

class DashboardController extends Controller
{
    public function index()
    {
        $totalFarmacias = Farmacia::count();

        $farmaciasSinCoordenadas = Farmacia::whereNull('lat')
            ->orWhereNull('lng')
            ->count();

        $reportesPendientes = Reporte::where('estado', 'pendiente')
            ->count();

        $ultimaImportacion = ImportacionTurno::orderByDesc('anio')
            ->orderByDesc('mes')
            ->first();

        $importaciones = ImportacionTurno::orderBy('anio')
            ->orderBy('mes')
            ->get();

        return view('admin.dashboard', compact(
            'totalFarmacias',
            'farmaciasSinCoordenadas',
            'reportesPendientes',
            'ultimaImportacion',
            'importaciones'
        ));
    }
}