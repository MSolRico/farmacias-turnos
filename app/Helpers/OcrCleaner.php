<?php

namespace App\Helpers;

class OcrCleaner
{
    /**
     * Corrige fechas mal reconocidas por OCR
     * Ejemplos: -4/12 → 24/12, 6-12 → 06/12, 6.12 → 06/12
     */
    public static function fixDate(string $fecha): string
    {
        // Limpiar espacios internos (ej: "06 / 12" -> "06/12")
        $fecha = preg_replace('/\s+/', '', $fecha);

        // Normalizar separadores (puntos o guiones a barras)
        $fecha = str_replace(['.', '-'], '/', $fecha);

        // Corregir errores comunes de OCR en el primer dígito
        // Ej: "-4/12" suele ser "24/12", "l6/12" es "16/12", "!2/12" es "12/12"
        $fecha = preg_replace('/^-(\d)\//', '2$1/', $fecha);
        $fecha = preg_replace('/^[lI|!](\d)\//', '1$1/', $fecha);
        $fecha = preg_replace('/\/[oO](\d)$/', '/0$1', $fecha);

        // Asegurar formato DD/MM (agregar ceros iniciales si faltan)
        if (preg_match('/^(\d{1,2})\/(\d{1,2})$/', $fecha, $m)) {
            $dia = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $mes = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            return "$dia/$mes";
        }

        return trim($fecha);
    }

    public static function extractAndFixDates(string $line): array
    {
        // REGEX MEJORADO:
        // 1. [-lIoO]? : Opcional prefijo de basura OCR
        // 2. \d{1,2}  : Día (1 o 2 dígitos)
        // 3. \s*[\/\.-]\s* : Separador (barra, punto, guion) con espacios opcionales
        // 4. \d{1,2}  : Mes
        preg_match_all('/(?:^|[\s\(\[])([-lIoO]?\d{1,2}\s*[\/\.-]\s*\d{1,2})(?:$|[\s\)\]])/', $line, $matches);

        if (empty($matches[1])) {
            // INTENTO EXTRA: Buscar formato "Día X" (ej: "Viernes 6") asumiendo mes actual (12)
            // Esto es crucial para Santo Tomé si el formato es distinto
            if (preg_match('/(?:Lunes|Martes|Mi[eé]rcoles|Jueves|Viernes|S[aá]bado|Domingo|D[ií]a)\s+(\d{1,2})\b/iu', $line, $m)) {
                $dia = str_pad($m[1], 2, '0', STR_PAD_LEFT);
                $mes = '12'; // Asumimos Diciembre por el contexto del archivo
                return ["$dia/$mes"];
            }
            return [];
        }

        $fechasCorregidas = [];
        foreach ($matches[1] as $fechaRaw) {
            $fechaLimpia = self::fixDate($fechaRaw);

            if (self::isValidDate($fechaLimpia)) {
                $fechasCorregidas[] = $fechaLimpia;
            }
        }

        return array_values(array_unique($fechasCorregidas));
    }

    private static function isValidDate(string $fecha): bool
    {
        if (!preg_match('/^(\d{2})\/(\d{2})$/', $fecha, $m)) {
            return false;
        }

        $dia = (int)$m[1];
        $mes = (int)$m[2];

        return $dia >= 1 && $dia <= 31 && $mes >= 1 && $mes <= 12;
    }

    public static function fixPhone(string $line): ?string
    {
        if (preg_match('/(\d{3,4})[\s\-=£\*E27]+(\d{3,5})/', $line, $m)) {
            return trim($m[1]) . trim($m[2]);
        }
        if (preg_match('/(\d{7,8})\s*$/', $line, $m)) {
            return $m[1];
        }
        if (preg_match('/(15\d{8,9})/', $line, $m)) {
            return $m[1];
        }
        if (preg_match('/\b(\d{7,8})\b/', $line, $m)) {
            return $m[1];
        }
        return null;
    }

    public static function normalizeName(string $nombre): string
    {
        $nombre = str_replace(['..', '...', '....'], ' ', $nombre);
        $nombre = preg_replace('/[^\p{L}\s\.]/u', ' ', $nombre);
        $nombre = preg_replace('/\s+(P\.?B\.?|sani)\s*$/i', '', $nombre);
        $nombre = preg_replace('/\s+/', ' ', $nombre);
        return trim($nombre);
    }

    public static function normalizeAddress(string $direccion): string
    {
        if (empty($direccion)) return '';

        $direccion = preg_replace('/[^\p{L}\p{N}\s\.\-º°\/]/u', ' ', $direccion);
        $direccion = preg_replace('/\.{2,}/', '.', $direccion);
        $direccion = preg_replace('/\s+/', ' ', $direccion);

        if (preg_match('/\b(avia)\s+\d+/i', $direccion)) {
            $direccion = preg_replace('/\b(avia)\s+/i', 'Rivadavia ', $direccion);
        }

        $direccion = self::fixStreetNames($direccion);
        foreach (self::$streetReplacements as $needle => $replacement) {
            if (stripos($direccion, $needle) !== false) {
                $direccion = str_ireplace($needle, $replacement, $direccion);
            }
        }

        [$direccionLimpia, $nota] = self::splitAddressNotes($direccion);
        return trim($direccionLimpia);
    }

    private static array $streetReplacements = [
        'Barrio El Pozo M.8 Vda. 43' => 'Manzana 8, El Pozo',
        'Av. A. del Valle'          => 'Avenida Aristóbulo del Valle',
        'AV A. del Valle'           => 'Avenida Aristóbulo del Valle',
        'A. del Valle'              => 'Aristóbulo del Valle',
        'Av. Blas Parera'           => 'Avenida Blas Parera',
        'AV. Blas Parera'           => 'Avenida Blas Parera',
        'Blas Parera'               => 'Avenida Blas Parera',
        'Av. Fdo. Zuviría'          => 'Avenida Facundo Zuviría',
        'Av. F. Zuviría'            => 'Avenida Facundo Zuviría',
        'AV F. Zuviría'             => 'Avenida Facundo Zuviría',
        'Av. J.J. Paso'             => 'Avenida Juan José Paso',
        'Av. Gral. López'           => 'Avenida General López',
        'AV. Gral. López'           => 'Avenida General López',
        'Gral. López'               => 'Avenida General López',
        'Av. Gral. Paz'             => 'Avenida General Paz',
        'AV. Gral. Paz'             => 'Avenida General Paz',
        'AV Gral. Paz'              => 'Avenida General Paz',
        'Av. G. Paz'                => 'Avenida General Paz',
        'Av. Gral Paz'              => 'Avenida General Paz',
        'AV. Gral Paz'              => 'Avenida General Paz',
        'AV Gral Paz'               => 'Avenida General Paz',
        'Gral Paz'                  => 'Avenida General Paz',
        'Av. Gorriti'               => 'Gorriti',
        'AV. Gorriti'               => 'Gorriti',
        'Av. L. y Planes'           => 'Avenida López y Planes',
        'AV. López y Planes'        => 'Avenida López y Planes',
        'Av. Peñaloza'              => 'Avenida Peñaloza',
        'AV. Peñaloza'              => 'Avenida Peñaloza',
        'Av. Richeri'               => 'Avenida Richieri',
        'AV. Richeri'               => 'Avenida Richieri',
        'Av. Urquiza'               => 'Urquiza',
        'AV. Urquiza'               => 'Urquiza',
        'Av. 7 de Marzo'            => 'Av. 7 de Marzo',
        'AV. 7 de Marzo'            => 'Av. 7 de Marzo',
        'Av. Luján'                 => 'Av. Luján',
        'AV. Luján'                 => 'Av. Luján',
        'Pte Roca'                  => 'Presidente Roca',
        'Bv. Zavalla'               => 'Bulevar Doctor Zavalla',
        'BV. Zavalla'               => 'Bulevar Doctor Zavalla',
        'DV. Zavalla'               => 'Bulevar Doctor Zavalla',
        'Salv. del Carril'          => 'Salvador del Carril',
        'Angel Casanello'           => 'Cassanello',
        'Dra. Grierson'             => 'Doctora Cecilia Grierson',
        'Stgo. del Estero'          => 'Santiago del Estero',
        '1º de Mayo'                => '1 de Mayo',
        '17 de Mayo'                => '1º de Mayo',
        '19 de Mayo'                => '1º de Mayo',
        '25 de Mayo'                => '25 de Mayo',
        'Rivadavia'                 => 'Avenida Rivadavia',
        'RiVadavia'                 => 'Avenida Rivadavia',
        'M. Candioti'               => 'Marcial Candioti',
        'P. Genesio'                => 'Padre Genesio',
        'Echague'                   => 'Echagüe',
        'GUEMES'                    => 'Güemes',
        'GUMES'                     => 'Güemes',
        'MENdoza'                   => 'Mendoza',
        'MNdoza'                    => 'Mendoza',
        'SUipacha'                  => 'Suipacha',
        'SVipacha'                  => 'Suipacha',
        'SaN Martín'                => 'San Martín',
        'SAN Martín'                => 'San Martín',
        'SAN Gerónimo'              => 'San Gerónimo',
        'San Gerónimo'              => 'San Gerónimo',
        'SAN Jerónimo'              => 'San Jerónimo',
        'Nellaneda'                 => 'Avellaneda',
        'ENtYE RÍos'                => 'Entre Ríos',
        'N RÍOS'                    => 'Entre Ríos',
        'Dro Senn'                  => 'Alejandro Senn',
        'dro Senn'                  => 'Alejandro Senn',
    ];

    public static function fixStreetNames(string $direccion): string
    {
        $fixes = [
            '/\bAV\s*\./i' => 'Av.',
            '/\bBV\s*\./i' => 'Bv.',
            '/\bGral\s*\./i' => 'Gral.',
            '/\bDr\s*\./i' => 'Dr.',
            '/\bFdo\s*\./i' => 'Fdo.',
            '/\bStgo\s*\./i' => 'Stgo.',
        ];

        foreach ($fixes as $patron => $reemplazo) {
            $direccion = preg_replace($patron, $reemplazo, $direccion);
        }
        return trim($direccion);
    }

    public static function splitAddressNotes(string $direccion): array
    {
        if (preg_match('/^(.+\d+)\s*(?:[-,]?\s*(.*))?$/i', $direccion, $m)) {
            $direccionLimpia = trim($m[1]);
            $nota = isset($m[2]) ? trim($m[2]) : null;
            return [$direccionLimpia, $nota];
        }
        return [$direccion, null];
    }
}
