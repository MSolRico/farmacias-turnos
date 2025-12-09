<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TurnosPdfDownloader
{
    private const STORAGE_PATH = 'turnos/pdfs';

    public function __construct(
        private TurnosPdfScraper $scraper
    ) {}

    /**
     * Obtiene la ruta del último PDF descargado localmente (para modo offline/local)
     */
    public function getLatestLocalPdfPath(): ?string
    {
        $files = Storage::files(self::STORAGE_PATH);

        if (empty($files)) {
            return null;
        }

        // Ordenar por fecha de modificación (el más nuevo primero)
        $latest = collect($files)
            ->sortByDesc(fn($file) => Storage::lastModified($file))
            ->first();

        return Storage::path($latest);
    }

    public function downloadLatest(bool $force = false): array
    {
        try {
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
            $filename = $this->getFilenameFromUrl($pdfUrl);
            $localRelativePath = self::STORAGE_PATH . '/' . $filename;

            if (!$force && Storage::exists($localRelativePath)) {
                $msg = "El archivo '{$filename}' ya existe. Se omite la descarga.";
                Log::info("CRON: {$msg}");

                return [
                    'success' => true,
                    'skipped' => true,
                    'path' => Storage::path($localRelativePath),
                    'url' => $pdfUrl,
                    'message' => $msg,
                ];
            }

            Log::info('CRON: Iniciando descarga...', ['url' => $pdfUrl]);
            return $this->download($pdfUrl, $force);
        } catch (\Exception $e) {
            Log::error('Error en downloadLatest', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'path' => null,
                'message' => "Error: {$e->getMessage()}",
            ];
        }
    }

    public function download(string $url, bool $force = false): array
    {
        try {
            $response = Http::timeout(60)
                ->retry(3, 1000)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get($url);

            if (!$response->successful()) {
                throw new \Exception("HTTP {$response->status()}");
            }

            $filename = $this->getFilenameFromUrl($url);
            $path = self::STORAGE_PATH . '/' . $filename;

            Storage::put($path, $response->body());
            $absolutePath = Storage::path($path);

            return [
                'success' => true,
                'skipped' => false,
                'path' => $absolutePath,
                'url' => $url,
                'message' => "PDF descargado exitosamente",
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'path' => null,
                'message' => "Error: {$e->getMessage()}",
            ];
        }
    }

    private function getFilenameFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        return preg_replace('/[^a-zA-Z0-9\-\_\.]/', '', basename($path));
    }

    public function cleanOldPdfs(int $keepLast = 10): void
    {
        $files = collect(Storage::files(self::STORAGE_PATH))
            ->sortByDesc(fn($file) => Storage::lastModified($file))
            ->skip($keepLast);

        foreach ($files as $file) {
            Storage::delete($file);
        }
    }
}
