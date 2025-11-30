<?php

namespace App\Services;

use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\DB;
use App\Services\GeocodeService;
use App\Models\Farmacia;
use App\Models\Turno;
use App\Models\Ciudad;
use Carbon\Carbon;
use App\Helpers\OcrCleaner;
use Illuminate\Support\Str;

class OcrFarmaciasService
{
    // Año por defecto (ajustá si necesitás otro)
    protected int $defaultYear = 2025;

    protected GeocodeService $geo;

    public function __construct(GeocodeService $geo)
    {
        $this->geo = $geo;
    }

    public function procesar(string $ruta)
    {
        if (!file_exists($ruta)) {
            return ['error' => "No existe el archivo: $ruta"];
        }

        // 1) Ejecutar OCR (Tesseract)
        try {
            $ocr = (new TesseractOCR($ruta))
                ->lang('spa')
                ->psm(6);

            $textoBruto = $ocr->run();
        } catch (\Throwable $e) {
            return ['error' => "Error ejecutando Tesseract: " . $e->getMessage()];
        }

        // 2) Limpiar preservando saltos de línea
        $texto = OcrCleaner::normalizeRawText($textoBruto);

        // Guardar dump para depuración (opcional)
        try {
            @file_put_contents(storage_path('logs/ocr_last_raw.txt'), $textoBruto);
            @file_put_contents(storage_path('logs/ocr_last_clean.txt'), $texto);
        } catch (\Throwable $e) {
            // no crítico
        }

        // 3) Separar en líneas y bloques
        $lines = explode("\n", $texto);

        $currentCity = null;
        $currentTurnDates = null; // array [inicio(DateTime), fin(DateTime)]
        $items = []; // elementos detectados: nombre,direccion,telefono,ciudad,fechaPair

        foreach ($lines as $rawLine) {
            $line = trim($rawLine);
            if ($line === '') continue;
            $upper = mb_strtoupper($line, 'UTF-8');

            // DETECTAR CIUDAD (busca palabras claves)
            if (preg_match('/\bSANTA\s*FE\b/u', $upper)) {
                $currentCity = 'Santa Fe';
                continue;
            }
            if (preg_match('/\bSANTO\s*TOM(É|E)?\b/iu', $upper)) {
                $currentCity = 'Santo Tomé';
                continue;
            }

            // DETECTAR BLOQUE FECHAS (ej: Desde 8 hs .. 03/11 - hasta 8 hs .. 04/11)
            if (preg_match_all('/(\d{1,2}\/\d{1,2})/', $line, $m) && count($m[1]) >= 2) {
                $d1 = OcrCleaner::fixDateString($m[1][0], $this->defaultYear);
                $d2 = OcrCleaner::fixDateString($m[1][1], $this->defaultYear);
                if ($d1 && $d2) {
                    // convertir a Carbon (inicio 8:00 del primer día, fin 8:00 del segundo día)
                    [$d1d, $d1m, $d1y] = $d1;
                    [$d2d, $d2m, $d2y] = $d2;
                    $inicio = Carbon::createFromDate($d1y, $d1m, $d1d)->setTime(8, 0, 0);
                    $fin = Carbon::createFromDate($d2y, $d2m, $d2d)->setTime(8, 0, 0);
                    $currentTurnDates = [$inicio, $fin];
                    continue;
                }
            }

            // DETECTAR LÍNEAS DE FARMACIA (heurística: teléfono o formato habitual)
            // La expresión captura números en formato variados.
            if (preg_match('/\d{2,4}[^\d]{0,2}\d{3,5}/', $line)) {

                $telefono = OcrCleaner::fixPhone($line);
                $direccion = $this->extractAddress($line);
                $nombre = $this->extractName($line, $direccion, $telefono);

                $nombre = OcrCleaner::normalizeName($nombre);
                $direccion = OcrCleaner::normalizeAddress($direccion);
                $direccion = OcrCleaner::fixStreetNames($direccion);
                [$direccion, $notas] = OcrCleaner::splitAddressNotes($direccion);

                $telefono = $telefono ? preg_replace('/\D+/', '', $telefono) : null;
                $ciudad = $currentCity ?? 'Santa Fe';

                $items[] = [
                    'id_tmp'    => Str::random(8),
                    'nombre'    => $nombre,
                    'direccion' => $direccion,
                    'telefono'  => $telefono,
                    'ciudad'    => $ciudad,
                    'notas'     => $notas,
                    'turn_dates' => $currentTurnDates
                ];

                continue;
            }


            // Otras líneas: posible continuación de la dirección (ej. línea previa tenía nombre + teléfono)
            // Intento unir con último item si parece una dirección
            if (!empty($items)) {
                $last = &$items[count($items) - 1];
                if ($last['direccion'] === null && $this->looksLikeAddress($line)) {
                    // concatenar
                    $last['direccion'] = trim(($last['direccion'] ?? '') . ' ' . $line);
                    $last['direccion'] = OcrCleaner::normalizeAddress($last['direccion']);
                }
            }
        } // end foreach lines

        // Si no detectó items -> reportar y devolver
        if (empty($items)) {
            return ['farmacias' => 0, 'turnos' => 0];
        }

        // 4) Guardar en BD con validaciones y evitado de duplicados
        $stats = ['farmacias' => 0, 'turnos' => 0];
        DB::beginTransaction();
        try {
            foreach ($items as $it) {
                // validar nombre minimo
                if (empty($it['nombre'])) continue;

                // si no hay fechas, saltar (podés cambiar la regla si querés aceptar sin fechas)
                if (empty($it['turn_dates']) || !is_array($it['turn_dates'])) continue;
                [$inicio, $fin] = $it['turn_dates'];
                if (!$inicio || !$fin) continue;

                // Crear o recuperar ciudad
                $ciudad = Ciudad::firstOrCreate(['nombre_ciudad' => $it['ciudad']]);

                // Buscar farmacia por nombre + ciudad (evitar duplicados)
                $farmacia = Farmacia::where('nombre', $it['nombre'])
                    ->where('id_ciudad', $ciudad->id_ciudad)
                    ->first();

                if (!$farmacia) {
                    // si no existe, crear
                    $farmacia = Farmacia::create([
                        'nombre' => $it['nombre'],
                        'direccion' => $it['direccion'],
                        'telefono' => $it['telefono'],
                        'id_ciudad' => $ciudad->id_ciudad
                    ]);
                    $stats['farmacias']++;
                } else {
                    // actualizar datos faltantes
                    $changed = false;
                    if (empty($farmacia->direccion) && !empty($it['direccion'])) {
                        $farmacia->direccion = $it['direccion'];
                        $changed = true;
                    }
                    if (empty($farmacia->telefono) && !empty($it['telefono'])) {
                        $farmacia->telefono = $it['telefono'];
                        $changed = true;
                    }
                    if ($changed) $farmacia->save();
                }

                // Geocoding solo si hace falta (respeta rate-limit)
                if ((empty($farmacia->lat) || empty($farmacia->lng)) && !empty($farmacia->direccion)) {
                    [$lat, $lng] = $this->obtenerCoordenadas($farmacia->direccion, $ciudad->nombre_ciudad);
                    if ($lat && $lng) {
                        $farmacia->lat = $lat;
                        $farmacia->lng = $lng;
                        $farmacia->save();
                        sleep(1);
                    }
                }

                // Crear turno (si no existe)
                $turno = Turno::firstOrCreate(
                    [
                        'fecha_hora_inicio' => $inicio->toDateTimeString(),
                        'fecha_hora_fin' => $fin->toDateTimeString(),
                        'id_ciudad' => $ciudad->id_ciudad
                    ],
                    [
                        'nombre_turno' => 'Turno ' . $inicio->format('d/m')
                    ]
                );

                $stats['turnos']++;

                $pivot = DB::table('farmacias_turnos')
                    ->where('id_farmacia', $farmacia->id_farmacia)
                    ->where('id_turno', $turno->id_turno)
                    ->first();

                if (!$pivot) {
                    // No existe: insertar todo
                    DB::table('farmacias_turnos')->insert([
                        'id_farmacia'  => $farmacia->id_farmacia,
                        'id_turno'     => $turno->id_turno,
                        'notas'        => $it['notas'] ?? null,
                    ]);
                } else {
                    // Existe: actualizar notas solo si está vacía y hay una nueva
                    if (empty($pivot->notas) && !empty($it['notas'])) {
                        DB::table('farmacias_turnos')
                            ->where('id_farmacia', $farmacia->id_farmacia)
                            ->where('id_turno', $turno->id_turno)
                            ->update(['notas' => $it['notas']]);
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return ['error' => $e->getMessage()];
        }

        return $stats;
    }

    // -------------------------
    // Helpers privados
    // -------------------------

    private function looksLikeAddress(string $line): bool
    {
        return (bool) preg_match('/\b(Av|Av\.|Bv|Calle|Diagonal|Dr|Gral|San|Marcial|Mariano|\d{1,3})\b/i', $line);
    }

    private function extractAddress(string $line): ?string
    {
        // buscar patrones comunes de direccion (con número)
        if (preg_match('/((Av\.?|Avenida|Bv\.?|Boulevard|Calle|Diagonal|San|Dr\.?|Gral\.?|Marcial|Mariano|Padre|9 de Julio|25 de Mayo|Blas Parera)[^\d\n]*\d{1,5})/iu', $line, $m)) {
            return trim($m[1]);
        }

        // fallback: buscar primer fragmento con número
        if (preg_match('/([A-Za-zÁÉÍÓÚÑáéíóúñ\s\.]+)\s+(\d{1,5})/u', $line, $m)) {
            return trim($m[0]);
        }

        return null;
    }

    private function extractName(string $line, ?string $direccion, ?string $telefono): string
    {
        $tmp = $line;

        // quitar teléfono si está al final
        if ($telefono) {
            $tmp = preg_replace('/' . preg_quote($telefono, '/') . '$/', '', $tmp);
            // también quitar espacios y caracteres sobrantes
            $tmp = preg_replace('/[@\-\:\|]+$/', '', trim($tmp));
        }

        // quitar dirección si aparece dentro
        if ($direccion) {
            $tmp = str_ireplace($direccion, '', $tmp);
        }

        // limpiar números residuales
        $tmp = preg_replace('/\d{2,}/', '', $tmp);

        // quitar símbolos extraños
        $tmp = preg_replace('/[@\.\-]{2,}/', ' ', $tmp);

        return trim($tmp);
    }

    private function obtenerCoordenadas(string $direccion, string $ciudad): array
    {
        return $this->geo->buscarVariantes($direccion, $ciudad);
    }
}
