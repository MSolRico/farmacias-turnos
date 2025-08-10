<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class GeocodeFarmacias extends Command
{
    protected $signature = 'farmacias:geocode';
    protected $description = 'Actualiza las coordenadas de latitud y longitud de las farmacias usando Nominatim';

    public function handle()
    {
        $farmacias = DB::table('farmacias')
            ->whereNull('lat')
            ->orWhereNull('lng')
            ->get();

        foreach ($farmacias as $farmacia) {
            $direccion = $farmacia->direccion . ', Santa Fe, Argentina';
            $url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($direccion);

            $this->info("Buscando: " . $direccion);
            $response = Http::withHeaders([
                'User-Agent' => 'LaravelApp (tu-email@ejemplo.com)'
            ])->get($url);

            $data = $response->json();

            if (!empty($data)) {
                $lat = $data[0]['lat'];
                $lon = $data[0]['lon'];

                DB::table('farmacias')
                    ->where('id_farmacia', $farmacia->id_farmacia)
                    ->update([
                        'lat' => $lat,
                        'lng' => $lon
                    ]);

                $this->info("✔ Coordenadas actualizadas: {$lat}, {$lon}");
            } else {
                $this->warn("✖ No se encontraron coordenadas para: " . $farmacia->direccion);
            }

            sleep(1); // respetar el servicio público
        }

        $this->info("✅ Geocodificación completa.");
    }
}

