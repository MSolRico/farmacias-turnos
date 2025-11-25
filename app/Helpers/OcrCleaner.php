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
        $s = str_replace(['O','o','I','l','|'], ['0','0','1','1','1'], $s);
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
}
