<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class TurnosPdfScraper
{
    private const BASE_URL = 'https://colfarsfe.org.ar';
    private const TURNOS_PAGE_URL = 'https://colfarsfe.org.ar/farmacias/'; // ← URL CORRECTA

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
                'Accept-Language' => 'es-AR,es;q=0.9',
            ];

            // Estrategias múltiples
            $strategies = [
                fn() => $this->scrapeFromFarmaciasPage($headers),
                fn() => $this->scrapeFromHomePage($headers),
                fn() => $this->scrapeFromWpUploads($headers),
            ];

            foreach ($strategies as $index => $strategy) {
                try {
                    $result = $strategy();
                    if ($result['success']) {
                        Log::info("✅ Estrategia " . ($index + 1) . " exitosa");
                        return $result;
                    }
                } catch (\Exception $e) {
                    Log::warning("Estrategia " . ($index + 1) . " falló: " . $e->getMessage());
                    continue;
                }
            }

            throw new \Exception('No se pudo extraer la URL del PDF con ninguna estrategia');
        } catch (\Exception $e) {
            Log::error('Error al extraer URL del PDF', [
                'error' => $e->getMessage(),
            ]);

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

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->get(self::TURNOS_PAGE_URL);

        if (!$response->successful()) {
            throw new \Exception("HTTP {$response->status()}");
        }

        $crawler = new Crawler($response->body());
        $pdfUrl = $this->findPdfLink($crawler);

        if (!$pdfUrl) {
            throw new \Exception('No se encontró PDF');
        }

        $pdfUrl = $this->normalizeUrl($pdfUrl);

        Log::info('PDF encontrado en /farmacias/', ['url' => $pdfUrl]);

        return [
            'success' => true,
            'url' => $pdfUrl,
            'message' => 'URL extraída desde /farmacias/',
        ];
    }

    /**
 * Estrategia 2: Scraping de página principal
 */
    private function scrapeFromHomePage(array $headers): array
    {
        Log::info('Estrategia 2: Scraping de página principal');

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->get(self::BASE_URL);

        if (!$response->successful()) {
            throw new \Exception("HTTP {$response->status()}");
        }

        $crawler = new Crawler($response->body());
        $pdfUrl = $this->findPdfLink($crawler);

        if (!$pdfUrl) {
            throw new \Exception('No se encontró PDF');
        }

        $pdfUrl = $this->normalizeUrl($pdfUrl);

        return [
            'success' => true,
            'url' => $pdfUrl,
            'message' => 'URL extraída desde página principal',
        ];
    }

    /**
 * Estrategia 3: Predicción de URL en wp-content/uploads
 */
    private function scrapeFromWpUploads(array $headers): array
    {
        Log::info('Estrategia 3: Predicción de URL');

        $year = now()->year;
        $month = now()->format('m');
        $monthName = ucfirst(now()->translatedFormat('F'));

        $possibleUrls = [
            "https://colfarsfe.org.ar/wp-content/uploads/{$year}/{$month}/Afiche-de-Turnos-{$monthName}-{$year}-1.pdf",
            "https://colfarsfe.org.ar/wp-content/uploads/{$year}/{$month}/Afiche-de-Turnos-{$monthName}-{$year}.pdf",
            "https://colfarsfe.org.ar/wp-content/uploads/{$year}/{$month}/Afiche-Turnos-{$monthName}-{$year}.pdf",
            "https://colfarsfe.org.ar/wp-content/uploads/{$year}/{$month}/Turnos-{$monthName}-{$year}.pdf",
        ];

        foreach ($possibleUrls as $url) {
            try {
                $response = Http::withHeaders($headers)->timeout(10)->head($url);

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
            // Estrategia 1: SOLO PDFs con "Afiche" Y "Turno" en el nombre
            fn() => collect($crawler->filter('a[href*=".pdf"]')->each(fn($node) => $node->attr('href')))
                ->first(
                    fn($href) =>
                    str_contains(strtolower($href), 'afiche') &&
                        str_contains(strtolower($href), 'turno')
                ),

            // Estrategia 2: Por data-id específico (el elemento exacto del HTML)
            fn() => $crawler->filter('[data-id="7dce977"] a[href*=".pdf"]')->first()->attr('href'),

            // Estrategia 3: PDF con "Turno" en la URL (pero NO "Inspeccion" ni "guia")
            fn() => collect($crawler->filter('a[href*=".pdf"]')->each(fn($node) => $node->attr('href')))
                ->filter(fn($href) => str_contains(strtolower($href), 'turno'))
                ->filter(fn($href) => !str_contains(strtolower($href), 'inspeccion'))
                ->filter(fn($href) => !str_contains(strtolower($href), 'guia'))
                ->first(),

            // Estrategia 4: Imagen específica "Farmacias-de-turno.png"
            fn() => $crawler->filter('img[src*="Farmacias-de-turno.png"]')
                ->ancestors()
                ->filter('a[href*=".pdf"]')
                ->first()
                ->attr('href'),

            // Estrategia 5: PDF del mes actual en la URL
            fn() => collect($crawler->filter('a[href*=".pdf"]')->each(fn($node) => $node->attr('href')))
                ->first(
                    fn($href) =>
                    str_contains(strtolower($href), strtolower(now()->translatedFormat('F'))) ||
                        str_contains(strtolower($href), now()->format('Y-m'))
                ),
        ];

        foreach ($strategies as $index => $strategy) {
            try {
                $link = $strategy();
                if ($link) {
                    Log::debug("PDF encontrado con estrategia " . ($index + 1), ['url' => $link]);
                    return $link;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    /**
 * Normaliza la URL
 */
    private function normalizeUrl(string $url): string
    {
        if (parse_url($url, PHP_URL_SCHEME)) {
            return $url;
        }

        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }

        if (str_starts_with($url, '/')) {
            return self::BASE_URL . $url;
        }

        return self::BASE_URL . '/' . $url;
    }

    /**
 * Obtiene metadata del PDF
 */
    public function getPdfMetadata(string $url): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->head($url);

            if (!$response->successful()) {
                return null;
            }

            return [
                'content_type' => $response->header('Content-Type'),
                'content_length' => $response->header('Content-Length'),
                'last_modified' => $response->header('Last-Modified'),
            ];
        } catch (\Exception $e) {
            Log::warning('No se pudo obtener metadata', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
