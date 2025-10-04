<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ciudad;
use Carbon\Carbon;

class TurnoController extends Controller
{
    public function index()
    {
        Carbon::setLocale('es'); 

        $hoy = Carbon::today();
        $mes = ucfirst($hoy->translatedFormat('F')); 
        $anio = $hoy->year;
        
        $ciudad_santa_fe = Ciudad::where('nombre_ciudad', 'Santa Fe')->first();

        $farmacias = collect();

        if ($ciudad_santa_fe) {
            $farmacias = DB::table('farmacias_turnos')
                ->join('turnos', 'farmacias_turnos.id_turno', '=', 'turnos.id_turno')
                ->join('farmacias', 'farmacias_turnos.id_farmacia', '=', 'farmacias.id_farmacia')
                ->whereDate('turnos.fecha_hora_inicio', '<=', $hoy)
                ->whereDate('turnos.fecha_hora_fin', '>=', $hoy)
                ->where('turnos.id_ciudad', '=', $ciudad_santa_fe->id_ciudad)
                ->select(
                    'farmacias.id_farmacia',
                    'farmacias.nombre',
                    'farmacias.direccion',
                    'farmacias.telefono',
                    'farmacias.lat',
                    'farmacias.lng',
                    'farmacias_turnos.notas'
                )
                ->distinct()
                ->get();
        }

        $ciudades = Ciudad::all();

        return view('turnos.index', compact('ciudades', 'farmacias', 'ciudad_santa_fe', 'mes', 'anio'));
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
                'farmacias.lat',
                'farmacias.lng',
                'farmacias_turnos.notas'
            )
            ->distinct()
            ->get();

        $ciudad = Ciudad::find($ciudad_id);

        $ciudades = Ciudad::all();

        return view('turnos.resultado', compact('farmacias', 'ciudad', 'fecha', 'ciudades'));
    }
}