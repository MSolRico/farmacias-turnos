<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Services\GeocodeService;
use App\Models\Farmacia;
use App\Models\Turno;
use App\Models\Ciudad;
use Illuminate\Support\Facades\Log;

class TurnoDataPersister
{
    protected GeocodeService $geo;

    public function __construct(GeocodeService $geo)
    {
        $this->geo = $geo;
    }

    public function guardarEnBD(array $items): array
    {
        $stats = ['farmacias' => 0, 'turnos' => 0, 'actualizadas' => 0, 'rechazadas' => 0];
        DB::beginTransaction();
        try {
            foreach ($items as $it) {
                // validar nombre minimo
                if (empty($it['nombre']) || empty($it['turn_dates'])) continue;

                if (isset($it['confianza']) && $it['confianza'] < 45) {
                    \Log::warning("❌ Farmacia rechazada por baja confianza: {$it['nombre']} ({$it['confianza']}%)");
                    $stats['rechazadas']++;
                    continue;
                }

                [$inicio, $fin] = $it['turn_dates'];

                $ciudad = Ciudad::firstOrCreate(['nombre_ciudad' => $it['ciudad']]);

                $farmacia = Farmacia::where('nombre', $it['nombre'])
                    ->where('id_ciudad', $ciudad->id_ciudad)
                    ->first();

                if (!$farmacia) {
                    $farmacia = Farmacia::create([
                        'nombre' => $it['nombre'],
                        'direccion' => $it['direccion'],
                        'telefono' => $it['telefono'],
                        'id_ciudad' => $ciudad->id_ciudad
                    ]);
                    $stats['farmacias']++;
                } else {
                    $changed = false;
                    if (empty($farmacia->direccion) && !empty($it['direccion'])) {
                        $farmacia->direccion = $it['direccion'];
                        $changed = true;
                    }
                    if (empty($farmacia->telefono) && !empty($it['telefono'])) {
                        $farmacia->telefono = $it['telefono'];
                        $changed = true;
                    }
                    if ($changed) {
                        $farmacia->save();
                        $stats['actualizadas']++;
                    }
                }

                // --- GEOCODING DESACTIVADO TEMPORALMENTE ---
                // Esto es lo que causaba la demora de 5+ minutos.
                // Lo comentamos para probar la carga de fechas y nombres rápido.
                /*
                if ((empty($farmacia->lat) || empty($farmacia->lng)) && !empty($farmacia->direccion)) {
                    [$lat, $lng] = $this->geo->buscarVariantes($farmacia->direccion, $ciudad->nombre_ciudad);
                    if ($lat && $lng) {
                        $farmacia->lat = $lat;
                        $farmacia->lng = $lng;
                        $farmacia->save();
                    }
                }
                */
                // -------------------------------------------

                $turno = Turno::firstOrNew(
                    [
                        'fecha_hora_inicio' => $inicio->toDateTimeString(),
                        'fecha_hora_fin' => $fin->toDateTimeString(),
                        'id_ciudad' => $ciudad->id_ciudad
                    ],
                    [
                        'nombre_turno' => 'Turno ' . $inicio->format('d/m')
                    ]
                );

                if (!$turno->exists) {
                    $turno->save();
                    \Log::info("✅ Turno CREADO: {$inicio->format('d/m/Y')} - {$fin->format('d/m/Y')}");
                }

                $stats['turnos']++;

                $pivot = DB::table('farmacias_turnos')
                    ->where('id_farmacia', $farmacia->id_farmacia)
                    ->where('id_turno', $turno->id_turno)
                    ->first();

                if (!$pivot) {
                    DB::table('farmacias_turnos')->insert([
                        'id_farmacia' => $farmacia->id_farmacia,
                        'id_turno' => $turno->id_turno,
                        'notas' => $it['notas'] ?? null,
                    ]);
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return ['error' => $e->getMessage()];
        }

        \Log::info("📊 Estadísticas finales: Creadas: {$stats['farmacias']}, Turnos: {$stats['turnos']}, Actualizadas: {$stats['actualizadas']}, Rechazadas: {$stats['rechazadas']}");
        return $stats;
    }
}
