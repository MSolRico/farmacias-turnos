<?php

namespace App\Http\Controllers;

use App\Models\Reporte;   // 👈 esto es clave
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ReporteController extends Controller
{


    public function store(Request $request)
{
    Reporte::create([
        'id_farmacia' => $request->id_farmacia,
        'id_usuario'  => Auth::check() ? Auth::id() : null,
 // null si no está logueado
        'motivo'      => 'cerrada',
    ]);

    return back()->with('success', 'Reporte registrado correctamente.');
}

}