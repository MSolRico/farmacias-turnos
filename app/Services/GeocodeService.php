<?php

namespace App\Services;

class GeocodeService
{
    private array $bbox = [
        'Santa Fe' => ['viewbox' => '-60.77,-31.56,-60.65,-31.72'],
        'Santo Tomé' => ['viewbox' => '-60.80,-31.67,-60.73,-31.72']
    ];

    public function getCoordinates(string $direccion, string $ciudad): array
    {
        $city = $this->normalizeCity($ciudad);
        $viewbox = $this->bbox[$city]['viewbox'] ?? $this->bbox['Santa Fe']['viewbox'];

        $query = urlencode("$direccion, $city, Argentina");
        $url = "https://nominatim.openstreetmap.org/search?format=json&q={$query}&limit=1&bounded=1&viewbox={$viewbox}";

        $opts = [
            "http" => [
                "header" => "User-Agent: farmacias-turnos-app/1.0\r\n"
            ]
        ];

        $context = stream_context_create($opts);
        $json = @file_get_contents($url, false, $context);

        if (!$json) return [null, null];

        $data = json_decode($json, true);

        if (empty($data) || !$this->validateReturnedCity($data[0]['display_name'] ?? '', $city)) {
            return [null, null];
        }

        return [$data[0]['lat'] ?? null, $data[0]['lon'] ?? null];
    }

    public function buscarVariantes(string $direccion, string $ciudad): array
    {
        $ciudad = $this->normalizeCity($ciudad);

        // 1. Dirección completa.
        [$lat, $lng] = $this->getCoordinates($direccion, $ciudad);

        if ($lat !== null && $lng !== null) {
            return [$lat, $lng];
        }

        // 2. Solo la calle, eliminando el número.
        if (preg_match('/^(.*?)[\s\-\,]+(\d{1,5})$/u', $direccion, $m)) {
            [$lat, $lng] = $this->getCoordinates($m[1], $ciudad);

            if ($lat !== null && $lng !== null) {
                return [$lat, $lng];
            }
        }

        // 3. Dirección sin tildes.
        $sinTildes = iconv('UTF-8', 'ASCII//TRANSLIT', $direccion);

        [$lat, $lng] = $this->getCoordinates($sinTildes, $ciudad);

        if ($lat !== null && $lng !== null) {
            return [$lat, $lng];
        }

        // 4. Primeros dos componentes de la dirección.
        $parts = explode(' ', $direccion);

        if (count($parts) > 2) {
            $direccionReducida = implode(' ', array_slice($parts, 0, 2));

            [$lat, $lng] = $this->getCoordinates($direccionReducida, $ciudad);

            if ($lat !== null && $lng !== null) {
                return [$lat, $lng];
            }
        }

        return [null, null];
    }

    private function normalizeCity(string $c): string
    {
        $c = trim(mb_strtoupper($c, 'UTF-8'));

        return str_contains($c, 'SANTO TOM')
            ? 'Santo Tomé'
            : 'Santa Fe';
    }

    private function validateReturnedCity(string $displayName, string $ciudad): bool 
    {
        $display = mb_strtoupper($displayName, 'UTF-8');
        $ciudad = mb_strtoupper($ciudad, 'UTF-8');

        if ($ciudad === 'SANTO TOMÉ') {
            return str_contains($display, 'SANTO TOME');
        }

        return str_contains($display, 'SANTA FE');
    }
}
