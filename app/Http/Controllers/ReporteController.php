<?php

namespace App\Http\Controllers;

use App\Models\ReporteFarmacia;
use App\Models\Farmacia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReporteController extends Controller
{
    /**
     * Crear un nuevo reporte
     */
    public function store(Request $request)
    {
        $request->validate([
            'farmacia_id' => 'required|exists:farmacias,id_farmacia',
            'comentario' => 'nullable|string|max:500',
        ]);

        try {
            // Verificar si ya reportó esta farmacia hoy
            $reporteExistente = ReporteFarmacia::where('id_farmacia', $request->farmacia_id)
                ->where('id_usuario', Auth::id())
                ->whereDate('fecha_reporte', today())
                ->first();

            if ($reporteExistente) {
                return redirect()->back()->with('warning', 'Ya has reportado esta farmacia como cerrada hoy.');
            }

            // Crear el reporte
            ReporteFarmacia::create([
                'id_farmacia' => $request->farmacia_id,
                'id_usuario' => Auth::id(),
                'comentario' => $request->comentario,
                'fecha_reporte' => now(),
                'estado' => 'pendiente',
            ]);

            return redirect()->back()->with('success', 'Reporte enviado correctamente. Gracias por tu colaboración.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Hubo un error al procesar tu reporte. Por favor, intenta nuevamente.');
        }
    }

    /**
     * Ver mis reportes
     */
    public function misReportes()
    {
        $reportes = ReporteFarmacia::where('id_usuario', Auth::id())
            ->with('farmacia')
            ->orderBy('fecha_reporte', 'desc')
            ->paginate(10);

        return view('reportes.mis-reportes', compact('reportes'));
    }

    /**
     * Eliminar un reporte (solo el usuario que lo creó)
     */
    public function destroy($id)
    {
        $reporte = ReporteFarmacia::findOrFail($id);

        // Verificar que el usuario sea el dueño del reporte
        if ($reporte->id_usuario !== Auth::id()) {
            return redirect()->back()->with('error', 'No tienes permiso para eliminar este reporte.');
        }

        $reporte->delete();

        return redirect()->back()->with('success', 'Reporte eliminado correctamente.');
    }
}