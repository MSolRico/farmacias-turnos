<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReporteController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'id_farmacia' => 'required|integer|exists:farmacias,id_farmacia',
            'id_turno' => 'nullable|integer|exists:turnos,id_turno',
            'comentario' => 'nullable|string|max:500',
        ]);

        $yaReporto = Reporte::where('id_usuario', Auth::id())
            ->where('id_farmacia', $request->id_farmacia)
            ->whereDate('fecha_reporte', today())
            ->exists();

        if ($yaReporto) {
            return back()->with('error', 'Ya reportaste esta farmacia hoy.');
        }

        Reporte::create([
            'id_farmacia' => $request->id_farmacia,
            'id_usuario' => Auth::id(),
            'id_turno' => $request->id_turno,
            'comentario' => $request->comentario,
            'estado' => 'pendiente',
            'fecha_reporte' => today(),
        ]);

        return back()->with(
            'success',
            'Reporte registrado correctamente.'
        );
    }

    public function misReportes()
    {
        $reportes = Reporte::with(['farmacia', 'turno'])
            ->where('id_usuario', Auth::id())
            ->latest('fecha_reporte')
            ->get();

        return view('reportes.mis-reportes', compact('reportes'));
    }

    public function destroy($id)
    {
        $reporte = Reporte::where('id_reporte', $id)
            ->where('id_usuario', Auth::id())
            ->firstOrFail();

        $reporte->delete();

        return back()->with(
            'success',
            'Reporte eliminado correctamente.'
        );
    }
}