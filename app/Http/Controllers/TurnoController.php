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

        /*
     * Reportes realizados hoy.
     * Solo contamos reportes pendientes o verificados.
     */
        $reportesHoy = DB::table('reportes_farmacia')
            ->select(
                'id_farmacia',
                DB::raw('COUNT(*) as reportes_hoy'),
                DB::raw('MAX(created_at) as ultimo_reporte')
            )
            ->whereDate(
                'fecha_reporte',
                $ahora->toDateString()
            )
            ->whereIn('estado', ['pendiente', 'verificado'])
            ->groupBy('id_farmacia');

        /*
         * Farmacias que están actualmente de turno.
         */
        $farmacias = DB::table('farmacias_turnos')
            ->join(
                'turnos',
                'farmacias_turnos.id_turno',
                '=',
                'turnos.id_turno'
            )
            ->join(
                'farmacias',
                'farmacias_turnos.id_farmacia',
                '=',
                'farmacias.id_farmacia'
            )
            ->join(
                'ciudades',
                'turnos.id_ciudad',
                '=',
                'ciudades.id_ciudad'
            )

            /*
             * Unimos los reportes del día con las farmacias.
             * LEFT JOIN permite que también aparezcan farmacias
             * que todavía no tienen ningún reporte.
             */
            ->leftJoinSub(
                $reportesHoy,
                'reportes_hoy',
                function ($join) {
                    $join->on(
                        'farmacias.id_farmacia',
                        '=',
                        'reportes_hoy.id_farmacia'
                    );
                }
            )

            ->where(
                'turnos.fecha_hora_inicio',
                '<=',
                $ahora
            )
            ->where(
                'turnos.fecha_hora_fin',
                '>',
                $ahora
            )

            ->select(
                'farmacias.id_farmacia',
                'farmacias.nombre',
                'farmacias.direccion',
                'farmacias.telefono',
                'farmacias.lat',
                'farmacias.lng',
                'farmacias_turnos.notas',

                /*
                 * Ciudad a la que pertenece la farmacia.
                 */
                'ciudades.nombre_ciudad',

                DB::raw(
                    'COALESCE(reportes_hoy.reportes_hoy, 0) as reportes_hoy'
                ),

                'reportes_hoy.ultimo_reporte'
            )
            ->distinct()
            ->get();

        /*
         * Todas las ciudades disponibles.

         * Se utilizan en el buscador.
         */
        $ciudades = Ciudad::all();

        $isToday = true;

        return view(
            'turnos.index',
            compact(
                'ciudades',
                'farmacias',
                'isToday'
            )
        );
    }

    public function buscar(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'ciudad' => 'required|integer',
        ]);

        $ciudad_id = $request->ciudad;
        $momentoReferencia = Carbon::parse(
            $request->fecha
        )->setTime(12, 0, 0);
        $fecha = $request->fecha;

        /*
         * Reportes realizados en el día que se está consultando.
         *
         * Esto permite consultar días anteriores y mostrar
         * los reportes correspondientes a esa fecha.
         */
        $reportesDelDia = DB::table('reportes_farmacia')
            ->select(
                'id_farmacia',
                DB::raw('COUNT(*) as reportes_hoy'),
                DB::raw('MAX(created_at) as ultimo_reporte')
            )
            ->whereDate('fecha_reporte', $fecha)
            ->whereIn('estado', ['pendiente', 'verificado'])
            ->groupBy('id_farmacia');

        /*
         * Farmacias de turno correspondientes a la fecha
         * y ciudad seleccionadas.
         */
        $farmacias = DB::table('farmacias_turnos')
            ->join(
                'turnos',
                'farmacias_turnos.id_turno',
                '=',
                'turnos.id_turno'
            )
            ->join(
                'farmacias',
                'farmacias_turnos.id_farmacia',
                '=',
                'farmacias.id_farmacia'
            )

            ->leftJoinSub(
                $reportesDelDia,
                'reportes_dia',
                function ($join) {
                    $join->on(
                        'farmacias.id_farmacia',
                        '=',
                        'reportes_dia.id_farmacia'
                    );
                }
            )

            ->where(
                'turnos.fecha_hora_inicio',
                '<=',
                $momentoReferencia
            )
            ->where(
                'turnos.fecha_hora_fin',
                '>',
                $momentoReferencia
            )
            ->where(
                'turnos.id_ciudad',
                '=',
                $ciudad_id
            )

            ->select(
                'farmacias.id_farmacia',
                'farmacias.nombre',
                'farmacias.direccion',
                'farmacias.telefono',
                'farmacias.lat',
                'farmacias.lng',
                'farmacias_turnos.notas',

                DB::raw(
                    'COALESCE(reportes_dia.reportes_hoy, 0) as reportes_hoy'
                ),

                'reportes_dia.ultimo_reporte'
            )

            ->distinct()
            ->get();

        $ciudad = Ciudad::find($ciudad_id);

        $ciudades = Ciudad::all();

        return view(
            'turnos.resultado',
            compact(
                'farmacias',
                'ciudad',
                'fecha',
                'ciudades'
            )
        );
    }
}
