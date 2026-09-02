<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class TurnosPdfScraper
{
    private const BASE_URL = 'https://colfarsfe.org.ar';
    private const TURNOS_PAGE_URL = 'https://colfarsfe.org.ar/farmacias/';

    /**
     * Extrae la URL del PDF correspondiente al mes solicitado.
     */
    public function extractPdfUrl(?Carbon $fechaObjetivo = null): array 
    {
        try {
            $fechaObjetivo ??= now();

            $mes = strtolower(
                $fechaObjetivo->translatedFormat('F')
            );

            $anio = $fechaObjetivo->year;

            Log::info('Iniciando scraping de URL del PDF de turnos',
                [ 'mes' => $mes,
                  'anio' => $anio ]);

            $headers = [
                'User-Agent' =>
                    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) ' .
                    'AppleWebKit/537.36 (KHTML, like Gecko) ' .
                    'Chrome/120.0.0.0 Safari/537.36',

                'Accept' =>
                    'text/html,application/xhtml+xml,application/xml;' .
                    'q=0.9,image/avif,image/webp,*/*;q=0.8',

                'Accept-Language' =>
                    'es-AR,es;q=0.9',
            ];

            $strategies = [
                fn() =>$this->scrapeFromFarmaciasPage($headers, $mes, $anio),
                fn() =>$this->scrapeFromHomePage($headers, $mes, $anio),
                fn() =>$this->scrapeFromWpUploads($headers, $fechaObjetivo),
            ];

            foreach ($strategies as $index => $strategy) {
                try {
                    $result = $strategy();
                    if ($result['success']) {
                        Log::info('Estrategia de scraping exitosa',
                            [ 'estrategia' => $index + 1,
                              'url' => $result['url'] ]);
                        return $result;
                    }
                } catch (\Throwable $e) {
                    Log::warning('Estrategia de scraping falló',
                        [ 'estrategia' => $index + 1,
                          'error' => $e->getMessage() ]);
                }
            }

            return [
                'success' => false,
                'status' => 'not_published',
                'url' => null,
                'message' =>
                    "Todavía no se encontró el PDF de {$mes} {$anio}.",
            ];

        } catch (\Throwable $e) {
            Log::error('Error al extraer URL del PDF', [
                'error' => $e->getMessage(),
                ]);

            return [
                'success' => false,
                'status' => 'error',
                'url' => null,
                'message' => "Error: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Estrategia 1: Scraping de /farmacias/
     */
    private function scrapeFromFarmaciasPage(array $headers, string $mes, int $anio): array 
    {
        Log::info('Estrategia 1: Scraping de /farmacias/');

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->get(self::TURNOS_PAGE_URL);

        if (!$response->successful()) {
            throw new \Exception("HTTP {$response->status()}");
        }

        $crawler = new Crawler($response->body());
        $pdfUrl = $this->findPdfLink($crawler, $mes, $anio);

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
    private function scrapeFromHomePage(array $headers, string $mes, int $anio): array 
    {
        Log::info('Estrategia 2: Scraping de página principal');

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->get(self::BASE_URL);

        if (!$response->successful()) {
            throw new \Exception("HTTP {$response->status()}");
        }

        $crawler = new Crawler($response->body());
        $pdfUrl = $this->findPdfLink($crawler, $mes, $anio);

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
    private function scrapeFromWpUploads( array $headers, Carbon $fechaObjetivo): array 
    {
        Log::info('Estrategia 3: Predicción de URL');

        $year = $fechaObjetivo->year;
        $month = $fechaObjetivo->format('m');
        $monthName = ucfirst($fechaObjetivo->translatedFormat('F'));

        $possibleUrls = [
            "https://colfarsfe.org.ar/wp-content/uploads/{$year}/{$month}/Afiche-de-Turnos-{$monthName}-{$year}-1.pdf",
            "https://colfarsfe.org.ar/wp-content/uploads/{$year}/{$month}/Afiche-de-Turnos-{$monthName}-{$year}.pdf",
            "https://colfarsfe.org.ar/wp-content/uploads/{$year}/{$month}/Afiche-Turnos-{$monthName}-{$year}.pdf",
            "https://colfarsfe.org.ar/wp-content/uploads/{$year}/{$month}/Turnos-{$monthName}-{$year}.pdf",

            // Formato detectado en el PDF real de agosto 2026
            "https://colfarsfe.org.ar/wp-content/uploads/{$year}/{$month}/AficheTurnos_{$year}{$month}_Final.pdf",
        ];

        foreach ($possibleUrls as $url) {
            try {
                $response = Http::withHeaders($headers)->timeout(10)->head($url);

                if ($response->successful() && str_contains(strtolower($response->header('Content-Type', '')), 'pdf')) {
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
    private function findPdfLink(Crawler $crawler, string $mes,int $anio): ?string 
    {
        $pdfLinks = collect(
            $crawler
                ->filter('a[href*=".pdf"]')
                ->each(fn($node) => $node->attr('href'))
        )
            ->filter()
            ->unique();

        Log::info('PDFs encontrados en la página', [
            'mes_buscado' => $mes,
            'anio_buscado' => $anio,
            'enlaces' => $pdfLinks->values()->all(),
        ]);

        $mesNumero = str_pad(
            (string) Carbon::createFromDate($anio, 1, 1)
                ->month($this->monthNumber($mes))
                ->month,
            2,
            '0',
            STR_PAD_LEFT
        );

        // Estrategia 1: "Afiche" + "Turno" + mes + año

        $link = $pdfLinks->first(
            function ($href) use ($mes, $anio) {

                $texto = strtolower($href);

                return str_contains($texto, 'afiche') &&
                    str_contains($texto, 'turno') &&
                    str_contains($texto, $mes) &&
                    str_contains($texto, (string) $anio);
            }
        );

        if ($link) {
            return $link;
        }

        // Estrategia 2: data-id específico

        try {

            $link = $crawler
                ->filter('[data-id="7dce977"] a[href*=".pdf"]')
                ->first()
                ->attr('href');

            if ($link) {

                $texto = strtolower($link);

                if (
                    str_contains($texto, $mes) ||
                    str_contains($texto, (string) $anio) ||
                    str_contains($texto, "{$anio}{$mesNumero}")
                ) {
                    return $link;
                }
            }

        } catch (\Exception $e) {
            // Mantener búsqueda con las demás estrategias.
        }

        // Estrategia 3: "Turno", excluyendo "Inspeccion" y "guia"

        $link = $pdfLinks
            ->filter(
                fn($href) =>
                str_contains(strtolower($href), 'turno')
            )
            ->filter(fn($href) => !str_contains(strtolower($href), 'inspeccion'))
            ->filter(fn($href) => !str_contains(strtolower($href), 'guia'))
            ->filter(
                function ($href) use ($mes, $anio) {

                    $texto = strtolower($href);

                    return str_contains($texto, $mes) ||
                        str_contains($texto, (string) $anio) ||
                        str_contains($texto, "{$anio}{$mesNumero}");
                }
            )
            ->first();

        if ($link) {
            return $link;
        }

        // Estrategia 4: Imagen específica "Farmacias-de-turno.png"

        try {
            $link = $crawler
                ->filter('img[src*="Farmacias-de-turno.png"]')
                ->ancestors()
                ->filter('a[href*=".pdf"]')
                ->first()
                ->attr('href');

            if ($link) {

                $texto = strtolower($link);

                if (
                    str_contains($texto, $mes) ||
                    str_contains($texto, (string) $anio) ||
                    str_contains($texto, "{$anio}{$mesNumero}")
                ) {
                    return $link;
                }
            }

        } catch (\Exception $e) {
            // Mantener búsqueda con las demás estrategias.
        }

        // Estrategia 5: PDF del mes/año solicitado

        $link = $pdfLinks->first(
            function ($href) use ($mes, $anio, $mesNumero) {

                $texto = strtolower($href);

                return (
                    str_contains($texto, $mes) ||
                    str_contains($texto, "{$anio}-{$mesNumero}") ||
                    str_contains($texto, "{$anio}{$mesNumero}")
                );
            }
        );

        if ($link) {
            return $link;
        }

        return null;
    }

    /**
     * Obtiene el número de mes a partir del nombre.
     */
    private function monthNumber(string $mes): int
    {
        return match ($mes) {

            'enero' => 1,
            'febrero' => 2,
            'marzo' => 3,
            'abril' => 4,
            'mayo' => 5,
            'junio' => 6,
            'julio' => 7,
            'agosto' => 8,
            'septiembre' => 9,
            'octubre' => 10,
            'noviembre' => 11,
            'diciembre' => 12,

            default => throw new \InvalidArgumentException(
                "Mes inválido: {$mes}"
            ),
        };
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
}
