<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\GeocodeService;

class GeocodeFarmacias extends Command
{
    protected $signature = 'farmacias:geocode';

    protected $description = 'Geocodifica las farmacias que no tienen coordenadas usando GeocodeService';

    public function handle(GeocodeService $geo)
    {
        $farmacias = DB::table('farmacias')
            ->leftJoin(
                'ciudades',
                'farmacias.id_ciudad',
                '=',
                'ciudades.id_ciudad'
            )
            ->where(function ($query) {
                $query->whereNull('farmacias.lat')
                    ->orWhereNull('farmacias.lng');
            })
            ->orderBy('farmacias.id_farmacia')
            ->select(
                'farmacias.*',
                'ciudades.nombre_ciudad'
            )
            ->get();

        if ($farmacias->isEmpty()) {
            $this->info("No hay farmacias pendientes de geocodificación.");
            return;
        }

        $contadores = ['georef' => 0, 'nominatim' => 0, 'manual' => 0, 'aproximado' => 0, 'fallido' => 0];

        foreach ($farmacias as $farmacia) {

            $this->line('----------------------------------------------------');
            $this->info("📍 Procesando ID: {$farmacia->id_farmacia}");
            $this->info("Farmacia: {$farmacia->nombre}");
            $this->info("Dirección: {$farmacia->direccion}");

            $direccion = trim((string) $farmacia->direccion);
            $ciudad = $farmacia->nombre_ciudad;

            if (!$ciudad) {
                $this->warn(
                    "⚠ La farmacia ID {$farmacia->id_farmacia} no tiene una ciudad asignada. Se omite."
                );
                continue;
            }

            $this->info("Ciudad: {$ciudad}");

            if (mb_strlen($direccion) < 3) {
                $this->warn(
                    "⚠ Dirección demasiado corta o inválida, se omite."
                );
                continue;
            }

            [$lat, $lng, $aproximado, $fuente] = $geo->buscarVariantes($direccion, $ciudad);

            if ($lat !== null && $lng !== null) {

                DB::table('farmacias')
                    ->where('id_farmacia', $farmacia->id_farmacia)
                    ->update([
                        'lat' => $lat,
                        'lng' => $lng,
                    ]);

                if ($aproximado) {
                    $contadores['aproximado']++;
                    $this->warn("〰 Coordenadas APROXIMADAS (nivel calle, vía {$fuente}): LAT {$lat} | LNG {$lng}");
                } else {
                    $contadores[$fuente]++;
                    $this->info("✔ Coordenadas encontradas (vía {$fuente}): LAT {$lat} | LNG {$lng}");
                }

            } else {
                $contadores['fallido']++;
                $this->warn("✖ No se encontraron coordenadas para: {$farmacia->direccion}, {$ciudad}");
            }

            // Georef no publica un rate limit tan estricto como Nominatim,
            // pero se mantiene el sleep para no saturar ninguna de las
            // dos APIs (solo se llama a Nominatim si Georef falló).
            sleep(1);
        }

        $this->line('----------------------------------------------------');
        $this->info("🎉 Geocodificación finalizada.");
        $this->info("   Georef (exacto): {$contadores['georef']}");
        $this->info("   Nominatim (exacto): {$contadores['nominatim']}");
        $this->info("   Manual (cargado a mano): {$contadores['manual']}");
        $this->warn("   Aproximado (nivel calle): {$contadores['aproximado']}");
        $this->warn("   Sin coordenadas: {$contadores['fallido']}");
    }
}
