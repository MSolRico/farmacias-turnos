<?php

namespace App\Services;

use App\Models\Farmacia;
use Illuminate\Support\Facades\Cache;

class FarmaciaMatchingService
{
    protected array $farmaciasCache = [];

    public function __construct()
    {
        $this->cargarFarmacias();
    }

    /**
     * Carga farmacias en cache con limpieza y pre-normalización
     */
    private function cargarFarmacias(): void
    {
        $this->farmaciasCache = Cache::remember('farmacias_conocidas', 3600, function () {
            $farmacias = Farmacia::with('ciudad')->get();
            $result = [];
            
            foreach ($farmacias as $farmacia) {
                $ciudadKey = $this->limpiarNombre($farmacia->ciudad->nombre_ciudad);

                if (!isset($result[$ciudadKey])) {
                    $result[$ciudadKey] = [];
                }

                $nombreLimpio = $this->limpiarNombre($farmacia->nombre);

                $result[$ciudadKey][] = [
                    'nombre'         => $farmacia->nombre,
                    'direccion'      => $farmacia->direccion,
                    'telefono'       => $farmacia->telefono,
                    'nombre_lower'   => strtolower($farmacia->nombre),
                    'nombre_limpio'  => $nombreLimpio,
                ];
            }

            return $result;
        });
    }

    /**
     * Normalización robusta para OCR
     */
    private function limpiarNombre(string $nombre): string
    {
        $nombre = strtolower($nombre);

        // Reemplazo de caracteres comunes en OCR
        $nombre = str_replace(
            ['á','é','í','ó','ú','ñ','ä','ë','ï','ö','ü','ý','ÿ'],
            ['a','e','i','o','u','n','a','e','i','o','u','y','y'],
            $nombre
        );

        // Reemplazos típicos de OCR
        $reemplazosOCR = [
            '1' => 'l',
            '0' => 'o',
            '5' => 's',
            '7' => 't',
        ];

        $nombre = strtr($nombre, $reemplazosOCR);

        // Palabras irrelevantes comunes
        $stopwords = [
            'farmacia', 'farmac1a', 'farmacla',
            'drogueria', 'mutual', 'del', 'la', 'el',
            'san', 'sta', 'santa', 'santo',
            'nuestra', 'senora', 'centro'
        ];

        $nombre = str_replace($stopwords, '', $nombre);

        // Quitar todo lo que no sea letra o número
        $nombre = preg_replace('/[^a-z0-9]/', '', $nombre);

        return trim($nombre);
    }

    /**
     * Busca coincidencias con heurísticas avanzadas
     */
    public function buscarCoincidencia(string $nombreOCR, string $ciudad): ?array
    {
        if (!$nombreOCR || !$ciudad) {
            return null;
        }

        $ciudadKey = $this->limpiarNombre($ciudad);

        if (!isset($this->farmaciasCache[$ciudadKey])) {
            return null;
        }

        $nombreOCRLower = strtolower($nombreOCR);
        $nombreOCRLimpio = $this->limpiarNombre($nombreOCR);

        $mejorMatch = null;
        $mejorScore = 0;

        foreach ($this->farmaciasCache[$ciudadKey] as $farmacia) {

            /**
             * 1) COINCIDENCIA EXACTA LIMPIA
             */
            if ($nombreOCRLimpio === $farmacia['nombre_limpio']) {
                return $this->armarRespuesta($farmacia, 100, 'exacta', $nombreOCR, $nombreOCRLimpio);
            }

            /**
             * 2) MATCH POR CONTENIDO — con filtro mínimo
             */
            if (strlen($nombreOCRLower) >= 5 && strlen($farmacia['nombre_lower']) >= 5) {
                if (
                    stripos($nombreOCRLower, $farmacia['nombre_lower']) !== false ||
                    stripos($farmacia['nombre_lower'], $nombreOCRLower) !== false
                ) {
                    $score = 92;

                    if ($score > $mejorScore) {
                        $mejorScore = $score;
                        $mejorMatch = $this->armarRespuesta($farmacia, $score, 'contenido', $nombreOCR, $nombreOCRLimpio);
                    }
                }
            }

            /**
             * 3) LEVENSHTEIN — ideal para OCR
             */
            $maxLen = max(strlen($nombreOCRLimpio), strlen($farmacia['nombre_limpio']));
            if ($maxLen > 0) {
                $distancia = levenshtein($nombreOCRLimpio, $farmacia['nombre_limpio']);
                $similitud = (1 - ($distancia / $maxLen)) * 100;

                if ($similitud > 70 && $similitud > $mejorScore) {
                    $mejorScore = $similitud;
                    $mejorMatch = $this->armarRespuesta($farmacia, round($similitud), 'levenshtein', $nombreOCR, $nombreOCRLimpio);
                }
            }

            /**
             * 4) similar_text — buena pero costosa
             */
            similar_text($nombreOCRLower, $farmacia['nombre_lower'], $percent);

            if ($percent > 70 && $percent > $mejorScore) {
                $mejorScore = $percent;
                $mejorMatch = $this->armarRespuesta($farmacia, round($percent), 'similar_text', $nombreOCR, $nombreOCRLimpio);
            }
        }

        return $mejorMatch;
    }

    /**
     * Arma la respuesta final unificada
     */
    private function armarRespuesta(array $farmacia, int $score, string $metodo, string $ocrEntrada, string $ocrLimpio): array
    {
        return [
            'nombre_correcto'      => $farmacia['nombre'],
            'direccion_correcta'   => $farmacia['direccion'],
            'telefono_correcto'    => $farmacia['telefono'],

            'confianza'            => $score,
            'metodo'               => $metodo,

            'entrada_ocr'          => $ocrEntrada,
            'entrada_ocr_limpia'   => $ocrLimpio,
        ];
    }

    /**
     * Limpia cache
     */
    public function limpiarCache(): void
    {
        Cache::forget('farmacias_conocidas');
        $this->cargarFarmacias();
    }
}
