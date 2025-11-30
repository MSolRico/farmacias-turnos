<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\GeocodeService;
use App\Helpers\OcrCleaner;

class GeocodeFarmacias extends Command
{
    protected $signature = 'farmacias:geocode';
    protected $description = 'Geocodifica farmacias usando GeocodeService';

    public function handle(GeocodeService $geo)
    {
        $farmacias = DB::table('farmacias')
            ->whereNull('lat')
            ->orWhereNull('lng')
            ->get();

        foreach ($farmacias as $farmacia) {


            $direccionLimpia = OcrCleaner::fixStreetNames($farmacia->direccion);

            $this->info("Buscando: $direccionLimpia");

            [$lat, $lng] = $geo->buscarVariantes($direccionLimpia, 'Santa Fe');

            if ($lat && $lng) {
                DB::table('farmacias')
                    ->where('id_farmacia', $farmacia->id_farmacia)
                    ->update([
                        'lat' => $lat,
                        'lng' => $lng
                    ]);

                $this->info("✔ Coordenadas actualizadas: $lat, $lng");
            } else {
                $this->warn("✖ No se encontraron coordenadas para: {$farmacia->direccion}");
            }

            sleep(1);
        }

        $this->info("✅ Geocodificación finalizada.");
    }
}
