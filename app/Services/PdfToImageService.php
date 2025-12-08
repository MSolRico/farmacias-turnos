<?php

namespace App\Services;

class PdfToImageService
{
    protected string $popplerPath;

    public function __construct()
    {
        // Ruta a pdftoppm.exe de Poppler
        $this->popplerPath = 'C:\poppler\Library\bin\pdftoppm.exe';
    }

    /**
     * Convierte el PDF a PNG y genera 4 recortes (3 Santa Fe + 1 Santo Tomé)
     */
    public function convertToImage(string $pdfPath): ?array
    {
        if (!file_exists($pdfPath)) {
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
            \Log::error("Error convirtiendo PDF a imagen: exit code {$exit}");
            return null;
        }

        $fullImagePath = $outputBase . '.png';

        if (!file_exists($fullImagePath)) {
            \Log::error("Archivo PNG no generado: {$fullImagePath}");
            return null;
        }

        // Preprocesar imagen para mejorar OCR
        $this->preprocessImage($fullImagePath);

        // Crear 4 columnas
        return $this->splitIntoColumns($fullImagePath);
    }

    /**
     * Preprocesa la imagen para mejorar la precisión del OCR
     */
    private function preprocessImage(string $imagePath): void
    {
        if (!extension_loaded('gd')) {
            \Log::warning("GD no está disponible, saltando preprocesamiento de imagen");
            return;
        }

        try {
            $image = @imagecreatefrompng($imagePath);
            if (!$image) {
                \Log::warning("No se pudo cargar imagen para preprocesar: {$imagePath}");
                return;
            }

            // 1. Convertir a escala de grises
            imagefilter($image, IMG_FILTER_GRAYSCALE);

            // 2. Aumentar contraste (ayuda a distinguir texto del fondo)
            imagefilter($image, IMG_FILTER_CONTRAST, -40);

            // 3. Aumentar brillo ligeramente
            imagefilter($image, IMG_FILTER_BRIGHTNESS, 10);

            // 4. Aplicar nitidez para texto más definido
            $matrix = [
                [-1, -1, -1],
                [-1, 16, -1],
                [-1, -1, -1],
            ];
            imageconvolution($image, $matrix, 8, 0);

            // Guardar imagen mejorada
            imagepng($image, $imagePath, 0); // 0 = sin compresión para máxima calidad
            imagedestroy($image);
        } catch (\Throwable $e) {
            \Log::warning("Error preprocesando imagen: " . $e->getMessage());
        }
    }

    /**
     * Divide la imagen del afiche en 4 columnas:
     * 3 columnas = Santa Fe
     * 1 columna = Santo Tomé
     */
    private function splitIntoColumns(string $imagePath): ?array
    {
        if (!extension_loaded('gd')) {
            \Log::warning("GD no está disponible, no se pueden cortar columnas");
            return [$imagePath]; // fallback
        }

        $img = @imagecreatefrompng($imagePath);
        if (!$img) {
            \Log::error("No se pudo abrir imagen para cortar: {$imagePath}");
            return null;
        }

        $w = imagesx($img);
        $h = imagesy($img);

        // Pixeles de referencia que vos proporcionaste (para ancho = 4796)
        $refWidth = 4796;
        $cutsRef = [1228, 2286, 3454]; // cortes verticales (x positions) entre columnas

        // Si la imagen no tiene exactamente el ancho de referencia, escalar cortes proporcionalmente
        if ($w !== $refWidth && $refWidth > 0) {
            $scale = $w / $refWidth;
            $cuts = array_map(fn($x) => intval(round($x * $scale)), $cutsRef);
        } else {
            $cuts = $cutsRef;
        }

        // Calcular anchos de cada columna a partir de cortes
        $x0 = 0;
        $x1 = $cuts[0];
        $x2 = $cuts[1];
        $x3 = $cuts[2];
        $x4 = $w; // final

        $cols = [
            ['x' => $x0, 'w' => max(0, $x1 - $x0), 'suffix' => '_sf0'],
            ['x' => $x1, 'w' => max(0, $x2 - $x1), 'suffix' => '_sf1'],
            ['x' => $x2, 'w' => max(0, $x3 - $x2), 'suffix' => '_sf2'],
            ['x' => $x3, 'w' => max(0, $x4 - $x3), 'suffix' => '_st'],
        ];

        $paths = [];

        foreach ($cols as $i => $c) {
            // Si por alguna razón el ancho calculado es 0, saltar
            if ($c['w'] <= 0) {
                \Log::warning("Ancho de columna {$i} inválido ({$c['w']}px), se omite");
                continue;
            }

            $new = imagecreatetruecolor($c['w'], $h);

            // Mantener transparencia/alpha si es necesario
            imagealphablending($new, false);
            imagesavealpha($new, true);
            $transparent = imagecolorallocatealpha($new, 255, 255, 255, 127);
            imagefilledrectangle($new, 0, 0, $c['w'], $h, $transparent);

            imagecopy($new, $img, 0, 0, $c['x'], 0, $c['w'], $h);

            $colPath = preg_replace('/\.png$/i', $c['suffix'] . '.png', $imagePath);
            // Evitar sobrescribir si existe: agregar índice
            $idx = 0;
            $finalPath = $colPath;
            while (file_exists($finalPath)) {
                $idx++;
                $finalPath = preg_replace('/\.png$/i', $c['suffix'] . "_{$idx}.png", $imagePath);
            }

            imagepng($new, $finalPath, 0);
            imagedestroy($new);

            $paths[] = $finalPath;
            \Log::info("Columna creada: {$finalPath} (x={$c['x']}, w={$c['w']}, h={$h})");
        }

        imagedestroy($img);

        return $paths;
    }

    /**
     * Elimina archivos viejos en /temp
     */
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