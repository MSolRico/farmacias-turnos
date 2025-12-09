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
    protected $farmaciaMatching;

    public function __construct(
        OcrFarmaciasValidator $validator,
        TurnoDataPersister $persister
    ) {
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
                    ->executable('C:\Program Files\Tesseract-OCR\tesseract.exe') // Asegúrate que esta ruta sea correcta en tu .env o config
                    ->lang('spa')
                    ->psm(6)
                    ->oem(3);

                $textoBruto .= $ocr->run() . "\n";
            } catch (\Throwable $e) {
                \Log::warning("Error ejecutando Tesseract en columna $colPath: " . $e->getMessage());
            }
        }

        // 2) Limpieza y Procesamiento
        $textoBruto = $this->limpiezaLocalOCR($textoBruto);
        $textoLimpio = $textoBruto;

        // (Omitimos lógica de Gemini para simplificar, se mantiene la original si la tienes)

        return $this->procesarTexto($textoLimpio);
    }

    private function limpiezaLocalOCR(string $texto): string
    {
        // ... (Misma lógica de limpieza que ya tenías) ...
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

    public function procesarTexto(string $textoBruto, ?string $ciudadDefault = 'Santa Fe'): array
    {
        $texto = str_replace(["\r\n", "\r"], "\n", $textoBruto);
        $lines = explode("\n", $texto);

        $currentCity = null;
        $currentTurnDates = null;
        $items = [];

        foreach ($lines as $rawLine) {
            $line = trim($rawLine);
            if ($line === '' || strlen($line) < 5) continue; // Bajé el límite a 5 para detectar fechas cortas
            $upper = mb_strtoupper($line, 'UTF-8');

            // --- DETECCIÓN DE CIUDAD Y RESETEO DE VARIABLES (CRÍTICO) ---
            if (preg_match('/\bSANTA\s*FE\b/u', $upper)) {
                $currentCity = 'Santa Fe';
                $currentTurnDates = null; // <--- CORRECCIÓN: Reseteamos fechas para evitar sangrado
                \Log::info("Ciudad detectada: Santa Fe (Fechas reiniciadas)");
                continue;
            }
            if (preg_match('/\bSANTO\s*TOM(É|E)?\b/iu', $upper)) {
                $currentCity = 'Santo Tomé';
                $currentTurnDates = null; // <--- CORRECCIÓN: Reseteamos fechas para evitar sangrado
                \Log::info("Ciudad detectada: Santo Tomé (Fechas reiniciadas)");
                continue;
            }

            // FILTRO DE NOTAS
            if (preg_match('/(s[oó]lo|nota|excepci[oó]n|estará\s+de\s+turno|turno\s+especial)/iu', $line)) {
                continue;
            }

            // --- DETECCIÓN DE FECHAS MEJORADA ---
            $fechasCorregidas = OcrCleaner::extractAndFixDates($line);

            if (count($fechasCorregidas) > 0) {
                $nuevasFechas = [];

                // CASO A: Rango de fechas (Típico Santa Fe: "01/12 al 02/12")
                if (count($fechasCorregidas) >= 2 && count($fechasCorregidas) % 2 === 0) {
                    for ($i = 0; $i < count($fechasCorregidas); $i += 2) {
                        [$d1, $m1] = array_map('intval', explode('/', $fechasCorregidas[$i]));
                        [$d2, $m2] = array_map('intval', explode('/', $fechasCorregidas[$i + 1]));
                        $year = $this->validator->inferYear($d1, $m1, $d2, $m2);

                        $nuevasFechas[] = [
                            Carbon::create($year, $m1, $d1, 8, 0, 0),
                            Carbon::create($year, $m2, $d2, 8, 0, 0)
                        ];
                    }
                }
                // CASO B: Fecha única (Típico Santo Tomé o línea mixta: "08/12: Farmacia X")
                // Asumimos turno de 24hs (8am a 8am del día siguiente)
                elseif (count($fechasCorregidas) === 1) {
                    [$d1, $m1] = array_map('intval', explode('/', $fechasCorregidas[0]));
                    $year = $this->validator->inferYear($d1, $m1, $d1, $m1);

                    $inicio = Carbon::create($year, $m1, $d1, 8, 0, 0);
                    $fin = $inicio->copy()->addDay();

                    $nuevasFechas[] = [$inicio, $fin];
                }

                if (!empty($nuevasFechas)) {
                    $currentTurnDates = $nuevasFechas;
                    \Log::info("Fechas actualizadas: " . json_encode($fechasCorregidas));

                    // IMPORTANTE: Si la línea TIENE fechas PERO TAMBIÉN parece ser una farmacia,
                    // NO hacemos 'continue', dejamos que fluya para procesar la farmacia.
                    if (!$this->validator->esLineaValidaDeFarmacia($line)) {
                        continue;
                    }
                }
            }

            // IGNORAR BLOQUES NO FARMACIAS
            if (preg_match('/\b(URGENCIAS?|HOSPITAL|TOXICOLOGICAS?|ALASSIA|CULLEN)\b/i', $upper)) {
                continue;
            }

            // PROCESAR FARMACIA
            // Solo procesamos si tenemos fechas activas para asignar
            if ($currentTurnDates) {
                // Lógica de múltiples teléfonos en una línea
                $numTelefonos = preg_match_all('/\d{3,4}[\s\-=£\*E27]{0,3}\d{3,5}/', $line);
                if ($numTelefonos > 1) {
                    $partes = preg_split('/\s+(?=[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+\s+[A-Za-záéíóúñ])/', $line);
                    if (count($partes) < $numTelefonos) $partes = preg_split('/\s{3,}|—/', $line);

                    foreach ($partes as $parte) {
                        if (strlen(trim($parte)) > 10) {
                            $this->procesarLineaFarmacia($parte, $currentCity, $ciudadDefault, $currentTurnDates, $items);
                        }
                    }
                } else {
                    $this->procesarLineaFarmacia($line, $currentCity, $ciudadDefault, $currentTurnDates, $items);
                }
            }
        }

        if (empty($items)) {
            \Log::warning("No se detectaron farmacias válidas en el OCR");
            return ['farmacias' => 0, 'turnos' => 0];
        }

        return $this->persister->guardarEnBD($items);
    }

    private function procesarLineaFarmacia(string $line, ?string $currentCity, string $ciudadDefault, ?array $currentTurnDates, array &$items): void
    {
        if (!$this->validator->esLineaValidaDeFarmacia($line)) return;

        $telefono = OcrCleaner::fixPhone($line);
        $direccion = $this->validator->extractAddress($line);
        $nombreSucio = $this->validator->extractName($line, $direccion, $telefono);

        $matchResult = $this->validator->limpiarNombreFarmacia($nombreSucio);
        if (!$matchResult || empty($matchResult['nombre'])) return;

        $nombre = $matchResult['nombre'];
        $confianza = $matchResult['confianza'];

        // Filtros de calidad
        if ($confianza < 50 && preg_match('/[^a-zA-ZáéíóúñÁÉÍÓÚÑ\s\-]/', $nombre)) return;

        if ($telefono) $telefono = $this->validator->validarTelefono($telefono);

        // Validación mínima: debe tener dirección O teléfono O ser un match muy confiable
        if (empty($telefono) && (empty($direccion) || strlen($direccion) < 5)) {
            // Último intento: Si el nombre es MUY conocido en la BD, lo aceptamos igual
            $matchBD = $this->farmaciaMatching->buscarCoincidencia($nombre, $currentCity ?? $ciudadDefault);
            if (!$matchBD || $matchBD['confianza'] < 90) {
                return;
            }
        }

        $ciudad = $currentCity ?? $ciudadDefault;

        // Normalizar dirección
        $notas = null;
        if ($direccion) {
            $direccion = OcrCleaner::normalizeAddress($direccion);
            $direccion = OcrCleaner::fixStreetNames($direccion);
            [$direccion, $notas] = OcrCleaner::splitAddressNotes($direccion);
        }

        // Buscar coincidencia en BD
        $match = $this->farmaciaMatching->buscarCoincidencia($nombre, $ciudad);
        if ($match && $match['confianza'] >= 80) {
            $nombre = $match['nombre_correcto'];
            if (!$direccion && !empty($match['direccion_correcta'])) $direccion = $match['direccion_correcta'];
            if (!$telefono && !empty($match['telefono_correcto'])) $telefono = $match['telefono_correcto'];
        }

        foreach ($currentTurnDates as $turno) {
            $items[] = [
                'id_tmp'     => Str::random(8),
                'nombre'     => $nombre,
                'direccion'  => $direccion,
                'telefono'   => $telefono,
                'ciudad'     => $ciudad,
                'notas'      => $notas,
                'turn_dates' => $turno,
                'confianza'  => $confianza
            ];
        }
    }
}
