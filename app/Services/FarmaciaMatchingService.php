<?php

namespace App\Services;

use App\Models\Farmacia;
use Illuminate\Support\Facades\Cache;

class FarmaciaMatchingService
{
    protected array $farmaciasCache = [];

    // Diccionario de errores OCR comunes (alias/sinónimos)
    protected array $aliases = [
        'mzanza' => 'azanza',
        'abreguezananeness' => 'abregu',
        'tonin' => 'tonini',
        'cufti' => 'curti',
        'ghersi' => 'ghersi',
        'dente' => 'dentesani',
        'queglas' => 'queglas',
        'que glas' => 'queglas',
        'bourdil' => 'bourdin',
        'scuderoea' => 'escudero',
        'scudero' => 'escudero',
        'trucco' => 'trucco',
        'alejan' => 'alejandrosenn',
        'giulioni' => 'giulioni',
        'mai' => 'mai',
        'felo' => 'ferro',
        'scalzzo' => 'scalzo',
        'assinari' => 'assinari',
        'jungs' => 'junges',
        'bertolif' => 'bertolin',
        'col' => 'coll',
        'coli' => 'colucci',
        'pa' => 'pacce',
        'garcia' => 'garcia',
        'gome' => 'gomez',
        'bui' => 'burgi',
        'gimenz' => 'gimenez',
        'pescelti' => 'pescetti',
        'ranzuglia' => 'ranzuglia',
        'salco' => 'sauco',
        'scobar' => 'escobar',
        'ciuz' => 'cruz',
        'de marzo' => 'sanroque',
        // entradas OCR específicas observadas
        'giulioni gorostiaga' => 'giulioni',
        'mai3' => 'mai',
        'felof t' => 'ferro',
        'boudril' => 'bourdin',
        'grhersi' => 'ghersi',
        'ti o eeric' => 'trucco',
        'longenenene' => 'long',
        'bertolifeez' => 'bertolin',
        'pa veverrrzez' => 'pacce',
        'garciaerivad' => 'garcia',
        'buiieeers0' => 'burgi',
        'colieze' => 'colucci',
        'gimenzere' => 'gimenez',
        'pescelti' => 'pescetti',
        'salco' => 'sauco',
        'scobar' => 'escobar',
        // fallbacks / variantes cortas
        'anton' => 'tonini',
        'tonini' => 'tonini'
    ];

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
                $direccionNormalizada = $this->normalizarDireccion($farmacia->direccion ?? '');
                $telefonoNormalizado = $this->normalizarTelefono($farmacia->telefono ?? '');

                $result[$ciudadKey][] = [
                    'nombre'             => $farmacia->nombre,
                    'direccion'          => $farmacia->direccion,
                    'telefono'           => $farmacia->telefono,
                    'nombre_lower'       => strtolower($farmacia->nombre),
                    'nombre_limpio'      => $nombreLimpio,
                    'direccion_limpia'   => $direccionNormalizada,
                    'telefono_limpio'    => $telefonoNormalizado,
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
        $reemplazosOCR = ['1'=>'l','0'=>'o','5'=>'s','7'=>'t'];
        $nombre = strtr($nombre, $reemplazosOCR);

        // Palabras irrelevantes comunes
        $stopwords = [
            'farmacia', 'farmac1a', 'farmacla',
            'drogueria', 'mutual', 'del', 'la', 'el',
            'san', 'sta', 'santa', 'santo',
            'nuestra', 'senora', 'centro'
        ];

        $nombre = str_replace($stopwords, '', $nombre);
        $nombre = str_replace('de nero', '', $nombre);
        $nombre = preg_replace('/[^a-z0-9]/', '', $nombre);

        return trim($nombre);
    }

    /**
     * Normaliza direcciones eliminando caracteres irrelevantes
     */
    private function normalizarDireccion(string $direccion): string
    {
        $direccion = strtolower($direccion);
        $direccion = str_replace(['.','-','/'], ' ', $direccion);
        $direccion = preg_replace('/\s+/', ' ', $direccion);
        $direccion = preg_replace('/[^a-z0-9 ]/', '', $direccion);
        return trim($direccion);
    }

    /**
     * Normaliza teléfono eliminando caracteres no numéricos
     */
    private function normalizarTelefono(string $telefono): string
    {
        return preg_replace('/[^0-9]/', '', $telefono);
    }

    /**
     * Busca coincidencias con heurísticas avanzadas
     */
    public function buscarCoincidencia(string $nombreOCR, string $ciudad, string $direccionOCR = '', string $telefonoOCR = ''): ?array
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
        $direccionOCRLimpia = $this->normalizarDireccion($direccionOCR);
        $telefonoOCRLimpio = $this->normalizarTelefono($telefonoOCR);

        $mejorMatch = null;
        $mejorScore = 0;

        // Check de Alias. Prioridad 100%.
        if (isset($this->aliases[$nombreOCRLimpio])) {
            $nombreTargetLimpio = $this->aliases[$nombreOCRLimpio];
            foreach ($this->farmaciasCache[$ciudadKey] as $farmacia) {
                if ($farmacia['nombre_limpio'] === $nombreTargetLimpio) {
                    return $this->armarRespuesta($farmacia, 100, 'alias', $nombreOCR, $nombreOCRLimpio);
                }
            }
        }

        foreach ($this->farmaciasCache[$ciudadKey] as $farmacia) {

            /**
             * 1) COINCIDENCIA EXACTA LIMPIA
             */
            if ($nombreOCRLimpio === $farmacia['nombre_limpio']) {
                return $this->armarRespuesta($farmacia, 100, 'exacta_nombre', $nombreOCR, $nombreOCRLimpio);
            }

            /**
             * 2) Coincidencia por dirección
             */
            if ($direccionOCRLimpia && $direccionOCRLimpia === $farmacia['direccion_limpia']) {
                $score = 95;
                if ($score > $mejorScore) {
                    $mejorScore = $score;
                    $mejorMatch = $this->armarRespuesta($farmacia, $score, 'direccion', $nombreOCR, $nombreOCRLimpio);
                }
            }

            /**
             * 3) Coincidencia por teléfono
             */
            if ($telefonoOCRLimpio && $telefonoOCRLimpio === $farmacia['telefono_limpio']) {
                $score = 98;
                if ($score > $mejorScore) {
                    $mejorScore = $score;
                    $mejorMatch = $this->armarRespuesta($farmacia, $score, 'telefono', $nombreOCR, $nombreOCRLimpio);
                }
            }

            /**
             * 4) MATCH POR CONTENIDO — con filtro mínimo
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
             * 5) LEVENSHTEIN — ideal para OCR
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
             * 6) similar_text — buena pero costosa
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
