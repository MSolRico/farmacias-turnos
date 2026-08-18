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

        $ahora = Carbon::now();
        $mes = ucfirst($ahora->translatedFormat('F'));
        $anio = $ahora->year;

        $ciudad_santa_fe = Ciudad::where('nombre_ciudad', 'Santa Fe')->first();

        $farmacias = collect();

        if ($ciudad_santa_fe) {
            $farmacias = DB::table('farmacias_turnos')
                ->join('turnos', 'farmacias_turnos.id_turno', '=', 'turnos.id_turno')
                ->join('farmacias', 'farmacias_turnos.id_farmacia', '=', 'farmacias.id_farmacia')
                ->where('turnos.fecha_hora_inicio', '<=', $ahora)
                ->where('turnos.fecha_hora_fin', '>', $ahora)
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

        $ciudad_id = $request->ciudad;
        $momentoReferencia = Carbon::parse($request->fecha)->setTime(12, 0, 0);
        $fecha = $request->fecha;

        $farmacias = DB::table('farmacias_turnos')
            ->join('turnos', 'farmacias_turnos.id_turno', '=', 'turnos.id_turno')
            ->join('farmacias', 'farmacias_turnos.id_farmacia', '=', 'farmacias.id_farmacia')
            ->where('turnos.fecha_hora_inicio', '<=', $momentoReferencia)
            ->where('turnos.fecha_hora_fin', '>', $momentoReferencia)
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