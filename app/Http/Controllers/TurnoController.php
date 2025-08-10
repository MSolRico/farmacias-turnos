<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TurnoController extends Controller
{
    public function index()
    {
        // Lista de ciudades para el formulario
        $ciudades = DB::table('ciudades')->get();
        return view('turnos.index', compact('ciudades'));
    }

    public function buscar(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'ciudad' => 'required|integer',
        ]);

        $fecha = $request->fecha;
        $ciudad_id = $request->ciudad;

        $farmacias = DB::table('farmacias_turnos')
            ->join('turnos', 'farmacias_turnos.id_turno', '=', 'turnos.id_turno')
            ->join('farmacias', 'farmacias_turnos.id_farmacia', '=', 'farmacias.id_farmacia')
            ->whereDate('turnos.fecha_hora_inicio', '<=', $fecha)
            ->whereDate('turnos.fecha_hora_fin', '>=', $fecha)
            ->where('turnos.id_ciudad', '=', $ciudad_id)
            ->select(
                'farmacias.id_farmacia',
                'farmacias.nombre',
                'farmacias.direccion',
                'farmacias.telefono',
                'farmacias_turnos.notas'
            )
            ->distinct()
            ->get();

        // Forzamos que siempre exista $ciudad->nombre
        $ciudad = DB::table('ciudades')
        ->where('id_ciudad', $ciudad_id)
        ->select('nombre_ciudad as nombre') // <-- cambiar por el nombre real
        ->first();

        return view('turnos.resultado', compact('farmacias', 'fecha', 'ciudad'));
    }
}
