<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Carbon\Carbon;

class TurnosPdfScraper
{
    private const BASE_URL = 'https://colfarsfe.org.ar';
    private const TURNOS_PAGE_URL = 'https://colfarsfe.org.ar/farmacias/';

    /**
     * Extrae la URL del PDF más reciente desde la página del colegio
     */
    public function extractPdfUrl(): array
    {
        try {
            Log::info('Iniciando scraping de URL del PDF de turnos');

            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            ];

            // Estrategias múltiples
            $strategies = [
                fn() => $this->scrapeFromFarmaciasPage($headers),
                fn() => $this->scrapeFromHomePage($headers),
                fn() => $this->scrapeFromWpUploads($headers), // Predicción directa
            ];

            foreach ($strategies as $index => $strategy) {
                try {
                    $result = $strategy();
                    if ($result['success']) {
                        Log::info("✅ Estrategia " . ($index + 1) . " exitosa: " . $result['url']);
                        return $result;
                    }
                } catch (\Exception $e) {
                    Log::warning("Estrategia " . ($index + 1) . " falló: " . $e->getMessage());
                    continue;
                }
            }

            throw new \Exception('No se pudo extraer la URL del PDF con ninguna estrategia');
        } catch (\Exception $e) {
            Log::error('Error al extraer URL del PDF', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'url' => null,
                'message' => "Error: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Estrategia 1: Scraping de /farmacias/
     */
    private function scrapeFromFarmaciasPage(array $headers): array
    {
        Log::info('Estrategia 1: Scraping de /farmacias/');

        $response = Http::withHeaders($headers)->timeout(30)->get(self::TURNOS_PAGE_URL);

        if (!$response->successful()) {
            throw new \Exception("HTTP {$response->status()}");
        }

        $crawler = new Crawler($response->body());
        $pdfUrl = $this->findPdfLink($crawler);

        if (!$pdfUrl) throw new \Exception('No se encontró PDF en el HTML');

        return [
            'success' => true,
            'url' => $this->normalizeUrl($pdfUrl),
            'message' => 'URL extraída desde /farmacias/',
        ];
    }

    /**
     * Estrategia 2: Scraping de página principal
     */
    private function scrapeFromHomePage(array $headers): array
    {
        Log::info('Estrategia 2: Scraping de página principal');

        $response = Http::withHeaders($headers)->timeout(30)->get(self::BASE_URL);

        if (!$response->successful()) {
            throw new \Exception("HTTP {$response->status()}");
        }

        $crawler = new Crawler($response->body());
        $pdfUrl = $this->findPdfLink($crawler);

        if (!$pdfUrl) throw new \Exception('No se encontró PDF en Home');

        return [
            'success' => true,
            'url' => $this->normalizeUrl($pdfUrl),
            'message' => 'URL extraída desde página principal',
        ];
    }

    /**
     * Estrategia 3: Predicción de URL en wp-content/uploads
     * (MEJORADA: Usa array fijo de meses para evitar error de idioma)
     */
    private function scrapeFromWpUploads(array $headers): array
    {
        Log::info('Estrategia 3: Predicción de URL');

        $year = now()->year;
        $month = now()->format('m');

        // Array fijo para garantizar español sin depender del servidor
        $meses = [
            '01' => 'Enero',
            '02' => 'Febrero',
            '03' => 'Marzo',
            '04' => 'Abril',
            '05' => 'Mayo',
            '06' => 'Junio',
            '07' => 'Julio',
            '08' => 'Agosto',
            '09' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre'
        ];

        $monthName = $meses[$month];

        $possibleUrls = [
            // Variantes comunes observadas en el colegio
            "https://colfarsfe.org.ar/wp-content/uploads/{$year}/{$month}/Afiche-Turnos-{$monthName}-{$year}.pdf",
            "https://colfarsfe.org.ar/wp-content/uploads/{$year}/{$month}/Afiche-de-Turnos-{$monthName}-{$year}.pdf",
            "https://colfarsfe.org.ar/wp-content/uploads/{$year}/{$month}/Afiche-de-Turnos-{$monthName}-{$year}-1.pdf",
            "https://colfarsfe.org.ar/wp-content/uploads/{$year}/{$month}/Turnos-{$monthName}-{$year}.pdf",
        ];

        foreach ($possibleUrls as $url) {
            try {
                Log::info("Probando URL: $url"); // Log para ver qué está intentando
                $response = Http::withHeaders($headers)->timeout(5)->head($url);

                if ($response->successful() && str_contains($response->header('Content-Type'), 'pdf')) {
                    Log::info('PDF encontrado por predicción', ['url' => $url]);
                    return [
                        'success' => true,
                        'url' => $url,
                        'message' => 'URL predicha exitosamente',
                    ];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        throw new \Exception('No se encontró PDF por predicción');
    }

    /**
     * Busca el enlace al PDF en el HTML
     */
    private function findPdfLink(Crawler $crawler): ?string
    {
        $strategies = [
            // 1. Enlace que contenga "Afiche" y "Turno"
            fn() => collect($crawler->filter('a[href*=".pdf"]')->each(fn($node) => $node->attr('href')))
                ->first(fn($href) => stripos($href, 'afiche') !== false && stripos($href, 'turno') !== false),

            // 2. Data ID específico (puede cambiar, es frágil)
            fn() => $crawler->filter('[data-id="7dce977"] a[href*=".pdf"]')->count() > 0
                ? $crawler->filter('[data-id="7dce977"] a[href*=".pdf"]')->first()->attr('href')
                : null,

            // 3. Cualquier PDF con "Turno" en el nombre (excluyendo inspección/guía)
            fn() => collect($crawler->filter('a[href*=".pdf"]')->each(fn($node) => $node->attr('href')))
                ->filter(fn($href) => stripos($href, 'turno') !== false)
                ->filter(fn($href) => stripos($href, 'inspeccion') === false)
                ->filter(fn($href) => stripos($href, 'guia') === false)
                ->first(),

            // 4. Buscar por imagen "Farmacias-de-turno"
            fn() => $crawler->filter('img[src*="Farmacias-de-turno"]')->count() > 0
                ? $crawler->filter('img[src*="Farmacias-de-turno"]')->ancestors()->filter('a[href*=".pdf"]')->first()->attr('href')
                : null
        ];

        foreach ($strategies as $strategy) {
            try {
                if ($link = $strategy()) return $link;
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    private function normalizeUrl(string $url): string
    {
        if (parse_url($url, PHP_URL_SCHEME)) return $url;
        if (str_starts_with($url, '//')) return 'https:' . $url;
        return str_starts_with($url, '/') ? self::BASE_URL . $url : self::BASE_URL . '/' . $url;
    }

    public function getPdfMetadata(string $url): ?array
    {
        try {
            $response = Http::timeout(5)->head($url);
            return $response->successful() ? [
                'content_type' => $response->header('Content-Type'),
                'content_length' => $response->header('Content-Length'),
                'last_modified' => $response->header('Last-Modified'),
            ] : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
