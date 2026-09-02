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
        $stats = [
            'farmacias_nuevas' => 0,
            'farmacias_actualizadas' => 0,
            'farmacias_rechazadas' => 0,
            'turnos_nuevos' => 0,
            'asignaciones_creadas' => 0,
        ];
        DB::beginTransaction();
        try {
            foreach ($items as $it) {
                // validar nombre minimo
                if (empty($it['nombre']) || empty($it['turn_dates'])) continue;

                // Rechazar farmacias con confianza muy baja
                if (isset($it['confianza']) &&$it['confianza'] < 45) {
                    Log::warning("❌ Farmacia rechazada por baja confianza: {$it['nombre']} ({$it['confianza']}%)");
                    $stats['farmacias_rechazadas']++;
                    continue;
                }

                [$inicio, $fin] = $it['turn_dates'];

                // Crear o recuperar ciudad
                $ciudad = Ciudad::firstOrCreate(['nombre_ciudad' => $it['ciudad']]);

                // Buscar farmacia por nombre + ciudad (evitar duplicados)
                $farmacia = Farmacia::where('nombre', $it['nombre'])
                    ->where('id_ciudad', $ciudad->id_ciudad)
                    ->first();

                if (!$farmacia) {
                    // si no existe, crear
                    $farmacia = Farmacia::create([
                        'nombre' => $it['nombre'],
                        'direccion' => $it['direccion'],
                        'telefono' => $it['telefono'],
                        'id_ciudad' => $ciudad->id_ciudad,
                    ]);
                    $stats['farmacias_nuevas']++;
                    Log::info("🆕 Farmacia CREADA: {$farmacia->nombre}");
                } else {
                    // actualizar datos faltantes
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
                        $stats['farmacias_actualizadas']++;
                        Log::info("🔄 Farmacia ACTUALIZADA: {$farmacia->nombre}");
                    }
                }

                // Geolocalización
                // Solo se ejecuta si la farmacia no tiene latitud o
                // longitud y además dispone de una dirección.
                if ((empty($farmacia->lat) || empty($farmacia->lng)) &&!empty($farmacia->direccion)) {
                    Log::info("📍 Geocodificando farmacia: {$farmacia->nombre}");
                    [$lat, $lng] = $this->geo->buscarVariantes($farmacia->direccion, $ciudad->nombre_ciudad);
                    if ($lat !== null && $lng !== null) {
                        $farmacia->lat = $lat;
                        $farmacia->lng = $lng;
                        $farmacia->save();
                        Log::info("✅ Coordenadas guardadas para {$farmacia->nombre}: {$lat}, {$lng}");

                        // Respetar rate-limit del servicio de geocodificación.
                        sleep(1);
                    } else {
                        Log::warning("⚠️ No se pudieron obtener coordenadas para: {$farmacia->nombre} | {$farmacia->direccion}");
                    }
                }

                // Crear turno (si no existe)
                $turno = Turno::firstOrNew(
                    [
                        'fecha_hora_inicio' => $inicio->toDateTimeString(),
                        'fecha_hora_fin' => $fin->toDateTimeString(),
                        'id_ciudad' => $ciudad->id_ciudad,
                    ],
                    [
                        'nombre_turno' => 'Turno ' . $inicio->format('d/m'),
                    ]
                );

                if (!$turno->exists) {
                    $turno->save();
                    $stats['turnos_nuevos']++;
                    Log::info("✅ Turno CREADO: {$inicio->format('d/m/Y')} - {$fin->format('d/m/Y')}");
                } else {
                    Log::debug("🔎 Turno ENCONTRADO (no creado): {$inicio->format('d/m/Y')} - {$fin->format('d/m/Y')}");
                }

                // Relación farmacia ↔ turno
                $pivot = DB::table('farmacias_turnos')
                    ->where('id_farmacia', $farmacia->id_farmacia)
                    ->where('id_turno', $turno->id_turno)
                    ->first();

                if (!$pivot) {
                    // No existe: insertar todo
                    DB::table('farmacias_turnos')->insert([
                        'id_farmacia' => $farmacia->id_farmacia,
                        'id_turno' => $turno->id_turno,
                        'notas' => $it['notas'] ?? null,
                    ]);
                    $stats['asignaciones_creadas']++;
                    Log::debug("🔗 Asignación creada: {$farmacia->nombre} → turno {$turno->id_turno}");
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('❌ Error guardando datos de turnos: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }

        // Estadísticas finales
        Log::info(
            "📊 Estadísticas finales: " .
            "Farmacias nuevas: {$stats['farmacias_nuevas']}, " .
            "Actualizadas: {$stats['farmacias_actualizadas']}, " .
            "Rechazadas: {$stats['farmacias_rechazadas']}, " .
            "Turnos nuevos: {$stats['turnos_nuevos']}, " .
            "Asignaciones creadas: {$stats['asignaciones_creadas']}"
        );
        return $stats;
    }
}