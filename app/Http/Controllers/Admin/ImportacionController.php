<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportacionTurno;

class ImportacionController extends Controller
{
    public function index()
    {
        $importaciones = ImportacionTurno::orderByDesc('anio')
            ->orderByDesc('mes')
            ->get();

        return view('admin.importaciones.index', compact('importaciones'));
    }

    public function show(ImportacionTurno $importacion)
    {
        return view('admin.importaciones.show', compact('importacion'));
    }
}
