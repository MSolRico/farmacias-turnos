<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Reemplaza a Tesseract + toda la limpieza de texto por regex.
 *
 * En vez de "leer" la imagen con OCR clásico y después adivinar qué dice,
 * le mandamos la imagen directamente a Gemini y le pedimos que devuelva
 * el contenido YA estructurado en JSON. Esto elimina la necesidad de:
 *  - limpiezaLocalOCR() (parches de regex para basura de Tesseract)
 *  - extractName()/extractAddress() (separar campos de una línea de texto)
 *  - el diccionario $aliases de FarmaciaMatchingService (errores de OCR
 *    específicos de Tesseract que Gemini simplemente no comete)
 *
 * Requiere en .env:
 *   GEMINI_API_KEY=xxxx
 *   GEMINI_VISION_MODEL=gemini-3.6-flash   (opcional, este es el default)
 */
class GeminiVisionOcrService
{
    // Se prueban en orden si el primero falla o da error de cuota/servidor.
    // IMPORTANTE: Google retira modelos de Gemini con relativa frecuencia
    // (en agosto 2026 esto ya nos pasó: gemini-2.5-flash, gemini-2.0-flash
    // y gemini-1.5-flash dejaron de existir de un día para el otro). Esta
    // lista es la mejor conocida al momento de escribir esto, pero además
    // de mantenerla actualizada, más abajo hay un mecanismo de auto-
    // descubrimiento que consulta la API si TODOS estos fallan con 404.
    private const MODELOS_FALLBACK = [
        'gemini-3.6-flash',      // estable/GA (recomendado por Google para uso general)
        'gemini-3.5-flash-lite', // más económico, GA
        'gemini-3.7-flash',      // el más nuevo al momento de escribir esto
    ];

    private const MAX_REINTENTOS_POR_MODELO = 2;

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = (string) env('GEMINI_API_KEY', '');
    }

    /**
     * Procesa una imagen (una columna del afiche, o el afiche completo si
     * GD no está disponible) y devuelve un array con la estructura:
     *
     * [
     *   'ciudad' => 'Santa Fe' | 'Santo Tomé' | null,
     *   'turnos' => [
     *      [
     *        'fecha_inicio' => '01/09',
     *        'fecha_fin'    => '02/09',
     *        'farmacias' => [
     *           ['nombre' => 'Banchio', 'direccion' => 'Irigoyen Freyre 2200', 'telefono' => '452 2268'],
     *           ...
     *        ],
     *        'excepciones' => [
     *           // notas al pie tipo "La farmacia Azanza estará de turno
     *           // sólo el 01/09 al 02/09": acotan las fechas de UNA
     *           // farmacia puntual dentro de este turno, en vez de
     *           // ignorarse por completo.
     *           ['nombre_farmacia' => 'Azanza', 'fecha_inicio' => '01/09', 'fecha_fin' => '02/09', 'texto_original' => '...'],
     *        ]
     *      ],
     *      ...
     *   ]
     * ]
     *
     * o ['error' => '...'] si falló con todos los modelos.
     */
    public function extraerDeImagen(string $imagePath): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'GEMINI_API_KEY no está configurada'];
        }

        if (!file_exists($imagePath)) {
            return ['error' => "No existe la imagen: {$imagePath}"];
        }

        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = 'image/png';
        $prompt = $this->buildPrompt();

        Log::info('[GeminiVision] Versión del servicio: 2026-08-16c (dedupe robusto a espacios/formato + fechas normalizadas)');

        $modelos = $this->modelosAProbar();

        foreach ($modelos as $modelo) {
            for ($intento = 1; $intento <= self::MAX_REINTENTOS_POR_MODELO; $intento++) {
                try {
                    Log::info("[GeminiVision] Intento {$intento} con modelo {$modelo} para {$imagePath}");

                    [$resultado, $modeloNoEncontrado] = $this->llamarGemini($modelo, $prompt, $imageData, $mimeType);

                    if ($resultado !== null) {
                        return $resultado;
                    }

                    // Si el modelo directamente no existe (404 "not found"/
                    // "no longer available"), no tiene sentido reintentar
                    // el mismo modelo una segunda vez: pasamos al siguiente
                    // de la lista de una.
                    if ($modeloNoEncontrado) {
                        break;
                    }
                } catch (\Throwable $e) {
                    Log::warning("[GeminiVision] Falló modelo {$modelo} intento {$intento}: " . $e->getMessage());
                }

                usleep(500_000);
            }
        }

        // Todos los modelos configurados fallaron. Antes de rendirnos,
        // preguntamos a la API cuáles modelos existen HOY y probamos con
        // el mejor candidato — esto es lo que nos hubiera evitado el corte
        // de agosto 2026 cuando Google retiró 3 modelos de un saque.
        $modeloDescubierto = $this->descubrirModeloDisponible();

        if ($modeloDescubierto && !in_array($modeloDescubierto, $modelos, true)) {
            Log::warning("[GeminiVision] Todos los modelos configurados fallaron. Reintentando una vez con modelo auto-descubierto: {$modeloDescubierto}. Actualizá MODELOS_FALLBACK con este nombre para no depender del auto-descubrimiento.");

            try {
                [$resultado] = $this->llamarGemini($modeloDescubierto, $prompt, $imageData, $mimeType);
                if ($resultado !== null) {
                    return $resultado;
                }
            } catch (\Throwable $e) {
                Log::warning("[GeminiVision] Falló también el modelo auto-descubierto {$modeloDescubierto}: " . $e->getMessage());
            }
        }

        return ['error' => 'No se pudo extraer información con ningún modelo de Gemini (incluyendo auto-descubrimiento)'];
    }

    /**
     * Consulta ListModels de la API de Gemini y elige el mejor candidato
     * vigente: preferentemente algo con "flash" en el nombre (rápido y
     * barato) que soporte generateContent, evitando modelos de
     * texto-a-voz, embeddings, generación de imagen/video, o versiones
     * "preview"/"exp" inestables si hay una alternativa estable.
     */
    private function descubrirModeloDisponible(): ?string
    {
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models?key={$this->apiKey}&pageSize=200";
            $response = Http::timeout(20)->get($url);

            if (!$response->successful()) {
                Log::warning("[GeminiVision] No se pudo listar modelos: HTTP {$response->status()}");
                return null;
            }

            $modelos = $response->json('models') ?? [];

            $candidatos = collect($modelos)
                ->filter(function ($m) {
                    $metodos = $m['supportedGenerationMethods'] ?? [];
                    return in_array('generateContent', $metodos, true);
                })
                ->map(fn($m) => str_replace('models/', '', $m['name'] ?? ''))
                ->filter(fn($nombre) => $nombre !== '')
                // Excluir variantes claramente no aptas para esta tarea
                ->reject(fn($n) => Str::contains($n, ['tts', 'embedding', 'image-generation', 'imagen-', 'veo-', 'robotics', 'live']))
                ->values();

            if ($candidatos->isEmpty()) {
                return null;
            }

            // Preferencia: modelo "flash" estable (sin preview/exp) primero
            $estable = $candidatos->first(fn($n) => Str::contains($n, 'flash') && !Str::contains($n, ['preview', 'exp']));
            if ($estable) {
                return $estable;
            }

            $flashPreview = $candidatos->first(fn($n) => Str::contains($n, 'flash'));
            if ($flashPreview) {
                return $flashPreview;
            }

            // Último recurso: cualquier modelo apto, priorizando "pro" sobre otros
            return $candidatos->first(fn($n) => Str::contains($n, 'pro')) ?? $candidatos->first();
        } catch (\Throwable $e) {
            Log::warning('[GeminiVision] Error consultando ListModels: ' . $e->getMessage());
            return null;
        }
    }

    private function modelosAProbar(): array
    {
        $preferido = env('GEMINI_VISION_MODEL');
        $modelos = self::MODELOS_FALLBACK;

        if ($preferido && !in_array($preferido, $modelos, true)) {
            array_unshift($modelos, $preferido);
        } elseif ($preferido) {
            // moverlo al frente si ya está en la lista
            $modelos = array_values(array_unique(array_merge([$preferido], $modelos)));
        }

        return $modelos;
    }

    /**
     * @return array{0: array|null, 1: bool} [resultado_o_null, fueModeloNoEncontrado]
     */
    private function llamarGemini(string $modelo, string $prompt, string $imageData, string $mimeType): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key={$this->apiKey}";

        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $imageData,
                            ],
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                // NOTA: los modelos Gemini 3.x (3.6-flash en adelante) ya
                // no aceptan 'temperature'/'top_p'/'top_k' en generateContent
                // (parámetros de sampling deprecados). Por eso no los
                // mandamos. Para extracción estructurada esto no hace
                // falta de todos modos: response_mime_type=json ya fuerza
                // una salida determinística en formato.
                'response_mime_type' => 'application/json',
            ],
        ];

        $response = Http::timeout(150)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $body);

        if (!$response->successful()) {
            $bodyText = $response->body();
            Log::warning("[GeminiVision] HTTP {$response->status()} para modelo {$modelo}: {$bodyText}");

            $modeloNoEncontrado = $response->status() === 404
                && (Str::contains($bodyText, 'NOT_FOUND') || Str::contains(strtolower($bodyText), ['no longer available', 'not found']));

            return [null, $modeloNoEncontrado];
        }

        $json = $response->json();
        $texto = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$texto) {
            Log::warning('[GeminiVision] Respuesta sin contenido de texto: ' . json_encode($json));
            return [null, false];
        }

        $parsed = json_decode($texto, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($parsed)) {
            Log::warning('[GeminiVision] La respuesta no es JSON válido: ' . substr($texto, 0, 500));
            return [null, false];
        }

        $parsed['turnos'] = $parsed['turnos'] ?? [];
        $parsed['ciudad'] = $parsed['ciudad'] ?? null;
        $parsed = $this->deduplicarTurnos($parsed);

        return [$parsed, false];
    }

    /**
     * Defensa contra repetición: si el modelo devuelve el mismo turno (o
     * la misma farmacia dentro de un turno) más de una vez — algo que
     * puede pasar cuando se le manda una imagen muy grande con una salida
     * JSON muy larga, como el afiche completo sin cortar en columnas —
     * esto lo deduplica antes de que llegue a procesarse. Sin esto, cada
     * repetición del modelo se traduce en una farmacia "creada" de más en
     * la base.
     *
     * Agrupa turnos por rango de fechas (por si el mismo turno aparece
     * como dos objetos separados en el array) y, dentro de cada uno,
     * deduplica farmacias y excepciones por nombre+dirección+teléfono.
     */
    private function deduplicarTurnos(array $parsed): array
    {
        $turnosPorFecha = [];

        foreach ($parsed['turnos'] as $turno) {
            $claveFecha = $this->claveFechaNormalizada((string) ($turno['fecha_inicio'] ?? '')) . '|' . $this->claveFechaNormalizada((string) ($turno['fecha_fin'] ?? ''));

            if (!isset($turnosPorFecha[$claveFecha])) {
                $turnosPorFecha[$claveFecha] = [
                    'fecha_inicio' => $turno['fecha_inicio'] ?? '',
                    'fecha_fin' => $turno['fecha_fin'] ?? '',
                    'farmacias' => [],
                    'excepciones' => [],
                ];
            }

            foreach (($turno['farmacias'] ?? []) as $f) {
                $clave = $this->claveNormalizada(($f['nombre'] ?? '') . ($f['direccion'] ?? '') . ($f['telefono'] ?? ''));
                $turnosPorFecha[$claveFecha]['farmacias'][$clave] = $f;
            }

            foreach (($turno['excepciones'] ?? []) as $e) {
                $clave = $this->claveNormalizada(($e['nombre_farmacia'] ?? '') . ($e['fecha_inicio'] ?? '') . ($e['fecha_fin'] ?? ''));
                $turnosPorFecha[$claveFecha]['excepciones'][$clave] = $e;
            }
        }

        $turnosLimpios = [];
        foreach ($turnosPorFecha as $turno) {
            $turno['farmacias'] = array_values($turno['farmacias']);
            $turno['excepciones'] = array_values($turno['excepciones']);
            $turnosLimpios[] = $turno;
        }

        $totalAntes = array_sum(array_map(fn($t) => count($t['farmacias'] ?? []), $parsed['turnos']));
        $totalDespues = array_sum(array_map(fn($t) => count($t['farmacias']), $turnosLimpios));

        // Log incondicional (no solo cuando hay duplicados): sirve para
        // confirmar en el log que esta versión del código con
        // deduplicación efectivamente se está ejecutando, sin depender de
        // que el modelo repita algo en esa corrida puntual.
        Log::info("[GeminiVision] Deduplicación: {$totalAntes} farmacias recibidas → {$totalDespues} únicas" . ($totalAntes > $totalDespues ? " (se descartaron " . ($totalAntes - $totalDespues) . " duplicadas)" : ""));

        $parsed['turnos'] = $turnosLimpios;

        return $parsed;
    }

    private function claveNormalizada(string $s): string
    {
        $s = mb_strtolower(trim($s), 'UTF-8');
        $s = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'à', 'è', 'ì', 'ò', 'ù', 'ä', 'ë', 'ï', 'ö', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u'],
            $s
        );
        // Elimina TODO lo que no sea letra o número (espacios, puntos, guiones,
        // barras) para que variaciones de formato entre repeticiones del
        // modelo (ej. "4537137" vs "453 7137", "Av." vs "Av ") no generen
        // claves distintas para la misma farmacia.
        return preg_replace('/[^a-z0-9]/', '', $s);
    }

    /**
     * Normaliza 'DD/MM' a 2 dígitos por campo (ej. '3/8' -> '03/08') para
     * que variaciones menores de formato en la fecha entre repeticiones no
     * impidan agrupar el mismo turno bajo una sola clave.
     */
    private function claveFechaNormalizada(string $fecha): string
    {
        if (preg_match('#^(\d{1,2})/(\d{1,2})$#', trim($fecha), $m)) {
            return str_pad($m[1], 2, '0', STR_PAD_LEFT) . '/' . str_pad($m[2], 2, '0', STR_PAD_LEFT);
        }
        return trim($fecha);
    }

    private function buildPrompt(): string
    {
        return <<<PROMPT
Sos un extractor de datos para afiches de "Turnos de Farmacia" del Colegio de
Farmacéuticos de Santa Fe (Argentina). La imagen adjunta es una columna (o el
afiche completo) con bloques tipo "PRIMER TURNO", "SEGUNDO TURNO", etc. Cada
bloque tiene un rango de fechas ("Desde 8 hs ... DD/MM - hasta 8 hs ... DD/MM")
y debajo una lista de farmacias con formato: Nombre .......... Dirección  ☎ Teléfono

TU TAREA: devolver ÚNICAMENTE un JSON (sin texto adicional, sin markdown) con
esta estructura exacta:

{
  "ciudad": "Santa Fe" | "Santo Tomé" | null,
  "turnos": [
    {
      "fecha_inicio": "DD/MM",
      "fecha_fin": "DD/MM",
      "farmacias": [
        { "nombre": "...", "direccion": "...", "telefono": "..." }
      ],
      "excepciones": [
        { "nombre_farmacia": "...", "fecha_inicio": "DD/MM", "fecha_fin": "DD/MM", "texto_original": "..." }
      ]
    }
  ]
}

REGLAS IMPORTANTES:
1. "ciudad": inferila del encabezado de la columna (SANTA FE o SANTO TOMÉ). Si
   no se ve encabezado en esta imagen, dejá null.
2. Un mismo bloque de turno puede tener varias líneas de fecha consecutivas
   (ej. "Desde 8hs 01/09 hasta 8hs 02/09" seguido de otra línea "Desde 8hs
   02/09 hasta 8hs 03/09" para un turno rotativo distinto): cada rango de
   fechas es un objeto de turno separado en el array, cada uno con su propia
   lista de farmacias si corresponde. Si varias líneas de farmacias
   pertenecen visualmente al mismo bloque de fechas, van todas en el mismo
   turno.
3. "nombre": solo el nombre propio de la farmacia (sin puntos suspensivos,
   sin la palabra "Farmacia").
4. "direccion": calle y altura, tal cual aparece, sin el nombre ni el
   teléfono.
5. "telefono": solo los dígitos y guiones tal cual aparecen (no inventes el
   0342 si no está escrito).
6. IGNORÁ por completo (no las incluyas como farmacia ni generes entradas
   para ellas): encabezados de sección, el texto "URGENCIAS TOXICOLÓGICAS",
   y datos de hospitales.
7. Notas al pie tipo "La farmacia X estará de turno sólo el DD/MM al DD/MM"
   (suelen ir en letra chica debajo de la lista de un turno, a veces en
   cursiva): NO son una farmacia nueva — la farmacia X ya está en la lista
   de "farmacias" de ese turno con su dirección/teléfono. Lo que indica esta
   nota es que, a diferencia del resto de las farmacias de ese turno (que
   cubren todo el rango fecha_inicio–fecha_fin), la farmacia X puntual solo
   está de turno en el sub-rango de fechas que dice la nota. Agregá esa
   información en el array "excepciones" del turno correspondiente, con
   "nombre_farmacia" igual (o lo más parecido posible) al "nombre" que
   pusiste en "farmacias", y "fecha_inicio"/"fecha_fin" con el rango de la
   nota. Si no hay ninguna nota de este tipo en un turno, "excepciones" va
   como array vacío [].
8. Si una farmacia no tiene dirección o teléfono legible, dejá ese campo
   como cadena vacía "" en vez de inventar un valor.
9. No inventes farmacias ni fechas que no estén en la imagen. Si un campo es
   ilegible, usá tu mejor lectura, nunca lo omitas del objeto.

Devolvé solo el JSON, nada más.
PROMPT;
    }
}