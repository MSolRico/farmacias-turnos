<?php

namespace App\Services;

use App\Models\Farmacia;
use Illuminate\Support\Facades\Cache;

/**
 * Se eliminó el diccionario $aliases (~50 entradas tipo 'mzanza' => 'azanza',
 * 'boudril' => 'bourdin', etc). Ese diccionario existía para corregir
 * errores ESPECÍFICOS de Tesseract sobre esta fuente/diseño de afiche en
 * particular — cada vez que Tesseract inventaba un error nuevo, había que
 * agregar una entrada a mano. Con Gemini Vision leyendo la imagen
 * directamente, los nombres llegan casi siempre correctos, y el matching
 * por Levenshtein/similar_text de más abajo ya cubre variaciones menores
 * (tildes, mayúsculas, algún carácter de más/menos) sin necesitar una
 * lista mantenida a mano.
 *
 * Si en el futuro aparece un error recurrente que el fuzzy matching no
 * resuelve, es mejor ajustar el prompt de GeminiVisionOcrService que
 * volver a un diccionario de parches.
 */
class FarmaciaMatchingService
{
    protected array $farmaciasCache = [];

    public function __construct()
    {
        // Las farmacias se cargan de forma diferida en buscarCoincidencia().
    }

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

                $result[$ciudadKey][] = [
                    'nombre'           => $farmacia->nombre,
                    'direccion'        => $farmacia->direccion,
                    'telefono'         => $farmacia->telefono,
                    'nombre_lower'     => strtolower($farmacia->nombre),
                    'nombre_limpio'    => $this->limpiarNombre($farmacia->nombre),
                    'direccion_limpia' => $this->normalizarDireccion($farmacia->direccion ?? ''),
                    'telefono_limpio'  => $this->normalizarTelefono($farmacia->telefono ?? ''),
                ];
            }

            return $result;
        });
    }

    private function limpiarNombre(string $nombre): string
    {
        $nombre = strtolower($nombre);

        $nombre = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ä', 'ë', 'ï', 'ö', 'ü', 'ý', 'ÿ'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'y', 'y'],
            $nombre
        );

        $stopwords = [
            'farmacia', 'drogueria', 'mutual', 'del', 'la', 'el',
            'san', 'sta', 'santa', 'santo',
            'nuestra', 'senora', 'centro',
        ];

        $nombre = str_replace($stopwords, '', $nombre);
        $nombre = preg_replace('/[^a-z0-9]/', '', $nombre);

        return trim($nombre);
    }

    private function normalizarDireccion(string $direccion): string
    {
        $direccion = strtolower($direccion);
        $direccion = str_replace(['.', '-', '/'], ' ', $direccion);
        $direccion = preg_replace('/\s+/', ' ', $direccion);
        $direccion = preg_replace('/[^a-z0-9 ]/', '', $direccion);
        return trim($direccion);
    }

    private function normalizarTelefono(string $telefono): string
    {
        return preg_replace('/[^0-9]/', '', $telefono);
    }

    /**
     * Busca coincidencias con heurísticas: exacta, dirección, teléfono,
     * contenido, Levenshtein y similar_text. Ya no hay chequeo de alias.
     */
    public function buscarCoincidencia(string $nombreOCR, string $ciudad, string $direccionOCR = '', string $telefonoOCR = ''): ?array
    {
        if (!$nombreOCR || !$ciudad) {
            return null;
        }

        /*
         * Carga diferida:
         * Las farmacias se consultan solamente cuando realmente
         * necesitamos realizar un matching.
         */
        if (empty($this->farmaciasCache)) {
            $this->cargarFarmacias();
        }

        $ciudadKey = $this->limpiarNombre($ciudad);

        if (!isset($this->farmaciasCache[$ciudadKey])) {
            return null;
        }

        $nombreOCRLower = strtolower($nombreOCR);
        $nombreOCRLimpio = $this->limpiarNombre($nombreOCR);
        $direccionOCRLimpia = $this->normalizarDireccion($direccionOCR);
        $telefonoOCRLimpio = $this->normalizarTelefono($telefonoOCR);

        $mejorMatch = null;
        $mejorScore = 0;

        foreach ($this->farmaciasCache[$ciudadKey] as $farmacia) {

            // 1) Coincidencia exacta de nombre limpio
            if ($nombreOCRLimpio === $farmacia['nombre_limpio']) {
                return $this->armarRespuesta($farmacia, 100, 'exacta_nombre', $nombreOCR, $nombreOCRLimpio);
            }

            // 2) Coincidencia por dirección
            if ($direccionOCRLimpia && $direccionOCRLimpia === $farmacia['direccion_limpia']) {
                $score = 95;
                if ($score > $mejorScore) {
                    $mejorScore = $score;
                    $mejorMatch = $this->armarRespuesta($farmacia, $score, 'direccion', $nombreOCR, $nombreOCRLimpio);
                }
            }

            // 3) Coincidencia por teléfono
            if ($telefonoOCRLimpio && $telefonoOCRLimpio === $farmacia['telefono_limpio']) {
                $score = 98;
                if ($score > $mejorScore) {
                    $mejorScore = $score;
                    $mejorMatch = $this->armarRespuesta($farmacia, $score, 'telefono', $nombreOCR, $nombreOCRLimpio);
                }
            }

            // 4) Match por contenido (substring), con largo mínimo
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

            // 5) Levenshtein
            $maxLen = max(strlen($nombreOCRLimpio), strlen($farmacia['nombre_limpio']));
            if ($maxLen > 0) {
                $distancia = levenshtein($nombreOCRLimpio, $farmacia['nombre_limpio']);
                $similitud = (1 - ($distancia / $maxLen)) * 100;

                if ($similitud > 70 && $similitud > $mejorScore) {
                    $mejorScore = $similitud;
                    $mejorMatch = $this->armarRespuesta($farmacia, (int) round($similitud), 'levenshtein', $nombreOCR, $nombreOCRLimpio);
                }
            }

            // 6) similar_text
            similar_text($nombreOCRLower, $farmacia['nombre_lower'], $percent);
            if ($percent > 70 && $percent > $mejorScore) {
                $mejorScore = $percent;
                $mejorMatch = $this->armarRespuesta($farmacia, (int) round($percent), 'similar_text', $nombreOCR, $nombreOCRLimpio);
            }
        }

        return $mejorMatch;
    }

    private function armarRespuesta(array $farmacia, int $score, string $metodo, string $ocrEntrada, string $ocrLimpio): array
    {
        return [
            'nombre_correcto'    => $farmacia['nombre'],
            'direccion_correcta' => $farmacia['direccion'],
            'telefono_correcto'  => $farmacia['telefono'],
            'confianza'          => $score,
            'metodo'             => $metodo,
            'entrada_ocr'        => $ocrEntrada,
            'entrada_ocr_limpia' => $ocrLimpio,
        ];
    }

    public function limpiarCache(): void
    {
        Cache::forget('farmacias_conocidas');
        $this->farmaciasCache = [];
    }
}