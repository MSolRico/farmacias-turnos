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
        // [CORRECCIÓN] Se añade la configuración regional aquí para que el h2 se traduzca.
        Carbon::setLocale('es'); 
        
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

    /**
     * [FUNCIÓN DE GEOLOCALIZACIÓN] Recibe la ubicación del usuario y el ID de la ciudad para devolver las farmacias de turno ordenadas por distancia.
     */
    public function getFarmaciasCercanas(Request $request)
    {
        // 1. Validar que se recibieron latitud, longitud y el ID de la ciudad
        $request->validate([
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'ciudad_id' => 'required|integer', 
        ]);

        $userLat = $request->input('latitud');
        $userLon = $request->input('longitud');
        $ciudadId = $request->input('ciudad_id');
        $hoy = Carbon::today(); // Para filtrar por turnos de hoy

        // Radio de la Tierra en Kilómetros (necesario para la fórmula Haversine)
        $radioTierraKm = 6371;

        // 2. Consulta con la fórmula Haversine y los filtros de turno
        $farmacias = DB::table('farmacias_turnos')
            ->join('turnos', 'farmacias_turnos.id_turno', '=', 'turnos.id_turno')
            ->join('farmacias', 'farmacias_turnos.id_farmacia', '=', 'farmacias.id_farmacia')
            
            // FILTROS DE TURNO (replicando la lógica de index/buscar)
            ->whereDate('turnos.fecha_hora_inicio', '<=', $hoy)
            ->whereDate('turnos.fecha_hora_fin', '>=', $hoy)
            ->where('turnos.id_ciudad', '=', $ciudadId) 
            
            // SELECCIÓN Y CÁLCULO DE DISTANCIA (USANDO farmacias.lat y farmacias.lng)
            ->selectRaw("
                farmacias.id_farmacia,
                farmacias.nombre,
                farmacias.direccion,
                farmacias.telefono,
                farmacias.lat,
                farmacias.lng,
                farmacias_turnos.notas,
                ($radioTierraKm * acos(
                    cos(radians(?)) * cos(radians(farmacias.lat)) * cos(radians(farmacias.lng) - radians(?)) 
                    + sin(radians(?)) * sin(radians(farmacias.lat))
                )) AS distancia_km", [$userLat, $userLon, $userLat])
            
            ->distinct()
            
            // 3. Ordenar por la distancia calculada (el más cercano primero)
            ->orderBy('distancia_km', 'asc') 
            ->get();

        // 4. Devolver la lista ordenada como JSON
        return response()->json([
            'farmacias' => $farmacias,
            'user_location' => [
                'lat' => $userLat,
                'lng' => $userLon
            ]
        ]);
    }
}