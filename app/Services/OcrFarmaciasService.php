<?php

namespace App\Services;

use App\Services\OcrFarmaciasValidator;
use App\Services\TurnoDataPersister;
use App\Services\GeminiVisionOcrService;
use App\Helpers\OcrCleaner;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OcrFarmaciasService
{
    protected OcrFarmaciasValidator $validator;
    protected TurnoDataPersister $persister;
    protected GeminiVisionOcrService $vision;
    protected FarmaciaMatchingService $farmaciaMatching;

    public function __construct(
        OcrFarmaciasValidator $validator,
        TurnoDataPersister $persister,
        GeminiVisionOcrService $vision
    ) {
        $this->validator = $validator;
        $this->persister = $persister;
        $this->vision = $vision;
        $this->farmaciaMatching = app(FarmaciaMatchingService::class);
    }

    /**
     * Punto de entrada: recibe la ruta al PDF del afiche, lo convierte a
     * imágenes por columna y le pide a Gemini que extraiga cada columna
     * como JSON estructurado. Ya no pasa por Tesseract ni por limpieza de
     * texto por regex.
     */
    public function procesar(string $ruta): array
    {
        if (!file_exists($ruta)) {
            return ['error' => "No existe el archivo: $ruta"];
        }

        $imgService = app(PdfToImageService::class);
        $imagePaths = $imgService->convertToImage($ruta);

        if (!$imagePaths || !is_array($imagePaths) || count($imagePaths) === 0) {
            return ['error' => 'No se pudo convertir el PDF a imágenes con Poppler'];
        }

        $items = [];
        $columnasConError = 0;

        foreach ($imagePaths as $colPath) {
            Log::info("[OCR] Procesando columna con Gemini Vision: {$colPath}");

            $resultado = $this->vision->extraerDeImagen($colPath);

            if (isset($resultado['error'])) {
                Log::error("[OCR] Error extrayendo columna {$colPath}: {$resultado['error']}");
                $columnasConError++;
                continue;
            }

            $this->procesarResultadoColumna($resultado, $items);
        }

        @file_put_contents(
            storage_path('logs/ocr_last_items.json'),
            json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        if (empty($items)) {
            Log::warning('No se detectaron farmacias válidas en el afiche');
            return [
                'farmacias' => 0,
                'turnos' => 0,
                'columnas_con_error' => $columnasConError,
            ];
        }

        Log::info('Total de farmacias detectadas: ' . count($items));

        $stats = $this->persister->guardarEnBD($items);
        $stats['columnas_con_error'] = $columnasConError;

        return $stats;
    }

    /**
     * Toma el JSON que devolvió Gemini para UNA columna/imagen y lo
     * convierte a los items planos que espera TurnoDataPersister::guardarEnBD().
     */
    private function procesarResultadoColumna(array $resultado, array &$items): void
    {
        $ciudad = $resultado['ciudad'] ?? null;
        $ciudad = $this->normalizarCiudad($ciudad);

        foreach (($resultado['turnos'] ?? []) as $turno) {
            $fechaInicioRaw = trim((string) ($turno['fecha_inicio'] ?? ''));
            $fechaFinRaw = trim((string) ($turno['fecha_fin'] ?? ''));

            if (!$fechaInicioRaw || !$fechaFinRaw) {
                Log::warning('[OCR] Turno sin fechas válidas, se descarta: ' . json_encode($turno));
                continue;
            }

            $fechas = $this->parsearRangoFechas($fechaInicioRaw, $fechaFinRaw);
            if (!$fechas) {
                Log::warning("[OCR] No se pudieron interpretar las fechas '{$fechaInicioRaw}' - '{$fechaFinRaw}'");
                continue;
            }

            [$inicio, $fin] = $fechas;

            // Se arman los items de este turno en un array aparte para
            // poder aplicarles las excepciones ANTES de mezclarlos al
            // array general $items (así no hace falta buscar entre todos
            // los turnos ya procesados de otras columnas).
            $itemsDelTurno = [];

            foreach (($turno['farmacias'] ?? []) as $farmaciaRaw) {
                $this->procesarFarmacia($farmaciaRaw, $ciudad, $inicio, $fin, $itemsDelTurno);
            }

            $this->aplicarExcepciones($turno['excepciones'] ?? [], $itemsDelTurno);

            array_push($items, ...$itemsDelTurno);
        }
    }

    /**
     * Ajusta las fechas de las farmacias mencionadas en notas de excepción
     * (ej. "La farmacia Azanza estará de turno solo el 01/09 al 02/09"):
     * en vez de ignorar esa nota (como hacía la versión anterior), la
     * usamos para acotar el rango de fechas de esa farmacia puntual dentro
     * del turno, sin afectar al resto.
     */
    private function aplicarExcepciones(array $excepciones, array &$itemsDelTurno): void
    {
        foreach ($excepciones as $excepcion) {
            $nombreExcepcion = trim((string) ($excepcion['nombre_farmacia'] ?? ''));
            $fechaInicioRaw = trim((string) ($excepcion['fecha_inicio'] ?? ''));
            $fechaFinRaw = trim((string) ($excepcion['fecha_fin'] ?? ''));

            if ($nombreExcepcion === '' || $fechaInicioRaw === '' || $fechaFinRaw === '') {
                Log::warning('[OCR] Excepción incompleta, se ignora: ' . json_encode($excepcion));
                continue;
            }

            $fechas = $this->parsearRangoFechas($fechaInicioRaw, $fechaFinRaw);
            if (!$fechas) {
                Log::warning("[OCR] Excepción con fechas inválidas para '{$nombreExcepcion}': {$fechaInicioRaw}-{$fechaFinRaw}");
                continue;
            }
            [$inicioExcepcion, $finExcepcion] = $fechas;

            $matched = false;
            foreach ($itemsDelTurno as &$item) {
                if ($this->nombresCoinciden($nombreExcepcion, $item['nombre'])) {
                    Log::info("[OCR] Excepción aplicada: '{$item['nombre']}' pasa a turno {$inicioExcepcion->format('d/m')}-{$finExcepcion->format('d/m')} (nota: {$nombreExcepcion})");
                    $item['turn_dates'] = [$inicioExcepcion, $finExcepcion];
                    $item['notas'] = trim(($item['notas'] ?? '') . ' ' . ($excepcion['texto_original'] ?? ''));
                    $matched = true;
                }
            }
            unset($item);

            if (!$matched) {
                Log::warning("[OCR] La excepción menciona a '{$nombreExcepcion}' pero no coincide con ninguna farmacia ya extraída en este turno");
            }
        }
    }

    /**
     * Comparación tolerante a mayúsculas/tildes/espacios extra, para
     * matchear el nombre de la excepción contra el nombre ya extraído de
     * la lista de farmacias del mismo turno.
     */
    private function nombresCoinciden(string $a, string $b): bool
    {
        $normalizar = function (string $s): string {
            $s = mb_strtolower(trim($s), 'UTF-8');
            $s = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $s);
            return preg_replace('/[^a-z0-9]/', '', $s);
        };

        $a = $normalizar($a);
        $b = $normalizar($b);

        if ($a === '' || $b === '') {
            return false;
        }

        if ($a === $b || str_contains($a, $b) || str_contains($b, $a)) {
            return true;
        }

        $maxLen = max(strlen($a), strlen($b));
        $similitud = $maxLen > 0 ? (1 - levenshtein($a, $b) / $maxLen) * 100 : 0;

        return $similitud >= 80;
    }

    private function procesarFarmacia(array $farmaciaRaw, string $ciudad, Carbon $inicio, Carbon $fin, array &$items): void
    {
        $nombre = trim((string) ($farmaciaRaw['nombre'] ?? ''));
        $direccion = trim((string) ($farmaciaRaw['direccion'] ?? ''));
        $telefono = trim((string) ($farmaciaRaw['telefono'] ?? ''));

        if ($nombre === '') {
            return;
        }

        // Validación / normalización de teléfono (se mantiene como red de
        // seguridad aunque Gemini ya devuelve el campo separado).
        $telefonoValidado = $telefono !== '' ? $this->validator->validarTelefono($telefono) : null;

        if ($direccion !== '') {
            $direccion = OcrCleaner::normalizeAddress($direccion);
            $direccion = OcrCleaner::fixStreetNames($direccion);
        }
        [$direccion, $notas] = $direccion !== '' ? OcrCleaner::splitAddressNotes($direccion) : [null, null];

        if (!$telefonoValidado && (!$direccion || strlen($direccion) <= 5)) {
            Log::info("[Validación] Farmacia descartada - sin teléfono ni dirección válidos: {$nombre}");
            return;
        }

        $confianza = 90; // Gemini vision: confianza base alta, no hay heurística de OCR clásico que la baje
        $match = $this->farmaciaMatching->buscarCoincidencia($nombre, $ciudad, $direccion ?? '', $telefonoValidado ?? '');

        if ($match && $match['confianza'] >= 80) {
            Log::info("✅ MATCH EN BD: '{$nombre}' → '{$match['nombre_correcto']}' (confianza: {$match['confianza']}%)");
            $nombre = $match['nombre_correcto'];

            if (!$direccion && !empty($match['direccion_correcta'])) {
                $direccion = $match['direccion_correcta'];
            }
            if (!$telefonoValidado && !empty($match['telefono_correcto'])) {
                $telefonoValidado = $match['telefono_correcto'];
            }
        }

        $items[] = [
            'id_tmp' => Str::random(8),
            'nombre' => $nombre,
            'direccion' => $direccion ?? null,
            'telefono' => $telefonoValidado,
            'ciudad' => $ciudad,
            'notas' => $notas ?? null,
            'turn_dates' => [$inicio, $fin],
            'confianza' => $confianza,
        ];

        Log::info("✅ Farmacia VALIDADA: {$nombre} | {$direccion} | {$telefonoValidado}");
    }

    private function normalizarCiudad(?string $ciudad): string
    {
        if (!$ciudad) {
            return 'Santa Fe';
        }

        $upper = mb_strtoupper($ciudad, 'UTF-8');

        return str_contains($upper, 'SANTO TOM') ? 'Santo Tomé' : 'Santa Fe';
    }

    /**
     * Convierte 'DD/MM', 'DD/MM' a un par de Carbon, infiriendo el año con
     * la misma lógica que antes (si el fin es anterior al inicio, es año
     * que viene).
     */
    private function parsearRangoFechas(string $inicioRaw, string $finRaw): ?array
    {
        if (!preg_match('#^(\d{1,2})/(\d{1,2})$#', $inicioRaw, $m1)) {
            return null;
        }
        if (!preg_match('#^(\d{1,2})/(\d{1,2})$#', $finRaw, $m2)) {
            return null;
        }

        [$d1, $m1v] = [(int) $m1[1], (int) $m1[2]];
        [$d2, $m2v] = [(int) $m2[1], (int) $m2[2]];

        $year = $this->validator->inferYear($d1, $m1v, $d2, $m2v);

        try {
            return [
                Carbon::create($year, $m1v, $d1, 8, 0, 0),
                Carbon::create($year, $m2v, $d2, 8, 0, 0),
            ];
        } catch (\Throwable $e) {
            Log::warning("[OCR] Fecha inválida: {$inicioRaw} - {$finRaw}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Se mantiene por compatibilidad si algo externo llama directamente con
     * texto ya extraído (p.ej. tests). Ya no es el camino principal.
     */
    public function procesarTexto(string $textoBruto, ?string $ciudadDefault = 'Santa Fe'): array
    {
        Log::warning('[OCR] procesarTexto() es un método legacy — el pipeline actual usa Gemini Vision directo sobre la imagen, no texto.');
        return ['error' => 'procesarTexto ya no es el flujo soportado. Usá procesar($rutaPdf).'];
    }
}