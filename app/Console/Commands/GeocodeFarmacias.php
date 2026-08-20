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

        foreach ($farmacias as $farmacia) {

            $this->line('----------------------------------------------------');
            $this->info("📍 Procesando ID: {$farmacia->id_farmacia}");
            $this->info("Farmacia: {$farmacia->nombre}");
            $this->info("Dirección: {$farmacia->direccion}");

            // La dirección ya viene normalizada desde el pipeline actual.
            $direccion = trim((string) $farmacia->direccion);

            // La ciudad se obtiene desde la relación con la tabla ciudades.
            $ciudad = $farmacia->nombre_ciudad;

            if (!$ciudad) {
                $this->warn(
                    "⚠ La farmacia ID {$farmacia->id_farmacia} no tiene una ciudad asignada. Se omite."
                );
                continue;
            }

            $this->info("Ciudad: {$ciudad}");

            // Evitar enviar una dirección vacía o demasiado corta a Nominatim.
            if (mb_strlen($direccion) < 3) {
                $this->warn(
                    "⚠ Dirección demasiado corta o inválida, se omite."
                );
                continue;
            }

            // Geocodificación utilizando las variantes definidas en GeocodeService.
            [$lat, $lng] = $geo->buscarVariantes($direccion, $ciudad);

            if ($lat !== null && $lng !== null) {

                DB::table('farmacias')
                    ->where('id_farmacia', $farmacia->id_farmacia)
                    ->update([
                        'lat' => $lat,
                        'lng' => $lng,
                    ]);

                $this->info("✔ Coordenadas encontradas: LAT {$lat} | LNG {$lng}");

            } else {
                $this->warn("✖ No se encontraron coordenadas para: {$farmacia->direccion}, {$ciudad}");
            }

            // Respetar el rate limit de Nominatim.
            sleep(1);
        }

        $this->info("🎉 Geocodificación finalizada.");
    }
}
