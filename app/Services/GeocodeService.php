<?php

namespace App\Services;

class GeocodeService
{
    /**
     * Intenta obtener coordenadas desde Nominatim.
     * Devuelve: [lat, lon] o [null, null]
     */
    public function getCoordinates(string $direccion): array
    {
        $url = "https://nominatim.openstreetmap.org/search"
             . "?format=json&q=" . urlencode($direccion)
             . "&limit=1";

        $opts = [
            "http" => [
                "header" => "User-Agent: farmacias-turnos-app/1.0\r\n"
            ]
        ];

        $context = stream_context_create($opts);
        $json = @file_get_contents($url, false, $context);

        if (!$json) {
            return [null, null];
        }

        $data = json_decode($json, true);

        if (empty($data)) {
            return [null, null];
        }

        return [
            $data[0]['lat'] ?? null,
            $data[0]['lon'] ?? null
        ];
    }

    /**
     * Intenta varias variantes de una dirección.
     */
    public function buscarVariantes(string $direccion, string $ciudad): array
    {
        $base = trim("$direccion, $ciudad, Argentina");

        // 1) Búsqueda normal
        [$lat, $lng] = $this->getCoordinates($base);
        if ($lat && $lng) return [$lat, $lng];

        // 2) Sin número
        if (preg_match('/^(.*?)[\s\-\,]+(\d{1,5})$/u', $direccion, $m)) {
            $sinNumero = $m[1];
            [$lat, $lng] = $this->getCoordinates("$sinNumero, $ciudad, Argentina");
            if ($lat && $lng) return [$lat, $lng];
        }

        // 3) Sin tildes
        $sinTildes = iconv('UTF-8', 'ASCII//TRANSLIT', $direccion);
        [$lat, $lng] = $this->getCoordinates("$sinTildes, $ciudad, Argentina");
        if ($lat && $lng) return [$lat, $lng];

        // 4) Sin calle larga (primeras 2 palabras)
        $parts = explode(' ', $direccion);
        if (count($parts) > 2) {
            $short = implode(' ', array_slice($parts, 0, 2));
            [$lat, $lng] = $this->getCoordinates("$short, $ciudad, Argentina");
            if ($lat && $lng) return [$lat, $lng];
        }

        return [null, null];
    }
}
