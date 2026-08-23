<?php

namespace App\Services;

class GeocodeService
{
    private const PROVINCIA = 'Santa Fe';
    private const DEPARTAMENTO = 'La Capital';

    private array $bbox = [
        'Santa Fe' => ['viewbox' => '-60.77,-31.56,-60.65,-31.72'],
        'Santo Tomé' => ['viewbox' => '-60.82,-31.63,-60.72,-31.72'],
    ];

    private const COORDENADAS_MANUALES = [
        'Av. Gorriti 3751-Loc.2' => ['-31.587900664788496', '-60.70532525102302'],
        'Barrio El Pozo M.8 Vda. 43' => ['-31.637374586176207', '-60.65960228634979'],
        'Paseo Las Acacias - Local 15' => ['-31.62928421648066', '-60.77459987736928'],
    ];

    /**
     * Direcciones que en realidad no son "calle + altura" (barrios
     * informales con nomenclatura de manzana/vivienda, etc.) y que se
     * resuelven mejor reemplazándolas por una dirección de referencia
     * real y cercana. Clave: dirección EXACTA original.
     */
    private const DIRECCION_REEMPLAZO = [
    ];

    /**
     * Lugares sin altura real (centros comerciales, paseos, etc.) que hay
     * que buscar por nombre de lugar en vez de como una dirección con
     * número. Clave: dirección EXACTA original, valor: término de
     * búsqueda a usar en su lugar.
     */
    private const BUSQUEDA_POR_NOMBRE = [
    ];

    /**
     * Correcciones ESPECÍFICAS de calles de Santa Fe/Santo Tomé cuya
     * abreviatura no se puede resolver de forma genérica (o donde una
     * expansión genérica daría un resultado incorrecto). Regex (case
     * insensitive) => reemplazo. Se aplican ANTES que las abreviaturas
     * genéricas y antes de cualquier intento de geocodificación.
     */
    private const CORRECCIONES_ESPECIFICAS = [
        '/\bAv\.?\s*A\.\s*del\s+Valle\b/iu' => 'Avenida Aristóbulo del Valle',
        '/\bFdo\.\s*Zuvir[ií]a\b/iu' => 'Facundo Zuviría',
        '/\bF\.\s*Zuvir[ií]a\b/iu' => 'Facundo Zuviría',
        '/\bPte\.?\s*Roca\b/iu' => 'Presidente Roca',
        '/\bSalv\.\s*del\s+Carril\b/iu' => 'Salvador del Carril',
        '/\bL\.\s*y\s+Planes\b/iu' => 'López y Planes',
        '/\bG\.\s*Paz\b/iu' => 'General Paz',
        '/\bP\.\s*Genesio\b/iu' => 'Padre Genesio',
        '/\bM\.\s*Candioti\b/iu' => 'Mariano Candioti',
    ];

    // Abreviaturas genéricas de tipo de calle: seguras de expandir en
    // cualquier contexto porque no son ambiguas con nombres propios.
    private const ABREVIATURAS_GENERICAS = [
        'Av.' => 'Avenida',
        'Bv.' => 'Bulevar',
        'Gral.' => 'General',
        'Stgo.' => 'Santiago',
        'Sto.' => 'Santo',
    ];

    /**
     * @return array{0: string|null, 1: string|null, 2: bool, 3: string}
     *   [lat, lng, esAproximado, fuente]
     */
    public function buscarVariantes(string $direccion, string $ciudad): array
    {
        $direccionOriginal = trim($direccion);
        $ciudad = $this->normalizeCity($ciudad);

        // Excepción 1: coordenada cargada a mano, no se llama a ninguna API.
        if (isset(self::COORDENADAS_MANUALES[$direccionOriginal])) {
            [$lat, $lng] = self::COORDENADAS_MANUALES[$direccionOriginal];
            return [$lat, $lng, false, 'manual'];
        }

        // Excepción 2: la dirección real no es "calle + altura" (barrio
        // informal), se reemplaza por una dirección de referencia real
        // antes de seguir el flujo normal.
        if (isset(self::DIRECCION_REEMPLAZO[$direccionOriginal])) {
            $direccion = self::DIRECCION_REEMPLAZO[$direccionOriginal];
        }

        // Excepción 3: lugares sin altura (centros comerciales, paseos):
        // se busca por nombre de lugar directamente en Nominatim, sin
        // exigir número de puerta, y se corta acá (Georef no tiene
        // sentido para esto porque siempre exige una altura numérica).
        if (isset(self::BUSQUEDA_POR_NOMBRE[$direccionOriginal])) {
            $termino = self::BUSQUEDA_POR_NOMBRE[$direccionOriginal];
            [$lat, $lng] = $this->getCoordinates($termino, $ciudad, exigirNumero: false);
            if ($lat !== null && $lng !== null) {
                return [$lat, $lng, true, 'nominatim'];
            }
            return [null, null, false, ''];
        }

        // Paso 0: aplicar correcciones específicas conocidas ANTES que
        // nada. Esto es lo que arregla "A. del Valle" y "Fdo. Zuviría".
        $direccion = $this->aplicarCorreccionesEspecificas($direccion);

        $variantesDireccion = array_unique(array_filter([
            $direccion,
            strtr($direccion, self::ABREVIATURAS_GENERICAS),
            $this->limpiarSufijosSecundarios($direccion),
            $this->quitarPrefijoAvenida($direccion),
        ]));

        // ---- 1. Georef con cada variante ----
        foreach ($variantesDireccion as $variante) {
            [$lat, $lng] = $this->geocodificarConGeoref($variante, $ciudad);
            if ($lat !== null && $lng !== null) {
                return [$lat, $lng, false, 'georef'];
            }
        }

        // ---- 2. Nominatim con cada variante, exigiendo número ----
        foreach ($variantesDireccion as $variante) {
            [$lat, $lng] = $this->getCoordinates($variante, $ciudad, exigirNumero: true);
            if ($lat !== null && $lng !== null) {
                return [$lat, $lng, false, 'nominatim'];
            }
        }

        $sinTildes = iconv('UTF-8', 'ASCII//TRANSLIT', $direccion);
        [$lat, $lng] = $this->getCoordinates($sinTildes, $ciudad, exigirNumero: true);
        if ($lat !== null && $lng !== null) {
            return [$lat, $lng, false, 'nominatim'];
        }

        // ---- 3. Último recurso: nivel calle (aproximado) ----
        if (preg_match('/^(.*?)[\s\-\,]+(\d{1,5})$/u', $direccion, $m)) {
            [$lat, $lng] = $this->getCoordinates($m[1], $ciudad, exigirNumero: false);
            if ($lat !== null && $lng !== null) {
                return [$lat, $lng, true, 'nominatim'];
            }
        }

        $parts = explode(' ', $direccion);
        if (count($parts) > 2) {
            $direccionReducida = implode(' ', array_slice($parts, 0, 2));
            [$lat, $lng] = $this->getCoordinates($direccionReducida, $ciudad, exigirNumero: false);
            if ($lat !== null && $lng !== null) {
                return [$lat, $lng, true, 'nominatim'];
            }
        }

        return [null, null, false, ''];
    }

    private function aplicarCorreccionesEspecificas(string $direccion): string
    {
        foreach (self::CORRECCIONES_ESPECIFICAS as $patron => $reemplazo) {
            $direccion = preg_replace($patron, $reemplazo, $direccion);
        }
        return $direccion;
    }

    /**
     * Quita sufijos tipo "-Loc.2", "Local 15", "Dto. 3", "- Loc. 5" que,
     * pegados a la altura, hacen que Georef/Nominatim no reconozcan el
     * número como altura válida (ej. "Av. Gorriti 3751-Loc.2" fallaba
     * porque el parser leía "3751-Loc.2" como si fuera un rango).
     */
    private function limpiarSufijosSecundarios(string $direccion): string
    {
        $limpio = preg_replace(
            '/\s*[\-\/]?\s*(loc\.?|local|dto\.?|depto\.?|piso)\s*\.?\s*\d*\s*$/iu',
            '',
            $direccion
        );
        return trim($limpio);
    }

    /**
     * Algunas calles están registradas SIN categoría "Avenida" en la base
     * oficial (ej. "Urquiza" a secas), y anteponerle "Av." hace que el
     * parser de Georef no la reconozca aunque coloquialmente se le diga
     * "avenida". Esta variante prueba sacando el prefijo de tipo de calle.
     */
    private function quitarPrefijoAvenida(string $direccion): string
    {
        return trim(preg_replace('/^(Av\.?|Avenida|Bv\.?|Bulevar)\s+/iu', '', $direccion));
    }

    private function geocodificarConGeoref(string $direccion, string $ciudad): array
    {
        $params = http_build_query([
            'direccion' => $direccion,
            'provincia' => self::PROVINCIA,
            'departamento' => self::DEPARTAMENTO,
            'localidad_censal' => $ciudad,
            'max' => 1,
        ]);

        $url = "https://apis.datos.gob.ar/georef/api/direcciones?{$params}";

        $opts = [
            "http" => [
                "header" => "User-Agent: farmacias-turnos-app/1.0 (contacto@tudominio.com)\r\n",
                "timeout" => 15,
            ]
        ];

        $context = stream_context_create($opts);
        $json = @file_get_contents($url, false, $context);

        if (!$json) {
            return [null, null];
        }

        $data = json_decode($json, true);
        $resultado = $data['direcciones'][0] ?? null;

        if (!$resultado) {
            return [null, null];
        }

        $alturaReconocida = $resultado['altura']['valor'] ?? null;
        $ubicacion = $resultado['ubicacion'] ?? null;

        if ($alturaReconocida === null || empty($ubicacion['lat']) || empty($ubicacion['lon'])) {
            return [null, null];
        }

        return [(string) $ubicacion['lat'], (string) $ubicacion['lon']];
    }

    private function getCoordinates(string $direccion, string $ciudad, bool $exigirNumero = false): array
    {
        $city = $this->normalizeCity($ciudad);
        $viewbox = $this->bbox[$city]['viewbox'] ?? $this->bbox['Santa Fe']['viewbox'];

        $query = urlencode("$direccion, $city, Argentina");
        $url = "https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&q={$query}&limit=1&bounded=1&viewbox={$viewbox}";

        $opts = [
            "http" => [
                "header" => "User-Agent: farmacias-turnos-app/1.0 (contacto@tudominio.com)\r\n"
            ]
        ];

        $context = stream_context_create($opts);
        $json = @file_get_contents($url, false, $context);

        if (!$json) return [null, null];

        $data = json_decode($json, true);

        if (empty($data)) {
            return [null, null];
        }

        $esNivelCalle = ($data[0]['class'] ?? '') === 'highway'
            || empty($data[0]['address']['house_number'] ?? null);

        if ($exigirNumero && $esNivelCalle) {
            return [null, null];
        }

        return [$data[0]['lat'] ?? null, $data[0]['lon'] ?? null];
    }

    private function normalizeCity(string $c): string
    {
        $c = trim(mb_strtoupper($c, 'UTF-8'));

        return str_contains($c, 'SANTO TOM')
            ? 'Santo Tomé'
            : 'Santa Fe';
    }

}