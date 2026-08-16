<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class PdfToImageService
{
    protected string $popplerPath;

    // Fallback si la detección automática de columnas falla (imagen rara,
    // afiche todo blanco, etc.) — mismos valores que antes, calculados
    // sobre este ancho de referencia.
    private const FALLBACK_REF_WIDTH = 4796;
    private const FALLBACK_CUTS_REF = [1228, 2286, 3454];

    public function __construct()
    {
        $this->popplerPath = env('POPPLER_PDFTOPPM_PATH', 'C:\poppler\Library\bin\pdftoppm.exe');
    }

    public function convertToImage(string $pdfPath): ?array
    {
        if (!file_exists($pdfPath)) {
            return null;
        }

        if (!file_exists($this->popplerPath)) {
            Log::error("No se encontró pdftoppm en la ruta configurada: {$this->popplerPath}. Revisá POPPLER_PDFTOPPM_PATH en .env");
            return null;
        }

        $outputBase = storage_path('app/temp/ocr_' . uniqid());

        // Crear directorio temp si no existe
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // -png → exportar a PNG
        // -singlefile → genera 1 archivo y no archivos por pagina
        // -r 300 → 300 DPI para mejor calidad OCR
        $cmd = "\"{$this->popplerPath}\" -png -r 300 -singlefile \"$pdfPath\" \"$outputBase\"";

        exec($cmd, $output, $exit);

        if ($exit !== 0) {
            Log::error("Error convirtiendo PDF a imagen: exit code {$exit}");
            return null;
        }

        $fullImagePath = $outputBase . '.png';

        if (!file_exists($fullImagePath)) {
            Log::error("Archivo PNG no generado: {$fullImagePath}");
            return null;
        }

        return $this->splitIntoColumns($fullImagePath);
    }

    /**
     * Divide la imagen del afiche en columnas detectando automáticamente
     * los espacios en blanco entre ellas, en vez de asumir posiciones de
     * píxel fijas. Así, si el Colegio de Farmacéuticos cambia el diseño
     * del afiche (más o menos columnas, otro ancho de margen), esto se
     * adapta solo.
     *
     * Si la detección automática no encuentra una cantidad razonable de
     * columnas (imagen atípica, todo blanco, etc.), cae al recorte fijo
     * anterior como red de seguridad.
     */
    private function splitIntoColumns(string $imagePath): ?array
    {
        if (!extension_loaded('gd')) {
            Log::warning('GD no está disponible, no se pueden cortar columnas');
            return [$imagePath];
        }

        $img = @imagecreatefrompng($imagePath);
        if (!$img) {
            Log::error("No se pudo abrir imagen para cortar: {$imagePath}");
            return null;
        }

        $w = imagesx($img);
        $h = imagesy($img);

        $cuts = $this->detectColumnCuts($img, $w, $h);

        if (empty($cuts)) {
            Log::warning('Detección automática de columnas no encontró cortes válidos, usando recorte fijo de respaldo');
            $cuts = $this->fallbackCuts($w);
        } else {
            Log::info('Cortes de columna detectados automáticamente: ' . implode(', ', $cuts));
        }

        $bounds = array_merge([0], $cuts, [$w]);
        $paths = [];

        for ($i = 0; $i < count($bounds) - 1; $i++) {
            $x = $bounds[$i];
            $colWidth = $bounds[$i + 1] - $x;

            if ($colWidth <= 0) {
                continue;
            }

            $new = imagecreatetruecolor($colWidth, $h);
            imagealphablending($new, false);
            imagesavealpha($new, true);
            $transparent = imagecolorallocatealpha($new, 255, 255, 255, 127);
            imagefilledrectangle($new, 0, 0, $colWidth, $h, $transparent);

            imagecopy($new, $img, 0, 0, $x, 0, $colWidth, $h);

            $colPath = preg_replace('/\.png$/i', "_col{$i}.png", $imagePath);
            $idx = 0;
            $finalPath = $colPath;
            while (file_exists($finalPath)) {
                $idx++;
                $finalPath = preg_replace('/\.png$/i', "_col{$i}_{$idx}.png", $imagePath);
            }

            imagepng($new, $finalPath, 0);
            imagedestroy($new);

            $paths[] = $finalPath;
            Log::info("Columna creada: {$finalPath} (x={$x}, w={$colWidth}, h={$h})");
        }

        imagedestroy($img);

        return $paths;
    }

    /**
     * Escanea la imagen en una grilla (muestreada, no píxel por píxel, por
     * performance) y busca franjas verticales que son casi enteramente
     * blancas de arriba a abajo: esos son los espacios entre columnas de
     * texto. Devuelve el punto medio de cada franja como corte.
     *
     * @return int[] posiciones X de corte, ordenadas de izquierda a derecha
     */
    private function detectColumnCuts($img, int $w, int $h): array
    {
        $strideX = max(1, intdiv($w, 1400));  // ~1400 columnas muestreadas
        $strideY = max(1, intdiv($h, 500));   // ~500 filas muestreadas por columna

        $whiteLuminanceThreshold = 245;  // 0-255, qué tan claro debe ser un píxel para contar como "fondo"
        $minWhiteFraction = 0.985;       // fracción de píxeles muestreados que deben ser "blancos" para que la columna sea un gap
        $minGapWidthPx = max(12, (int) round($w * 0.004)); // ancho mínimo de gap para no confundir con espacio entre letras

        $isGapAtX = [];

        for ($x = 0; $x < $w; $x += $strideX) {
            $white = 0;
            $total = 0;

            for ($y = 0; $y < $h; $y += $strideY) {
                $rgb = imagecolorat($img, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $luminance = 0.299 * $r + 0.587 * $g + 0.114 * $b;

                if ($luminance >= $whiteLuminanceThreshold) {
                    $white++;
                }
                $total++;
            }

            $isGapAtX[$x] = $total > 0 && ($white / $total) >= $minWhiteFraction;
        }

        $sampledXs = array_keys($isGapAtX);

        // Recortar bordes: no nos interesan los márgenes blancos del
        // principio/final de la hoja, solo los gaps ENTRE contenido.
        $firstContentX = null;
        $lastContentX = null;
        foreach ($sampledXs as $x) {
            if (!$isGapAtX[$x]) {
                if ($firstContentX === null) {
                    $firstContentX = $x;
                }
                $lastContentX = $x;
            }
        }

        if ($firstContentX === null) {
            // La imagen entera dio "blanca" según el umbral: algo anda mal,
            // que el caller decida usar el fallback.
            return [];
        }

        $cuts = [];
        $inGap = false;
        $gapStart = null;

        foreach ($sampledXs as $x) {
            if ($x <= $firstContentX || $x >= $lastContentX) {
                continue;
            }

            if ($isGapAtX[$x]) {
                if (!$inGap) {
                    $inGap = true;
                    $gapStart = $x;
                }
            } elseif ($inGap) {
                $gapEnd = $x - $strideX;
                if (($gapEnd - $gapStart) >= $minGapWidthPx) {
                    $cuts[] = intdiv($gapStart + $gapEnd, 2);
                }
                $inGap = false;
            }
        }

        // Sanity check: un afiche de este tipo va a tener entre 1 y 6
        // columnas razonablemente. Si la detección da un número
        // disparatado de cortes, algo se leyó mal (ruido, textura de
        // fondo, etc.) y es más seguro caer al recorte fijo.
        $columnCount = count($cuts) + 1;
        if ($columnCount < 2 || $columnCount > 6) {
            Log::warning("Detección automática dio {$columnCount} columnas (fuera de rango esperado 2-6), se descarta");
            return [];
        }

        return $cuts;
    }

    private function fallbackCuts(int $w): array
    {
        if ($w === self::FALLBACK_REF_WIDTH) {
            return self::FALLBACK_CUTS_REF;
        }

        $scale = $w / self::FALLBACK_REF_WIDTH;
        return array_map(fn($x) => (int) round($x * $scale), self::FALLBACK_CUTS_REF);
    }

    public function cleanOldTempFiles(): int
    {
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            return 0;
        }

        $deleted = 0;
        $files = glob($tempDir . '/ocr_*.png');
        $oneHourAgo = time() - 3600;

        foreach ($files as $file) {
            if (filemtime($file) < $oneHourAgo) {
                @unlink($file);
                $deleted++;
            }
        }

        return $deleted;
    }
}