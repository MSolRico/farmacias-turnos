<?php

namespace App\Services;

use thiagoalessio\TesseractOCR\TesseractOCR;
use App\Services\OcrFarmaciasValidator;
use App\Services\TurnoDataPersister;
use App\Helpers\OcrCleaner;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OcrFarmaciasService
{
    protected OcrFarmaciasValidator $validator;
    protected TurnoDataPersister $persister;
    
    // Las listas $nombresConocidos y $stopwords se movieron a OcrFarmaciasValidator.
    // La dependencia GeocodeService se movió a TurnoDataPersister.
    protected $farmaciaMatching; // Se mantiene solo si el Service principal necesita acceder a él directamente, sino se movería a Validator.

    public function __construct(
        OcrFarmaciasValidator $validator,
        TurnoDataPersister $persister
    )
    {
        $this->validator = $validator;
        $this->persister = $persister;
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
            '/[aeiouyrcsn]{5,}/i' => ' ',
            '/[.\-]{2,}/' => ' ',
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

            // FILTRO DE NOTAS DE EXCEPCIÓN
            $esNotaDeExcepcion = preg_match('/(s[oó]lo|solo|nota|excepci[oó]n|estará\s+de\s+turno|turno\s+especial)/iu', $line);

            if ($esNotaDeExcepcion) {
                \Log::info("[Filtro] Línea descartada de creación de turno por ser nota/excepción: {$line}");
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

                    // Usar el inferYear del Validator
                    $year = $this->validator->inferYear($d1, $m1, $d2, $m2);
                    // convertir a Carbon (inicio 8:00 del primer día, fin 8:00 del segundo día)
                    $currentTurnDates[] = [
                        Carbon::create($year, $m1, $d1, 8, 0, 0),
                        Carbon::create($year, $m2, $d2, 8, 0, 0)
                    ];
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
        
        // Delegar la persistencia al TurnoDataPersister
        return $this->persister->guardarEnBD($items);
    }
    
    /**
     * Procesar línea individual con validación completa (DELEGACIÓN DE LÓGICA)
     */
    private function procesarLineaFarmacia(string $line, ?string $currentCity, string $ciudadDefault, ?array $currentTurnDates, array &$items): void
    {
        // 1. Delegar validación de línea inicial
        if (!$this->validator->esLineaValidaDeFarmacia($line)) {
            return;
        }

        // 2. Extracción delegada
        $telefono = OcrCleaner::fixPhone($line);
        $direccion = $this->validator->extractAddress($line);
        $nombreSucio = $this->validator->extractName($line, $direccion, $telefono);

        // 3. Limpieza y fuzzy matching delegada
        $matchResult = $this->validator->limpiarNombreFarmacia($nombreSucio);

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

        // 4. Validación de teléfono delegada
        if ($telefono) {
            $telefono = $this->validator->validarTelefono($telefono);
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
        $notas = null;
        if ($direccion) {
            $direccion = OcrCleaner::normalizeAddress($direccion);
            $direccion = OcrCleaner::fixStreetNames($direccion);
            [$direccion, $notas] = OcrCleaner::splitAddressNotes($direccion);
        }

        // Buscar coincidencia en BD existente (MANTENER AQUÍ para usar `$this->farmaciaMatching`)
        $match = $this->farmaciaMatching->buscarCoincidencia($nombre, $ciudad);
        
        if ($match && $match['confianza'] >= 80) {
            \Log::info("✅ MATCH EN BD: '{$nombre}' → '{$match['nombre_correcto']}' (confianza: {$match['confianza']}%)");
            $nombre = $match['nombre_correcto'];
            
            // Usar datos de BD si los datos OCR están incompletos
            if (!$direccion && isset($match['direccion_correcto']) && $match['direccion_correcto']) {
                $direccion = $match['direccion_correcto'];
                \Log::info("   → Usando dirección de BD: {$direccion}");
            }
            if (!$telefono && isset($match['telefono_correcto']) && $match['telefono_correcto']) {
                $telefono = $match['telefono_correcto'];
                \Log::info("   → Usando teléfono de BD: {$telefono}");
            }
        }

        // Iterar sobre cada turno para el array de items
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
}