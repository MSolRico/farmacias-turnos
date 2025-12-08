<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TurnosPdfDownloader
{
    private const STORAGE_PATH = 'turnos/pdfs';
    private const CACHE_HOURS = 24;

    public function __construct(
        private TurnosPdfScraper $scraper
    ) {}

    /**
 * Descarga el PDF automáticamente desde la página del colegio
 *
 * @param bool $force Forzar descarga aunque exista uno reciente
 * @return array{success: bool, path: string|null, message: string, url: string|null}
 */
    public function downloadLatest(bool $force = false): array
    {
        try {
            // Extraer URL del PDF
            $scraperResult = $this->scraper->extractPdfUrl();

            if (!$scraperResult['success']) {
                return [
                    'success' => false,
                    'path' => null,
                    'url' => null,
                    'message' => $scraperResult['message'],
                ];
            }

            $pdfUrl = $scraperResult['url'];
            Log::info('URL del PDF obtenida', ['url' => $pdfUrl]);

            // Verificar metadata antes de descargar
            $metadata = $this->scraper->getPdfMetadata($pdfUrl);
            if ($metadata) {
                $sizeInMB = round((int)$metadata['content_length'] / 1024 / 1024, 2);
                Log::info('Metadata del PDF', [
                    'size' => "{$sizeInMB} MB",
                    'last_modified' => $metadata['last_modified'],
                ]);
            }

            // Descargar el PDF
            return $this->download($pdfUrl, $force);
        } catch (\Exception $e) {
            Log::error('Error en downloadLatest', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'path' => null,
                'url' => null,
                'message' => "Error: {$e->getMessage()}",
            ];
        }
    }

    /**
 * Descarga el PDF desde una URL específica
 */
    public function download(string $url, bool $force = false): array
    {
        try {
            // Verificar cache
            if (!$force && $recentFile = $this->getRecentPdf()) {
                return [
                    'success' => false,
                    'path' => $recentFile,
                    'url' => $url,
                    'message' => 'Ya existe un PDF reciente. Use force=true para descargar nuevamente.',
                ];
            }

            Log::info('Descargando PDF', ['url' => $url]);

            // Descargar con timeout y retry
            $response = Http::timeout(60)
                ->retry(3, 1000)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])
                ->get($url);

            if (!$response->successful()) {
                throw new \Exception("HTTP {$response->status()}");
            }

            // Validar contenido
            if (!$this->isPdfContent($response)) {
                throw new \Exception('El contenido descargado no es un PDF válido');
            }

            // Guardar
            $filename = $this->generateFilename($url);
            $path = self::STORAGE_PATH . '/' . $filename;
            Storage::put($path, $response->body());

            $fileSize = Storage::size($path);
            $fileSizeMB = round($fileSize / 1024 / 1024, 2);

            // Obtener ruta absoluta normalizada
            $absolutePath = Storage::path($path); // ← Usar Storage::path()

            // Limpiar PDFs antiguos
            $this->cleanOldPdfs();

            Log::info('PDF descargado exitosamente', [
                'filename' => $filename,
                'size' => "{$fileSizeMB} MB",
                'url' => $url,
                'path' => $absolutePath,
            ]);

            return [
                'success' => true,
                'path' => $absolutePath, // ← Retornar ruta normalizada
                'url' => $url,
                'message' => "PDF descargado exitosamente ({$fileSizeMB} MB)",
            ];
        } catch (\Exception $e) {
            Log::error('Error al descargar PDF', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'path' => null,
                'url' => $url,
                'message' => "Error: {$e->getMessage()}",
            ];
        }
    }

    private function getRecentPdf(): ?string
    {
        $files = Storage::files(self::STORAGE_PATH);

        if (empty($files)) {
            return null;
        }

        $latestFile = collect($files)
            ->sortByDesc(fn($file) => Storage::lastModified($file))
            ->first();

        $lastModified = Storage::lastModified($latestFile);
        $hoursSince = now()->diffInHours(Carbon::createFromTimestamp($lastModified));

        return $hoursSince < self::CACHE_HOURS ? storage_path('app/' . $latestFile) : null;
    }

    private function isPdfContent($response): bool
    {
        $contentType = $response->header('Content-Type');
        $body = $response->body();
        return (str_contains($contentType, 'pdf') || str_contains($contentType, 'octet-stream'))
            && preg_match('/%PDF-/', substr($body, 0, 8));
    }

    private function generateFilename(string $url): string
    {
        // Extraer nombre del archivo original si es posible
        $urlPath = parse_url($url, PHP_URL_PATH);
        $originalName = pathinfo($urlPath, PATHINFO_FILENAME);

        // Sanitizar y agregar timestamp
        $safeName = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $originalName);
        $timestamp = now()->format('Y-m-d_His');

        return "{$safeName}_{$timestamp}.pdf";
    }

    public function cleanOldPdfs(int $keepLast = 10): void
    {
        $files = collect(Storage::files(self::STORAGE_PATH))
            ->sortByDesc(fn($file) => Storage::lastModified($file))
            ->skip($keepLast);

        foreach ($files as $file) {
            Storage::delete($file);
            Log::info('PDF antiguo eliminado', ['file' => $file]);
        }
    }
}
