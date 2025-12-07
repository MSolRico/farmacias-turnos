<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\GeocodeService;
use App\Helpers\OcrCleaner;

class GeocodeFarmacias extends Command
{
    protected $signature = 'farmacias:geocode';
    protected $description = 'Geocodifica farmacias usando GeocodeService y limpieza avanzada de direcciones OCR';

    public function handle(GeocodeService $geo)
    {
        $farmacias = DB::table('farmacias')
            ->whereNull('lat')
            ->orWhereNull('lng')
            ->orderBy('id_farmacia')
            ->get();

        if ($farmacias->isEmpty()) {
            $this->info("No hay farmacias pendientes de geocodificación.");
            return;
        }

        foreach ($farmacias as $farmacia) {

            $this->line("----------------------------------------------------");
            $this->info("📍 Procesando ID: {$farmacia->id_farmacia}");
            $this->info("Dirección original: {$farmacia->direccion}");

            // 1) Normalización OCR
            $direccion = OcrCleaner::normalizeAddress($farmacia->direccion);

            // 2) Corrección de nombres de calle
            $direccion = OcrCleaner::fixStreetNames($direccion);

            // 3) Separar dirección de notas tipo “Local 2”
            [$direccionBase, $nota] = OcrCleaner::splitAddressNotes($direccion);

            // 4) Trim profundo
            $direccionBase = trim($direccionBase);

            $this->info("➡ Dirección normalizada: $direccionBase" . ($nota ? " (nota: $nota)" : ""));

            // Evitar enviar basura vacía al geocoder
            if (strlen($direccionBase) < 3) {
                $this->warn("⚠ Dirección demasiado corta o inválida, saltando.");
                continue;
            }

            // Ciudad fija porque en DB no está cargada → si querés se puede leer desde tabla ciudad
            $ciudad = 'Santa Fe';

            // 5) Geocodificación con variantes
            [$lat, $lng] = $geo->buscarVariantes($direccionBase, $ciudad);

            if ($lat && $lng) {

                DB::table('farmacias')
                    ->where('id_farmacia', $farmacia->id_farmacia)
                    ->update([
                        'lat' => $lat,
                        'lng' => $lng
                    ]);

                $this->info("✔ Coordenadas encontradas: LAT $lat | LNG $lng");

            } else {
                $this->warn("✖ No se encontraron coordenadas para: {$farmacia->direccion}");
            }

            // Evitar bloquear el rate limit de Nominatim
            sleep(1);
        }

        $this->info("🎉 Geocodificación finalizada.");
    }
}
