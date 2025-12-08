<?php

namespace App\Services;

use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\DB;
use App\Services\GeocodeService;
use App\Models\Farmacia;
use App\Models\Turno;
use App\Models\Ciudad;
use Carbon\Carbon;
use App\Helpers\OcrCleaner;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class OcrFarmaciasService
{
    protected GeocodeService $geo;
    protected $farmaciaMatching;

    // Patrones de nombres válidos
    private array $nombresConocidos = [
        // Santa Fe
        'Adrián Carrizo', 'Banchio', 'Belgrano', 'Bonazzola', 'Bruno', 'Camilatto',
        'Camusso', 'Costa', 'Gheco', 'Morales', 'Morello', 'Nebiolo', 'Ortiz de Zárate',
        'Azanza', 'Ignacio Azanza', 'Abregú', 'Gotero', 'Luero', 'Galizzi', 'Marcelo Galizzi',
        'Martínez', 'Rita Martínez', 'Mario Martínez', 'Liliana Martínez', 'Pardo', 'Sabio',
        'San Lorenzo', 'Santiváñez', 'Zimmerman', 'Bolognesi', 'Cardoso', 'Esterkil',
        'Figueroa Sobrero', 'Gómez', 'Imhof', 'Lagger', 'Lagger Zurbriggen', 'Lencinas',
        'María Selva', 'Santiago', 'Vinderola', 'Coniglio', 'Dentesani', 'Finelli',
        'Fucksmann', 'Leiva', 'Mercado Central', 'Morante', 'Queglas', 'Las Flores II',
        'Salvatierra', 'Sileoni', 'Bosch', 'Amherdt', 'Bourdil', 'Escudero', 'Ghersi',
        'Jullier', 'Lauxmann', 'Theiller', 'Tio', 'Throendly', 'Timofiejuk', 'Wailloud',
        'Bordignon', 'Acosta', 'Alejandro Senn', 'Bonazzola Denise', 'Chelini', 'Giulioni',
        'López', 'Germán López', 'Mai', 'Martínez Juan', 'Méndez', 'Naito', 'Pasteur',
        'Rojas', 'Valetti', 'Barrientos', 'Diagonal', 'Clavé', 'Fanessi', 'Felo',
        'Judith Acevedo', 'Montes', 'Sen', 'Coltrinari', 'Suppo', 'Ugolini',
        'Argenti', 'Berron', 'Castro Karina', 'Facino', 'Labath', 'Mónica Wagner',
        'Rojas Sotelo', 'Pescetti', 'Pescetti Maximiliano', 'Scalzo', 'Vilarrubi',
        'Armando', 'Arrimada', 'Daniel Lagger', 'Del Barco', 'Burgués Romano',
        'Lucía Banchio', 'Mazzali', 'Pellegrini', 'Plank', 'Sobrero', 'Strada',
        'Assinari', 'Capra', 'Costa Samita', 'Caporizzo', 'Donadío', 'Junges',
        'Long', 'Mergen', 'Ortega', 'Pedro Kornijuk', 'Sartor', 'Valverde',
        'Verónica Cano', 'Vignolo', 'Bertolif', 'Chemes', 'Col', 'Damiani',
        'Domet Hurani', 'Gabriel Jauregui', 'Irrazabal', 'Nicolau Manzur', 'Pa',
        'Peiro', 'Stricker', 'Zapata Morán', 'García', 'Bonazzola Estefanía',
        'Brambilla', 'Buil', 'Coli', 'Giménez', 'Imvinkelried', 'Mansilla',
        'Menapace', 'Pescetti P', 'Ranzuglia', 'Wagner Burgués', 'Zeniner',
        // Santo Tomé
        'Erica Tepp', 'Stessens', 'Villata', 'Sauco', 'Olivero', 'Escobar',
        'Cirelli', 'Zimmermann', 'Marta Tepp', 'Bonino', 'Pescetti Julieta',
        'Berta', 'Cruz', 'Curado', 'Mayoráz', 'Macagno', 'Contini', 'Marcolini',
        'San Roque', 'Quassolo', 'Mariana Gómez', 'Terenzi', 'Firmani', 'Palacin',
    ];

    // Palabras que NO pueden ser nombres de farmacias
    private array $stopwords = [
        'COLEGIO', 'FARMACEUTICOS', 'PROVINCIA', 'LEY', 'PRIMERA', 'CIRCI',
        'TURNO', 'URGENCIAS', 'TOXICOLOGICAS', 'HOSPITAL', 'ALASSIA', 'CULLEN',
        'PRIMER', 'SEGUNDO', 'TERCER', 'CUARTO', 'QUINTO', 'SEXTO', 'SEPTIMO',
        'OCTAVO', 'NOVENO', 'DECIMO', 'UNDECIMO', 'DUODECIMO', 'INSCRIPCION',
        'Desde', 'hasta', 'Tel', 'Loc'
    ];

    public function __construct(GeocodeService $geo)
    {
        $this->geo = $geo;
        $this->farmaciaMatching = app(\App\Services\FarmaciaMatchingService::class);
    }

    public function procesar(string $ruta): array
    {
        if (!file_exists($ruta)) {
            return ['error' => "No existe el archivo: $ruta"];
        }

        // 1) Ejecutar OCR (Tesseract)
        $imgService = app(\App\Services\PdfToImageService::class);
        $imagePaths = $imgService->convertToImage($ruta);

        if (!$imagePaths || !is_array($imagePaths) || count($imagePaths) === 0) {
            return ['error' => "No se pudo convertir el PDF a imágenes con Poppler"];
        }

        $textoBruto = '';

        foreach ($imagePaths as $colPath) {
            try {
                $ocr = (new \thiagoalessio\TesseractOCR\TesseractOCR($colPath))
                    ->executable('C:\Program Files\Tesseract-OCR\tesseract.exe')
                    ->lang('spa')
                    ->psm(6)
                    ->oem(3);

                $textoBruto .= $ocr->run() . "\n";
            } catch (\Throwable $e) {
                \Log::warning("Error ejecutando Tesseract en columna $colPath: " . $e->getMessage());
            }
        }

        // 2) Limpiar preservando saltos de línea
        $textoBruto = $this->limpiezaLocalOCR($textoBruto);
        @file_put_contents(storage_path('logs/ocr_last_raw.txt'), $textoBruto);

        $textoLimpio = $textoBruto;

        if (env('GEMINI_ENABLED', false)) {
            \Log::info("Gemini está habilitado, intentando limpiar texto...");
            $resultado = $this->limpiarTextoConGemini($textoBruto);

            if (!isset($resultado['error'])) {
                $textoLimpio = $resultado;
            } else {
                \Log::warning("Gemini falló, usando texto OCR sin limpiar: " . $resultado['error']);
            }
        } else {
            \Log::info("Gemini deshabilitado, usando texto OCR directo");
        }

        @file_put_contents(storage_path('logs/ocr_last_clean.txt'), $textoLimpio);

        \Log::info("====== TEXTO OCR DESPUÉS DE LIMPIEZA ======");
        \Log::info(substr($textoLimpio, 0, 1000));
        \Log::info("============================================");

        return $this->procesarTexto($textoLimpio);
    }

    private function limpiezaLocalOCR(string $texto): string
    {
        $reemplazos = [
            '/(\d{3,4})[\s]*[=£\*E27]{1,2}[\s]*(\d{3,5})/' => '$1-$2',
            '/\.{3,}/' => ' ',
            '/\s{2,}/' => ' ',
            '/[ceeu]{3,}/i' => ' ',
            '/@{2,}/' => '',
            '/\s+-\s+-\s+/' => ' - ',
            '/^[^\w\s]+/' => '',
        ];

        $lineas = explode("\n", $texto);
        $lineasLimpias = [];

        foreach ($lineas as $linea) {
            $linea = trim($linea);

            if (empty($linea)) continue;

            foreach ($reemplazos as $patron => $reemplazo) {
                $linea = preg_replace($patron, $reemplazo, $linea);
            }

            $linea = preg_replace('/\s+(nn|rrr|eee|uuu|cen|mer|nar|ana)\s+/i', ' ', $linea);
            $linea = preg_replace('/\.\s*\.+/', ' ', $linea);

            if (strlen($linea) > 3) {
                $lineasLimpias[] = trim($linea);
            }
        }

        return implode("\n", $lineasLimpias);
    }

    private function limpiarTextoConGemini(string $textoBruto, int $maxReintentos = 3): string|array
    {
        $intento = 0;
        $apiKey = env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            return ['error' => 'GEMINI_API_KEY no está configurada'];
        }

        $modelos = [
            'gemini-1.5-flash',
            'gemini-1.5-pro',
            'gemini-pro',
            'gemini-1.0-pro',
        ];

        foreach ($modelos as $modelo) {
            \Log::info("Intentando con modelo: {$modelo}");

            while ($intento < $maxReintentos) {
                try {
                    \Log::info("Intento {$intento} de limpieza con Gemini (modelo: {$modelo})...");

                    $prompt = "Eres un experto en limpiar texto OCR de turnos de farmacias argentinas. 

INSTRUCCIONES CRÍTICAS:
1. Corrige caracteres mal reconocidos: 
   - Reemplaza \"=\" por \"-\" en teléfonos
   - Corrige \"ceee\", \"u.u\", \"eee\" y similares por espacios
   - Elimina puntos extras en nombres (ej: \"Banchio ............\" → \"Banchio\")
   - Corrige símbolos raros: \"**\", \"£\", \"27\", \"E\", \"5\", \"7\", \"25\" antes de teléfonos por \"-\"
   - **IMPORTANTE**: Corrige fechas mal leídas: \"-4/12\" → \"24/12\", \"-0/12\" → \"20/12\", \"-1/12\" → \"21/12\"
   
2. Formato de datos:
   - Nombre de farmacia | Dirección | Teléfono (formato limpio: 0342-XXXXXX o solo número)
   - Mantén fechas exactamente como están DESPUÉS de corregirlas (DD/MM)
   - Conserva nombres de ciudades: SANTA FE, SANTO TOMÉ
   - Mantén encabezados de turnos: PRIMER TURNO, SEGUNDO TURNO, etc.

3. Limpieza de texto:
   - Elimina puntos suspensivos excesivos
   - Corrige espacios múltiples
   - Mantén SOLO un espacio entre palabras
   - Conserva la estructura de líneas (una farmacia por línea)

4. NO INVENTES DATOS, solo limpia lo que está.

TEXTO OCR A LIMPIAR:

{$textoBruto}";

                    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key={$apiKey}";

                    $data = [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ]
                    ];

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json'
                    ]);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlError = curl_error($ch);
                    curl_close($ch);

                    if ($curlError) {
                        throw new \Exception("Error cURL: {$curlError}");
                    }

                    if ($httpCode === 404) {
                        \Log::info("Modelo {$modelo} no disponible, probando siguiente...");
                        $intento = 0;
                        break;
                    }

                    if ($httpCode !== 200) {
                        $error = json_decode($response, true);
                        $errorMsg = $error['error']['message'] ?? $response;
                        throw new \Exception("HTTP {$httpCode}: {$errorMsg}");
                    }

                    $result = json_decode($response, true);

                    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                        \Log::info("Gemini respondió exitosamente con modelo {$modelo}");
                        return $result['candidates'][0]['content']['parts'][0]['text'];
                    }

                    throw new \Exception("Respuesta de Gemini sin contenido válido");
                    
                } catch (\Throwable $e) {
                    $intento++;
                    $errorMsg = $e->getMessage();

                    \Log::warning("Error Gemini (intento {$intento}): {$errorMsg}");

                    if (str_contains($errorMsg, 'quota') ||
                        str_contains($errorMsg, 'limit') ||
                        str_contains($errorMsg, '429') ||
                        str_contains($errorMsg, '503') ||
                        str_contains($errorMsg, 'RESOURCE_EXHAUSTED')) {
                            
                        if ($intento < $maxReintentos) {
                            $tiempoEspera = pow(2, $intento) * 5;
                            \Log::info("Error temporal detectado, esperando {$tiempoEspera} segundos...");
                            sleep($tiempoEspera);
                            continue;
                        }
                    }

                    if ($intento >= $maxReintentos) {
                        break;
                    }
                }
            }
        }

        return ['error' => "No se pudo limpiar con Gemini después de probar todos los modelos disponibles"];
    }

    public function procesarTexto(string $textoBruto, ?string $ciudadDefault = 'Santa Fe'): array
    {
        $texto = str_replace(["\r\n", "\r"], "\n", $textoBruto);
        $lines = explode("\n", $texto);

        $currentCity = null;
        $currentTurnDates = null; // array [inicio(DateTime), fin(DateTime)]
        $items = []; // elementos detectados: nombre,direccion,telefono,ciudad,fechaPair

        foreach ($lines as $rawLine) {
            $line = trim($rawLine);
            if ($line === '' || strlen($line) < 10) continue;
            $upper = mb_strtoupper($line, 'UTF-8');

            // DETECTAR CIUDAD (busca palabras claves)
            if (preg_match('/\bSANTA\s*FE\b/u', $upper)) {
                $currentCity = 'Santa Fe';
                continue;
            }
            if (preg_match('/\bSANTO\s*TOM(É|E)?\b/iu', $upper)) {
                $currentCity = 'Santo Tomé';
                continue;
            }

            // DETECTAR BLOQUE FECHAS (ej: Desde 8 hs .. 03/11 - hasta 8 hs .. 04/11)
            $fechasCorregidas = OcrCleaner::extractAndFixDates($line);

            if (count($fechasCorregidas) >= 2) {
                \Log::info("Fechas detectadas en línea: " . implode(', ', $fechasCorregidas));

                $currentTurnDates = [];
                for ($i = 0; $i < count($fechasCorregidas); $i += 2) {
                    if (!isset($fechasCorregidas[$i + 1])) break;

                    [$d1, $m1] = array_map('intval', explode('/', $fechasCorregidas[$i]));
                    [$d2, $m2] = array_map('intval', explode('/', $fechasCorregidas[$i + 1]));

                    $year = $this->inferYear($d1, $m1, $d2, $m2);
                    // convertir a Carbon (inicio 8:00 del primer día, fin 8:00 del segundo día)
                    $currentTurnDates[] = [
                        Carbon::create($year, $m1, $d1, 8, 0, 0),
                        Carbon::create($year, $m2, $d2, 8, 0, 0)
                    ];

                    \Log::info("Turno creado: {$d1}/{$m1}/{$year} - {$d2}/{$m2}/{$year}");
                }
                continue;
            }

            // IGNORAR BLOQUES QUE NO SON FARMACIAS (HOSPITALES, URGENCIAS, TOXICOLÓGICAS)
            if (preg_match('/\b(URGENCIAS?|HOSPITAL|TOXICOLOGICAS?|ALASSIA|CULLEN)\b/i', $upper)) {
                \Log::info("Línea ignorada por ser de urgencias o hospital: {$line}");
                continue;
            }

            // LÍNEAS CON MÚLTIPLES FARMACIAS
            $numTelefonos = preg_match_all('/\d{3,4}[\s\-=£\*E27]{0,3}\d{3,5}/', $line);
            if ($numTelefonos > 1) {
                \Log::info("Múltiples farmacias detectadas en línea, intentando subdividir...");

                $partes = preg_split('/\s+(?=[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+\s+[A-Za-záéíóúñ])/', $line);

                if (count($partes) < $numTelefonos) {
                    $partes = preg_split('/\s{3,}|—/', $line);
                }

                foreach ($partes as $parte) {
                    $parte = trim($parte);
                    if (strlen($parte) < 15) continue;

                    $telsEnParte = preg_match_all('/\d{3,4}[\s\-]{0,2}\d{3,5}/', $parte);

                    if ($telsEnParte === 1) {
                        $this->procesarLineaFarmacia($parte, $currentCity, $ciudadDefault, $currentTurnDates, $items);
                    } else {
                        \Log::warning("Subdivisión fallida, múltiples teléfonos en: {$parte}");
                    }
                }
                continue;
            }

            // LÍNEAS INDIVIDUALES
            $this->procesarLineaFarmacia($line, $currentCity, $ciudadDefault, $currentTurnDates, $items);
        }

        if (empty($items)) {
            \Log::warning("No se detectaron farmacias válidas en el OCR");
            return ['farmacias' => 0, 'turnos' => 0];
        }

        \Log::info("Total de farmacias detectadas: " . count($items));
        return $this->guardarEnBD($items);
    }

    /**
     * Encontrar nombre conocido más cercano usando Levenshtein mejorado
     */
    private function encontrarNombreSimilar(string $nombreSucio): ?array
    {
        $nombreLimpio = strtolower(preg_replace('/[^a-zA-ZáéíóúñÁÉÍÓÚÑ\s]/', '', $nombreSucio));
        $mejorCoincidencia = null;
        $menorDistancia = PHP_INT_MAX;

        foreach ($this->nombresConocidos as $nombreConocido) {
            $nombreConocidoLower = strtolower($nombreConocido);
            
            // Calcular distancia Levenshtein
            $distancia = levenshtein(
                $nombreConocidoLower,
                substr($nombreLimpio, 0, strlen($nombreConocido) + 10)
            );

            // Umbral dinámico: 35% de la longitud del nombre conocido
            $umbral = max(3, (int)(strlen($nombreConocido) * 0.35));

            if ($distancia < $menorDistancia && $distancia <= $umbral) {
                $menorDistancia = $distancia;
                $mejorCoincidencia = [
                    'nombre' => $nombreConocido,
                    'distancia' => $distancia,
                    'confianza' => 100 - (($distancia / strlen($nombreConocido)) * 100)
                ];
            }
            
            // También verificar si el nombre sucio CONTIENE el nombre conocido
            if (strlen($nombreConocidoLower) >= 5 && stripos($nombreLimpio, $nombreConocidoLower) !== false) {
                $confianza = 95 - (abs(strlen($nombreLimpio) - strlen($nombreConocidoLower)) * 2);
                
                if ($confianza > 70 && (!$mejorCoincidencia || $confianza > $mejorCoincidencia['confianza'])) {
                    $mejorCoincidencia = [
                        'nombre' => $nombreConocido,
                        'distancia' => 0,
                        'confianza' => $confianza
                    ];
                }
            }
        }

        // Solo retornar si la confianza es mayor al 60%
        if ($mejorCoincidencia && $mejorCoincidencia['confianza'] >= 60) {
            return $mejorCoincidencia;
        }

        return null;
    }

    /**
     * Validación estricta de líneas
     */
    private function esLineaValidaDeFarmacia(string $line): bool
    {
        $upper = mb_strtoupper($line, 'UTF-8');
        
        // Rechazar stopwords
        foreach ($this->stopwords as $stopword) {
            if (stripos($upper, $stopword) !== false && strlen($line) < 40) {
                \Log::info("[Validación] Línea descartada por contener stopword '{$stopword}': {$line}");
                return false;
            }
        }

        // Rechazar líneas con demasiados caracteres especiales
        $caracteresRaros = preg_match_all('/[^a-zA-Z0-9\sáéíóúñÁÉÍÓÚÑ\.\-\/]/', $line);
        if ($caracteresRaros > 15) {
            \Log::info("[Validación] Línea descartada por exceso de caracteres raros ({$caracteresRaros}): {$line}");
            return false;
        }

        // Rechazar líneas muy cortas o muy largas
        $longitud = strlen($line);
        if ($longitud < 20 || $longitud > 200) {
            \Log::info("[Validación] Línea descartada por longitud inválida ({$longitud}): {$line}");
            return false;
        }

        // Debe tener al menos un número (dirección o teléfono)
        $tieneNumero = preg_match('/\b\d{3,5}\b/', $line);
        if (!$tieneNumero) {
            \Log::info("[Validación] Línea descartada por falta de números: {$line}");
            return false;
        }

        return true;
    }

    /**
     * Limpieza de nombres con fuzzy matching
     */
    private function limpiarNombreFarmacia(string $nombre): ?array
    {
        $nombreOriginal = $nombre;

        // 1) Intentar encontrar coincidencia con nombres conocidos PRIMERO
        $match = $this->encontrarNombreSimilar($nombre);
        if ($match) {
            \Log::info("[Limpieza] '{$nombreOriginal}' → '{$match['nombre']}' (confianza: {$match['confianza']}%)");
            return $match;
        }

        // 2) Si no hay coincidencia, limpiar manualmente
        $nombreLimpio = preg_replace('/\s+(nn|rrr|eee|uuu|cen|mer|nar|ana|ac|ee|rner|ene|nen|es|er)\s+/i', ' ', $nombre);
        $nombreLimpio = preg_replace('/\.{2,}/', '', $nombreLimpio);
        $nombreLimpio = preg_replace('/\s+/', ' ', $nombreLimpio);
        $nombreLimpio = trim($nombreLimpio);

        // Remover terminaciones raras
        $nombreLimpio = preg_replace('/[\-\.\s]+$/', '', $nombreLimpio);
        $nombreLimpio = preg_replace('/^[\-\.\s]+/', '', $nombreLimpio);

        // Validaciones de calidad
        if (strlen($nombreLimpio) < 4) {
            \Log::info("[Validación] Nombre muy corto después de limpieza: '{$nombreOriginal}'");
            return null;
        }

        if (preg_match_all('/\d/', $nombreLimpio) > 3) {
            \Log::info("[Validación] Demasiados números en nombre: '{$nombreOriginal}'");
            return null;
        }

        if (substr_count($nombreLimpio, '-') > 3 || substr_count($nombreLimpio, '.') > 3) {
            \Log::info("[Validación] Demasiados separadores en nombre: '{$nombreOriginal}'");
            return null;
        }

        if (!preg_match('/[a-zA-ZáéíóúñÁÉÍÓÚÑ]{3,}/', $nombreLimpio)) {
            \Log::info("[Validación] No hay suficientes letras consecutivas: '{$nombreOriginal}'");
            return null;
        }

        \Log::info("[Limpieza] '{$nombreOriginal}' → '{$nombreLimpio}' (limpieza manual, sin match)");
        
        return [
            'nombre' => $nombreLimpio,
            'confianza' => 50, // Baja confianza si no coincide con nombres conocidos
            'distancia' => -1
        ];
    }

    /**
     * Validación de teléfonos
     */
    private function validarTelefono(string $telefono): ?string
    {
        $telefono = preg_replace('/\D/', '', $telefono);

        if (strlen($telefono) < 6 || strlen($telefono) > 10) {
            return null;
        }

        if (substr($telefono, 0, 4) === '0342') {
            $telefono = substr($telefono, 4);
        }

        // Rechazar números obviamente incorrectos
        if (preg_match('/^[0-9]{1,2}$/', $telefono) || preg_match('/^0+$/', $telefono)) {
            return null;
        }

        return $telefono;
    }

    /**
     * Extracción de direcciones más robusta
     */
    private function extractAddress(string $line): ?string
    {
        $lineaLimpia = preg_replace('/\s+(nn|ac|ee|rrr|eee)\s+/i', ' ', $line);

        // Patrones de calles argentinas comunes
        $patrones = [
            '/((Av\.?|Avenida|Bv\.?|Boulevard|Calle|Diagonal|San|Dr\.?|Dra\.?|Gral\.?|Marcial|Mariano|Stgo\.?|Santiago|Fdo\.?|Fernando|Fray|Gobernador|Angel|Padre|Ejército|Obispo|Hipólito)[^\d\n]*\d{1,5})/iu',
            '/([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+(?:\s+[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+){0,3})\s+(\d{1,5})\b/u',
        ];

        foreach ($patrones as $patron) {
            if (preg_match($patron, $lineaLimpia, $m)) {
                $dir = trim($m[1] ?? $m[0]);
                $dir = preg_replace('/\.{2,}/', '', $dir);
                $dir = preg_replace('/\s+/', ' ', $dir);
                
                // Validar que la dirección tenga sentido
                if (strlen($dir) > 8 && preg_match('/\d/', $dir)) {
                    return trim($dir);
                }
            }
        }

        return null;
    }

    /**
     * Extracción de nombres más inteligente
     */
    private function extractName(string $line, ?string $direccion, ?string $telefono): string
    {
        Log::info("[OCR] Extrayendo nombre de farmacia. Línea original: '{$line}'");

        $tmp = $line;

        // Remover teléfono
        if ($telefono) {
            $pattern = '/' . preg_quote($telefono, '/') . '.*$/';
            $tmp = preg_replace($pattern, '', $tmp);
        }

        // Remover dirección (MEJORADO: también remover fragmentos incompletos)
        if ($direccion) {
            $tmp = str_ireplace($direccion, '', $tmp);
            
            // También remover solo la calle si queda
            $partesDir = explode(' ', $direccion);
            if (count($partesDir) > 0) {
                foreach ($partesDir as $parte) {
                    if (strlen($parte) > 4) {
                        $tmp = str_ireplace($parte, '', $tmp);
                    }
                }
            }
        }

        // Remover palabras comunes de direcciones
        $palabrasDireccion = ['Avenida', 'Boulevard', 'Calle', 'Diagonal', 'Bulevar', 'Av.', 'Bv.'];
        foreach ($palabrasDireccion as $palabra) {
            $tmp = str_ireplace($palabra, '', $tmp);
        }

        // Remover números sueltos (probablemente parte de dirección/teléfono)
        $tmp = preg_replace('/\b\d{3,}\b/', '', $tmp);

        // Limpiar basura de OCR
        $tmp = preg_replace('/[@\.\-]{2,}/', ' ', $tmp);
        $tmp = preg_replace('/[=£\*E27]+/', '', $tmp);
        $tmp = preg_replace('/\.{2,}/', '', $tmp);

        // Mantener solo caracteres válidos
        $tmp = preg_replace('/[^\w\sáéíóúñÁÉÍÓÚÑ\.\-]/u', '', $tmp);

        $final = trim($tmp);
        Log::info("[OCR] Nombre final extraído: '{$final}'");

        return $final;
    }

    /**
     * Procesar línea individual con validación completa
     */
    private function procesarLineaFarmacia(string $line, ?string $currentCity, string $ciudadDefault, ?array $currentTurnDates, array &$items): void
    {
        if (!$this->esLineaValidaDeFarmacia($line)) {
            return;
        }

        $telefono = OcrCleaner::fixPhone($line);
        $direccion = $this->extractAddress($line);
        $nombreSucio = $this->extractName($line, $direccion, $telefono);

        // Limpiar nombre con fuzzy matching
        $matchResult = $this->limpiarNombreFarmacia($nombreSucio);

        if (!$matchResult || empty($matchResult['nombre'])) {
            \Log::info("[Validación] Nombre inválido después de limpieza: '{$nombreSucio}'");
            return;
        }

        $nombre = $matchResult['nombre'];
        $confianza = $matchResult['confianza'];

        // Rechazar nombres con baja confianza que parecen basura
        if ($confianza < 50 && preg_match('/[^a-zA-ZáéíóúñÁÉÍÓÚÑ\s\-]/', $nombre)) {
            \Log::info("[Validación] Nombre rechazado por baja confianza ({$confianza}%) y caracteres raros: '{$nombre}'");
            return;
        }

        // Validar teléfono
        if ($telefono) {
            $telefono = $this->validarTelefono($telefono);
        }

        // Debe tener al menos dirección O teléfono válido
        $telefonoValido = !empty($telefono);
        $direccionValida = !empty($direccion) && strlen($direccion) > 5;

        if (!$telefonoValido && !$direccionValida) {
            \Log::info("[Validación] Farmacia descartada - sin teléfono ni dirección válidos: {$nombre}");
            return;
        }

        $ciudad = $currentCity ?? $ciudadDefault;

        // Normalizar dirección
        if ($direccion) {
            $direccion = OcrCleaner::normalizeAddress($direccion);
            $direccion = OcrCleaner::fixStreetNames($direccion);
            [$direccion, $notas] = OcrCleaner::splitAddressNotes($direccion);
        }

        // Buscar coincidencia en BD existente
        $match = $this->farmaciaMatching->buscarCoincidencia($nombre, $ciudad);
        
        if ($match && $match['confianza'] >= 80) {
            \Log::info("✅ MATCH EN BD: '{$nombre}' → '{$match['nombre_correcto']}' (confianza: {$match['confianza']}%)");
            $nombre = $match['nombre_correcto'];
            
            // Usar datos de BD si los datos OCR están incompletos
            if (!$direccion && $match['direccion_correcta']) {
                $direccion = $match['direccion_correcta'];
                \Log::info("   → Usando dirección de BD: {$direccion}");
            }
            if (!$telefono && $match['telefono_correcto']) {
                $telefono = $match['telefono_correcto'];
                \Log::info("   → Usando teléfono de BD: {$telefono}");
            }
        }

        // Iterar sobre cada turno
        if ($currentTurnDates && is_array($currentTurnDates)) {
            foreach ($currentTurnDates as $turno) {
                $items[] = [
                    'id_tmp'    => Str::random(8),
                    'nombre'    => $nombre,
                    'direccion' => $direccion ?? null,
                    'telefono'  => $telefono,
                    'ciudad'    => $ciudad,
                    'notas'     => $notas ?? null,
                    'turn_dates' => $turno,
                    'confianza' => $confianza
                ];
            }
        }

        \Log::info("✅ Farmacia VALIDADA: {$nombre} | {$direccion} | {$telefono} (confianza: {$confianza}%)");
    }

    private function guardarEnBD(array $items): array
    {
        $stats = ['farmacias' => 0, 'turnos' => 0, 'actualizadas' => 0, 'rechazadas' => 0];
        DB::beginTransaction();
        try {
            foreach ($items as $it) {
                // validar nombre minimo
                if (empty($it['nombre']) || empty($it['turn_dates'])) continue;
                
                // Rechazar farmacias con confianza muy baja
                if (isset($it['confianza']) && $it['confianza'] < 45) {
                    \Log::warning("❌ Farmacia rechazada por baja confianza: {$it['nombre']} ({$it['confianza']}%)");
                    $stats['rechazadas']++;
                    continue;
                }
                
                [$inicio, $fin] = $it['turn_dates'];

                // Crear o recuperar ciudad
                $ciudad = Ciudad::firstOrCreate(['nombre_ciudad' => $it['ciudad']]);

                // Buscar farmacia por nombre + ciudad (evitar duplicados)
                $farmacia = Farmacia::where('nombre', $it['nombre'])
                    ->where('id_ciudad', $ciudad->id_ciudad)
                    ->first();

                if (!$farmacia) {
                    // si no existe, crear
                    $farmacia = Farmacia::create([
                        'nombre' => $it['nombre'],
                        'direccion' => $it['direccion'],
                        'telefono' => $it['telefono'],
                        'id_ciudad' => $ciudad->id_ciudad
                    ]);
                    $stats['farmacias']++;
                } else {
                    // actualizar datos faltantes
                    $changed = false;
                    if (empty($farmacia->direccion) && !empty($it['direccion'])) {
                        $farmacia->direccion = $it['direccion'];
                        $changed = true;
                    }
                    if (empty($farmacia->telefono) && !empty($it['telefono'])) {
                        $farmacia->telefono = $it['telefono'];
                        $changed = true;
                    }
                    if ($changed) {
                        $farmacia->save();
                        $stats['actualizadas']++;
                    }
                }

                // Geocoding solo si hace falta (respeta rate-limit)
                if ((empty($farmacia->lat) || empty($farmacia->lng)) && !empty($farmacia->direccion)) {
                    [$lat, $lng] = $this->geo->buscarVariantes($farmacia->direccion, $ciudad->nombre_ciudad);
                    if ($lat && $lng) {
                        $farmacia->lat = $lat;
                        $farmacia->lng = $lng;
                        $farmacia->save();
                        sleep(1);
                    }
                }

                // Crear turno (si no existe)
                $turno = Turno::firstOrCreate(
                    [
                        'fecha_hora_inicio' => $inicio->toDateTimeString(),
                        'fecha_hora_fin' => $fin->toDateTimeString(),
                        'id_ciudad' => $ciudad->id_ciudad
                    ],
                    [
                        'nombre_turno' => 'Turno ' . $inicio->format('d/m')
                    ]
                );

                $stats['turnos']++;

                $pivot = DB::table('farmacias_turnos')
                    ->where('id_farmacia', $farmacia->id_farmacia)
                    ->where('id_turno', $turno->id_turno)
                    ->first();

                if (!$pivot) {
                    // No existe: insertar todo
                    DB::table('farmacias_turnos')->insert([
                        'id_farmacia' => $farmacia->id_farmacia,
                        'id_turno' => $turno->id_turno,
                        'notas' => $it['notas'] ?? null,
                    ]);
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return ['error' => $e->getMessage()];
        }

        \Log::info("📊 Estadísticas finales: Creadas: {$stats['farmacias']}, Turnos: {$stats['turnos']}, Actualizadas: {$stats['actualizadas']}, Rechazadas: {$stats['rechazadas']}");
        return $stats;
    }

    private function inferYear(int $d1, int $m1, int $d2, int $m2): int
    {
        $y = Carbon::now()->year;
        $inicio = Carbon::create($y, $m1, $d1);
        $fin = Carbon::create($y, $m2, $d2);
        return $fin->lessThan($inicio) ? $y + 1 : $y;
    }
}
