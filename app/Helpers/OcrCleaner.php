<?php

namespace App\Helpers;

class OcrCleaner
{
    // Mantener saltos de línea; limpiar caracteres no imprimibles
    public static function normalizeRawText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        // eliminar marcas HTML si llegaron
        $text = preg_replace('/<[^>]+>/', ' ', $text);
        // quitar caracteres raros EXCEPTO newline
        $text = preg_replace('/[^\p{L}0-9@\.,\/\-\s\n\:\(\)]/u', ' ', $text);
        // compactar múltiples espacios (no borra \n)
        $text = preg_replace('/[ ]{2,}/u', ' ', $text);
        // trim por línea
        $lines = array_map('trim', explode("\n", $text));
        $lines = array_filter($lines, fn($l) => $l !== '');
        return implode("\n", $lines);
    }

    public static function fixPhone(?string $s): ?string
    {
        if (!$s) return null;
        // Extraer secuencias de números plausibles
        if (preg_match('/(\d{2,4})[^\d]{0,2}(\d{3,5})/', $s, $m)) {
            $tel = $m[1] . $m[2];
            // heurística local: si tiene 7 dígitos -> prefijo 342 (opcional)
            if (strlen($tel) === 7) $tel = '342' . $tel;
            return $tel;
        }
        return null;
    }

    public static function fixDateString(string $s, int $defaultYear): ?array
    {
        // Reemplazar confusiones OCR
        $s = str_replace(['O', 'o', 'I', 'l', '|'], ['0', '0', '1', '1', '1'], $s);
        if (preg_match('/(\d{1,2})\/(\d{1,2})/', $s, $m)) {
            $d = intval($m[1]);
            $mo = intval($m[2]);
            // validación simple
            if ($d >= 1 && $d <= 31 && $mo >= 1 && $mo <= 12) {
                return [$d, $mo, $defaultYear];
            }
        }
        return null;
    }

    public static function normalizeName(?string $s): ?string
    {
        if (!$s) return null;
        $s = preg_replace('/[^A-Za-zÁÉÍÓÚÑáéíóúñ0-9\.\-\&\s\']+/u', ' ', $s);
        $s = preg_replace('/\s{2,}/', ' ', trim($s));
        return $s;
    }

    public static function normalizeAddress(?string $s): ?string
    {
        if (!$s) return null;
        $a = preg_replace('/\s{2,}/', ' ', trim($s));
        // Normalizaciones simples
        $a = preg_replace('/\bAV\b/i', 'Av.', $a);
        $a = preg_replace('/\bBV\b/i', 'Bv.', $a);
        $a = preg_replace('/\bCALLE\b/i', 'Calle', $a);
        return $a;
    }

    private static array $streetReplacements = [
        'Av. A. del Valle'          => 'Avenida Aristóbulo del Valle',
        'Av. F. Zuviría'            => 'Avenida Facundo Zuviría',
        'Av. Fdo. Zuviría'          => 'Avenida Facundo Zuviría',
        'Av. J.J. Paso'             => 'Avenida Juan José Paso',
        'Pte Roca'                  => 'Presidente Roca',
        'Bv. Zavalla'               => 'Bulevar Doctor Zavalla',
        'Av. Gral. Paz'             => 'Avenida General Paz',
        'Salv. del Carril'          => 'Salvador del Carril ',
        'Av. Gorriti'               => 'Gorriti',
        'Barrio El Pozo M.8 Vda. 43' => 'Manzana 8, El Pozo',
        'Angel Casanello'           => 'Cassanello',
        'Dra. Grierson'             => 'Doctora Cecilia Grierson',
        'Stgo. del Estero'          => 'Santiago del Estero',
        'Av. Richeri'               => 'Avenida Richieri',
        'Av. Richieri'              => 'Avenida Richieri',
    ];

    public static function fixStreetNames(string $direccion): string
    {
        if (!$direccion) return $direccion;

        // crear patrones a partir del diccionario (escapado)
        foreach (self::$streetReplacements as $pattern => $replacement) {
            // transformar el patrón en una regex tolerante a puntos y espacios:
            $p = preg_quote($pattern, '/');
            // permitir opcionalmente puntos y espacios entre letras/abreviaturas
            $p = str_replace(['\\ ', '\\.'], ['\\s*', '\\.?'], $p);

            // busco y reemplazo todas las ocurrencias (case-insensitive)
            $direccion = preg_replace("/\b{$p}\b/iu", $replacement, $direccion);
        }

        // compactar espacios sobrantes
        $direccion = preg_replace('/\s{2,}/', ' ', trim($direccion));

        return $direccion;
    }


    public static function splitAddressNotes(?string $direccion): array
    {
        if (!$direccion) {
            return [null, null];
        }

        // Detectar separadores comunes
        if (preg_match('/(.+?)\s*[-–\/]\s*(.+)/u', $direccion, $m)) {
            return [trim($m[1]), trim($m[2])];
        }

        // Notas entre paréntesis
        if (preg_match('/(.+?)\s*\((.+)\)/u', $direccion, $m)) {
            return [trim($m[1]), trim($m[2])];
        }

        return [trim($direccion), null];
    }
}
