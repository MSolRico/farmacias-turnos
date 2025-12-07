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

class OcrFarmaciasService
{
    protected GeocodeService $geo;
    protected $farmaciaMatching;

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
        $imagePath = $imgService->convertToImage($ruta);

        if (!$imagePath) {
            return ['error' => "No se pudo convertir el PDF a imagen con Poppler"];
        }

        try {
            $ocr = (new \thiagoalessio\TesseractOCR\TesseractOCR($imagePath))
                ->executable('C:\Program Files\Tesseract-OCR\tesseract.exe')
                ->lang('spa')
                ->psm(6)
                ->oem(3);

            $textoBruto = $ocr->run();
            $textoBruto = $this->limpiezaLocalOCR($textoBruto);
            
        } catch (\Throwable $e) {
            return ['error' => "Error ejecutando Tesseract: " . $e->getMessage()];
        }

        // 2) Limpiar preservando saltos de línea
        @file_put_contents(storage_path('logs/ocr_last_raw.txt'), $textoBruto);

        $textoLimpio = $textoBruto;
        
        if (env('GEMINI_ENABLED', false)) {
            \Log::info("Gemini está habilitado, intentando limpiar texto...");
            $resultado = $this->limpiarTextoConGemini($textoBruto);
            
            if (isset($resultado['error'])) {
                \Log::warning("Gemini falló, usando texto OCR sin limpiar: " . $resultado['error']);
            } else {
                $textoLimpio = $resultado;
                \Log::info("Texto limpiado exitosamente con Gemini");
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
            '/([A-Za-z\s])[\s]*[=£\*E27]{1,2}[\s]*(\d)/' => '$1 -$2',
            '/\s+[=£\*E27]+\s*(\d)/' => ' -$1',
            '/\.{3,}/' => ' ',
            '/\s{2,}/' => ' ',
            '/[ceeu]{3,}/i' => ' ',
            '/@{2,}/' => '',
            '/\s+-\s+-\s+/' => ' - ',
            '/^[^\w\s]+/' => '',
            '/(\d{3,4})\s*[\s\-=£\*E27]+\s*(\d{4,5})/' => '$1-$2',
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
   
2. Formato de datos:
   - Nombre de farmacia | Dirección | Teléfono (formato limpio: 0342-XXXXXX o solo número)
   - Mantén fechas exactamente como están (DD/MM)
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
            if (preg_match_all('/(\d{1,2}\/\d{1,2})/', $line, $m) && count($m[1]) >= 2) {
                [$d1, $m1] = array_map('intval', explode('/', $m[1][0]));
                [$d2, $m2] = array_map('intval', explode('/', $m[1][1]));
                $year = $this->inferYear($d1, $m1, $d2, $m2);
                // convertir a Carbon (inicio 8:00 del primer día, fin 8:00 del segundo día)
                $currentTurnDates = [
                    Carbon::create($year, $m1, $d1, 8, 0, 0),
                    Carbon::create($year, $m2, $d2, 8, 0, 0)
                ];
                continue;
            }

            // IGNORAR BLOQUES QUE NO SON FARMACIAS (HOSPITALES, URGENCIAS, TOXICOLÓGICAS)
            if (preg_match('/\b(URGENCIAS?|HOSPITAL|TOXICOLOGICAS?|ALASSIA|CULLEN)\b/i', $upper)) {
                \Log::info("Línea ignorada por ser de urgencias o hospital: {$line}");
                continue;
            }

            // DETECTAR LÍNEAS DE FARMACIA (heurística: teléfono o formato habitual)
            // La expresión captura números en formato variados.
            $numTelefonos = preg_match_all('/\d{3,4}[\s\-=£\*E27]{0,3}\d{3,5}/', $line, $telefonosMatches);
            
            if ($numTelefonos > 1) {
                \Log::info("Detectadas {$numTelefonos} farmacias en una línea, intentando dividir...");
                
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
                    } elseif ($telsEnParte > 1) {
                        \Log::warning("Subdivisión falló, múltiples teléfonos en: {$parte}");
                    }
                }
                continue;
            }
            
            if (preg_match('/^([A-Za-zÁÉÍÓÚáéíóúñÑ\s\.]+?)\s+([A-Za-z\.]+.*?)\s+(\d{3,5})\s.*?(\d{3,4})[\s\-]*(\d{3,5})\s*$/u', $line, $matches)) {
                
                $nombreRaw = trim($matches[1]);
                $direccionRaw = trim($matches[2] . ' ' . $matches[3]);
                $telefonoRaw = $matches[4] . $matches[5];
                
                $nombre = $this->cleanNombre($nombreRaw);
                
                if (empty($nombre) || strlen($nombre) < 3 || strlen($nombre) > 50) {
                    \Log::warning("Nombre inválido, línea ignorada: {$line}");
                    continue;
                }
                
                if (substr_count($nombre, '-') > 2 || preg_match('/[^\w\sáéíóúñÁÉÍÓÚÑ\.\-]/u', $nombre)) {
                    \Log::warning("Nombre con caracteres extraños, línea ignorada: {$nombre}");
                    continue;
                }
                
                $nombre = OcrCleaner::normalizeName($nombre);
                $ciudad = $currentCity ?? $ciudadDefault;

                // Buscar coincidencia con farmacias conocidas
                $match = $this->farmaciaMatching->buscarCoincidencia($nombre, $ciudad);
                if ($match && $match['confianza'] >= 80) {
                    \Log::info("✓ Farmacia corregida: '{$nombre}' → '{$match['nombre_correcto']}' (confianza: {$match['confianza']}%, método: {$match['metodo']})");
                    $nombre = $match['nombre_correcto'];
                }
                
                $direccion = OcrCleaner::normalizeAddress($direccionRaw);
                $direccion = OcrCleaner::fixStreetNames($direccion);
                [$direccion, $notas] = OcrCleaner::splitAddressNotes($direccion);
                
                $telefono = preg_replace('/\D+/', '', $telefonoRaw);
                
                if (strlen($telefono) < 6 || strlen($telefono) > 10) {
                    \Log::warning("Teléfono inválido: {$telefono}, línea: {$line}");
                    $telefono = null;
                }

                \Log::info("✓ Farmacia detectada: {$nombre} | {$direccion} | {$telefono}");

                $items[] = [
                    'id_tmp'    => Str::random(8),
                    'nombre'    => $nombre,
                    'direccion' => $direccion,
                    'telefono'  => $telefono,
                    'ciudad'    => $ciudad,
                    'notas'     => $notas,
                    'turn_dates' => $currentTurnDates
                ];
                
                continue;
            }

            // Otras líneas: posible continuación de la dirección (ej. línea previa tenía nombre + teléfono)
            // Intento unir con último item si parece una dirección
            if (preg_match('/\d{3,4}[\s\-=£\*E27]{0,3}\d{3,5}/', $line)) {
                
                $numTelefonos = preg_match_all('/\d{3,4}[\s\-=£\*E27]{0,3}\d{3,5}/', $line);
                if ($numTelefonos > 1) {
                    \Log::warning("Múltiples teléfonos en una línea, ignorando: {$line}");
                    continue;
                }
                
                $telefono = OcrCleaner::fixPhone($line);
                $direccion = $this->extractAddress($line);
                $nombre = $this->extractName($line, $direccion, $telefono);

                if (empty($nombre) || strlen($nombre) < 3 || strlen($nombre) > 50) {
                    \Log::warning("Nombre inválido (flexible), línea ignorada: {$line}");
                    continue;
                }

                $caracteresRaros = preg_match_all('/[^\w\sáéíóúñÁÉÍÓÚÑ\.\-]/u', $nombre);
                if ($caracteresRaros > 3) {
                    \Log::warning("Demasiados caracteres raros en nombre: {$nombre}");
                    continue;
                }

                $nombre = OcrCleaner::normalizeName($nombre);
                $ciudad = $currentCity ?? $ciudadDefault;

                // Buscar coincidencia con farmacias conocidas
                $match = $this->farmaciaMatching->buscarCoincidencia($nombre, $ciudad);
                if ($match && $match['confianza'] >= 80) {
                    \Log::info("✓ Farmacia corregida (flexible): '{$nombre}' → '{$match['nombre_correcto']}' (confianza: {$match['confianza']}%, método: {$match['metodo']})");
                    $nombre = $match['nombre_correcto'];
                    
                    if (empty($direccion) && !empty($match['direccion_correcta'])) {
                        $direccion = $match['direccion_correcta'];
                        \Log::info("→ Dirección completada desde BD: {$direccion}");
                    }
                    
                    if (empty($telefono) && !empty($match['telefono_correcto'])) {
                        $telefono = $match['telefono_correcto'];
                        \Log::info("→ Teléfono completado desde BD: {$telefono}");
                    }
                }
                
                $direccion = OcrCleaner::normalizeAddress($direccion ?? '');
                $direccion = OcrCleaner::fixStreetNames($direccion);
                [$direccion, $notas] = OcrCleaner::splitAddressNotes($direccion);

                $telefono = $telefono ? preg_replace('/\D+/', '', $telefono) : null;
                
                if ($telefono && (strlen($telefono) < 6 || strlen($telefono) > 10)) {
                    \Log::warning("Teléfono inválido (flexible): {$telefono}");
                    $telefono = null;
                }

                \Log::info("✓ Farmacia detectada (flexible): {$nombre} | {$direccion} | {$telefono}");

                $items[] = [
                    'id_tmp'    => Str::random(8),
                    'nombre'    => $nombre,
                    'direccion' => $direccion,
                    'telefono'  => $telefono,
                    'ciudad'    => $ciudad,
                    'notas'     => $notas,
                    'turn_dates' => $currentTurnDates
                ];
                continue;
            }
        }

        if (empty($items)) {
            \Log::warning("No se detectaron farmacias válidas en el OCR");
            return ['farmacias' => 0, 'turnos' => 0];
        }

        \Log::info("Total de farmacias detectadas: " . count($items));
        return $this->guardarEnBD($items);
    }

    private function cleanNombre(string $nombre): string
    {
        $nombre = preg_replace('/\.{2,}/', '', $nombre);
        $nombre = preg_replace('/\b(nn|rrr|eee|uuu|cen|mer|nar|ana|ac|ee|rner|ene|nen|es)\b/i', '', $nombre);
        $nombre = preg_replace('/\s+/', ' ', $nombre);
        return trim($nombre);
    }

    private function procesarLineaFarmacia(string $line, ?string $currentCity, string $ciudadDefault, ?array $currentTurnDates, array &$items): void
    {
        $telefono = OcrCleaner::fixPhone($line);
        $direccion = $this->extractAddress($line);
        $nombre = $this->extractName($line, $direccion, $telefono);

        if (empty($nombre) || strlen($nombre) < 4 || strlen($nombre) > 50) {
            \Log::debug("Nombre inválido en subdivisión (longitud): {$nombre}");
            return;
        }
        
        if (!preg_match('/^[A-ZÁÉÍÓÚÑ]/', $nombre)) {
            \Log::debug("Nombre no empieza con mayúscula: {$nombre}");
            return;
        }
        
        if (!preg_match('/[a-záéíóúñ]{3,}/', $nombre)) {
            \Log::debug("Nombre sin palabras completas: {$nombre}");
            return;
        }

        $caracteresRaros = preg_match_all('/[^\w\sáéíóúñÁÉÍÓÚÑ\.\-]/u', $nombre);
        $numeros = preg_match_all('/\d/', $nombre);
        
        if ($caracteresRaros > 2 || $numeros > 2) {
            \Log::debug("Demasiados caracteres raros o números: {$nombre}");
            return;
        }
        
        $telefonoValido = $telefono && strlen(preg_replace('/\D+/', '', $telefono)) >= 6;
        $direccionValida = !empty($direccion) && strlen($direccion) > 5;
        
        if (!$telefonoValido && !$direccionValida) {
            \Log::debug("Sin dirección ni teléfono válidos: {$nombre}");
            return;
        }

        $nombre = OcrCleaner::normalizeName($nombre);
        $ciudad = $currentCity ?? $ciudadDefault;

        // Buscar coincidencia con farmacias conocidas
        $match = $this->farmaciaMatching->buscarCoincidencia($nombre, $ciudad);
        if ($match && $match['confianza'] >= 80) {
            \Log::info("✓ Farmacia corregida (subdivisión): '{$nombre}' → '{$match['nombre_correcto']}' (confianza: {$match['confianza']}%)");
            $nombre = $match['nombre_correcto'];
            
            if (empty($direccion) && !empty($match['direccion_correcta'])) {
                $direccion = $match['direccion_correcta'];
            }
            if (empty($telefono) && !empty($match['telefono_correcto'])) {
                $telefono = $match['telefono_correcto'];
            }
        }
        
        $direccion = OcrCleaner::normalizeAddress($direccion ?? '');
        $direccion = OcrCleaner::fixStreetNames($direccion);
        [$direccion, $notas] = OcrCleaner::splitAddressNotes($direccion);

        $telefono = $telefono ? preg_replace('/\D+/', '', $telefono) : null;
        
        if ($telefono && (strlen($telefono) < 6 || strlen($telefono) > 10)) {
            \Log::debug("Teléfono inválido final: {$telefono}");
            $telefono = null;
        }

        \Log::info("✓ Farmacia válida (subdivisión): {$nombre} | {$direccion} | {$telefono}");

        $items[] = [
            'id_tmp'    => Str::random(8),
            'nombre'    => $nombre,
            'direccion' => $direccion,
            'telefono'  => $telefono,
            'ciudad'    => $ciudad,
            'notas'     => $notas,
            'turn_dates' => $currentTurnDates
        ];
    }

    private function guardarEnBD(array $items): array
    {
        $stats = ['farmacias' => 0, 'turnos' => 0];
        DB::beginTransaction();
        try {
            foreach ($items as $it) {
                // validar nombre minimo
                if (empty($it['nombre']) || empty($it['turn_dates'])) continue;
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
                    if ($changed) $farmacia->save();
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

        return $stats;
    }

    private function extractAddress(string $line): ?string
    {
        // buscar patrones comunes de direccion (con número)
        if (preg_match('/((Av\.?|Avenida|Bv\.?|Boulevard|Calle|Diagonal|San|Dr\.?|Gral\.?|Marcial|Mariano|Stgo\.?|Santiago|Fdo\.?|Fernando|Fray|Gobernador|Dra\.?|Angel|Padre|Ejército)[^\d\n]*\d{1,5})/iu', $line, $m)) {
            return trim($m[1]);
        }
        
        // fallback: buscar primer fragmento con número
        if (preg_match('/([A-Za-zÁÉÍÓÚÑáéíóúñ\s\.]{3,}?)\s+(\d{1,5})\b/u', $line, $m)) {
            $addr = trim($m[0]);
            $addr = preg_replace('/\.{2,}/', '', $addr);
            return trim($addr);
        }
        
        return null;
    }

    private function extractName(string $line, ?string $direccion, ?string $telefono): string
    {
        $tmp = $line;
        
        // quitar teléfono si está al final
        if ($telefono) {
            $tmp = preg_replace('/' . preg_quote($telefono, '/') . '.*$/', '', $tmp);
        }
        
        // quitar dirección si aparece dentro
        if ($direccion) {
            $tmp = str_ireplace($direccion, '', $tmp);
        }
        
        // limpiar números residuales
        $tmp = preg_replace('/\b\d{3,}\b/', '', $tmp);
        
        // quitar símbolos extraños
        $tmp = preg_replace('/[@\.\-]{2,}/', ' ', $tmp);
        $tmp = preg_replace('/[=£\*E27]+/', '', $tmp);
        $tmp = preg_replace('/\.{2,}/', '', $tmp);
        
        // Eliminar caracteres no permitidos en nombres
        $tmp = preg_replace('/[^\w\sáéíóúñÁÉÍÓÚÑ\.\-]/u', '', $tmp);
        
        return trim($tmp);
    }

    private function inferYear(int $d1, int $m1, int $d2, int $m2): int
    {
        $y = Carbon::now()->year;
        $inicio = Carbon::create($y, $m1, $d1);
        $fin = Carbon::create($y, $m2, $d2);
        return $fin->lessThan($inicio) ? $y + 1 : $y;
    }
}
