<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TurnosPdfDownloader
{
    private const STORAGE_PATH = 'turnos/pdfs';
    private const CACHE_HOURS = 24;

    public function __construct(
        private TurnosPdfScraper $scraper
    ) {}

    /**
     * Descarga el PDF de turnos correspondiente al mes solicitado.
     *
     * @return array{success: bool, status: string, path: string|null, url: string|null, message: string}
     */
    public function downloadForMonth(Carbon $fechaObjetivo,bool $force = false): array 
    {
        try {
            $anio = $fechaObjetivo->year;
            $mes = $fechaObjetivo->month;

            $scraperResult = $this->scraper->extractPdfUrl(
                $fechaObjetivo
            );

            if (!$scraperResult['success']) {
                return [
                    'success' => false,
                    'status' => $scraperResult['status'] ?? 'error',
                    'path' => null,
                    'url' => null,
                    'message' => $scraperResult['message'],
                ];
            }

            $pdfUrl = $scraperResult['url'];
            Log::info('URL del PDF obtenida', [
                'url' => $pdfUrl,
                'mes' => $mes,
                'anio' => $anio,
            ]);
            return $this->download($pdfUrl, $fechaObjetivo, $force);
        } catch (\Throwable $e) {
            Log::error('Error en downloadForMonth', [
                'error' => $e->getMessage(),
                'mes' => $fechaObjetivo->month,
                'anio' => $fechaObjetivo->year,
            ]);

            return [
                'success' => false,
                'status' => 'error',
                'path' => null,
                'url' => null,
                'message' => "Error: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Descarga el PDF desde una URL específica
     */
    public function download(string $url, Carbon $fechaObjetivo, bool $force = false): array 
    {
        try {
            // Verificar cache
            if (!$force && ($recentFile = $this->getRecentPdf($fechaObjetivo))) {
                return [
                    'success' => true,
                    'status' => 'cached',
                    'path' => $recentFile,
                    'url' => $url,
                    'message' => 'Se utilizará el PDF reciente existente.',
                ];
            }

            Log::info('Descargando PDF', [
                'url' => $url,
                'mes' => $fechaObjetivo->month,
                'anio' => $fechaObjetivo->year,
            ]);

            // Descargar con timeout y retry
            $response = Http::timeout(60)
                ->retry(3, 1000)
                ->withHeaders([
                    'User-Agent' =>
                        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) ' .
                        'AppleWebKit/537.36 (KHTML, like Gecko) ' .
                        'Chrome/120.0.0.0 Safari/537.36',
                ])
                ->get($url);

            if (!$response->successful()) {
                throw new \RuntimeException("HTTP {$response->status()}");
            }

            // Validar contenido
            if (!$this->isPdfContent($response)) {
                throw new \RuntimeException('El contenido descargado no es un PDF válido');
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
                'status' => 'downloaded',
                'path' => $absolutePath, // ← Retornar ruta normalizada
                'url' => $url,
                'message' => "PDF descargado exitosamente ({$fileSizeMB} MB)",
            ];
        } catch (\Throwable $e) {
            Log::error('Error al descargar PDF', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'error',
                'path' => null,
                'url' => $url,
                'message' => "Error: {$e->getMessage()}",
            ];
        }
    }

    // Busca un PDF reciente correspondiente al mes solicitado.
    private function getRecentPdf(Carbon $fechaObjetivo): ?string 
    {
        $files = Storage::files(self::STORAGE_PATH);

        if (empty($files)) {
            return null;
        }

        $periodo = sprintf('%04d%02d', $fechaObjetivo->year, $fechaObjetivo->month);

        $latestFile = collect($files)
            ->filter(fn($file) => str_contains(basename($file), $periodo))
            ->sortByDesc(fn($file) => Storage::lastModified($file))
            ->first();

        if (!$latestFile) return null;

        $lastModified = Storage::lastModified($latestFile);
        $hoursSince = now()->diffInHours(Carbon::createFromTimestamp($lastModified));

        return $hoursSince < self::CACHE_HOURS
            ? Storage::path($latestFile)
            : null;
    }

    // Verifica que la respuesta corresponda realmente a un PDF.
    private function isPdfContent($response): bool
    {
        $contentType = strtolower($response->header('Content-Type', ''));
        $body = $response->body();
        return (str_contains($contentType, 'pdf') || str_contains($contentType, 'octet-stream'))
        && preg_match('/%PDF-/', substr($body, 0, 8));
    }

    // Genera un nombre de archivo seguro y único.
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

    // Conserva los PDFs más recientes y elimina los restantes.
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
