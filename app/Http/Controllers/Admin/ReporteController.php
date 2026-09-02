<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reporte;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $estado = $request->input('estado');
        $busqueda = $request->input('buscar');

        $reportes = Reporte::with(['farmacia', 'usuario', 'turno'])
            ->when($estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($busqueda, function ($query, $busqueda) {
                $query->whereHas('farmacia', function ($query) use ($busqueda) {
                    $query->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('direccion', 'like', "%{$busqueda}%");
                });
            })
            ->orderByRaw("CASE WHEN estado = 'pendiente' THEN 0 ELSE 1 END")
            ->orderByDesc('fecha_reporte')
            ->orderByDesc('id_reporte')
            ->get();

        return view('admin.reportes.index', compact(
            'reportes',
            'estado',
            'busqueda'
        ));
    }

    public function show(Reporte $reporte)
    {
        $reporte->load(['farmacia', 'usuario', 'turno']);

        return view('admin.reportes.show', compact('reporte'));
    }

    public function update(Request $request, Reporte $reporte)
    {
        $datos = $request->validate([
            'estado' => ['required', 'in:verificado,rechazado'],
        ]);

        $reporte->update([
            'estado' => $datos['estado'],
        ]);

        return redirect()
            ->route('admin.reportes.show', $reporte)
            ->with('success', 'Reporte actualizado correctamente.');
    }
}
