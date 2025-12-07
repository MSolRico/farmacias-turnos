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
     * Convierte un PDF a PNG usando Poppler (pdftoppm) con preprocesamiento para mejor OCR
     */
    public function convertToImage(string $pdfPath): ?string
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

        $outputFile = $outputBase . '.png';

        if (!file_exists($outputFile)) {
            \Log::error("Archivo PNG no generado: {$outputFile}");
            return null;
        }

        // Preprocesar imagen para mejorar OCR
        $this->preprocessImage($outputFile);

        return $outputFile;
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
            $divisor = 8;
            $offset = 0;
            imageconvolution($image, $matrix, $divisor, $offset);

            // Guardar imagen mejorada
            imagepng($image, $imagePath, 0); // 0 = sin compresión para máxima calidad
            imagedestroy($image);

            \Log::info("Imagen preprocesada exitosamente: {$imagePath}");

        } catch (\Throwable $e) {
            \Log::warning("Error en preprocesamiento de imagen: " . $e->getMessage());
        }
    }

    /**
     * Limpia archivos temporales antiguos (más de 1 hora)
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