<?php

namespace App\Helpers;

class OcrCleaner
{
    /**
     * Corrige fechas mal reconocidas por OCR
     * Ejemplos: -4/12 → 24/12, -0/12 → 20/12, -1/12 → 21/12
     */
    public static function fixDate(string $fecha): string
    {
        $fecha = preg_replace('/^-(\d)\//', '2$1/', $fecha);
        $fecha = preg_replace('/^[lI](\d)\//', '1$1/', $fecha);
        $fecha = preg_replace('/\/[oO](\d)$/', '/0$1', $fecha);
        $fecha = preg_replace('/\s+/', '', $fecha);

        return trim($fecha);
    }

    public static function extractAndFixDates(string $line): array
    {
        preg_match_all('/[-lIoO]?\d{1,2}\/\d{1,2}/', $line, $matches);

        if (empty($matches[0])) {
            return [];
        }

        $fechasCorregidas = [];
        foreach ($matches[0] as $fecha) {
            $fechaLimpia = self::fixDate($fecha);

            if (self::isValidDate($fechaLimpia)) {
                $fechasCorregidas[] = $fechaLimpia;
            }
        }

        return $fechasCorregidas;
    }

    private static function isValidDate(string $fecha): bool
    {
        if (!preg_match('/^(\d{1,2})\/(\d{1,2})$/', $fecha, $m)) {
            return false;
        }

        $dia = (int)$m[1];
        $mes = (int)$m[2];

        return $dia >= 1 && $dia <= 31 && $mes >= 1 && $mes <= 12;
    }

    public static function fixPhone(string $line): ?string
    {
        // Buscar patrones de teléfono con separadores mal reconocidos
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

        // Eliminar basura de OCR
        $direccion = preg_replace('/[^\p{L}\p{N}\s\.\-º°\/]/u', ' ', $direccion);
        $direccion = preg_replace('/\.{2,}/', '.', $direccion);
        $direccion = preg_replace('/\s+/', ' ', $direccion);

        // CORRECCIÓN ESPECIAL: "avia" → "Rivadavia"
        if (preg_match('/\b(avia)\s+\d+/i', $direccion)) {
            $direccion = preg_replace('/\b(avia)\s+/i', 'Rivadavia ', $direccion);
        }

        // Reemplazos especiales de calles
        $direccion = self::fixStreetNames($direccion);
        foreach (self::$streetReplacements as $needle => $replacement) {
            if (stripos($direccion, $needle) !== false) {
                $direccion = str_ireplace($needle, $replacement, $direccion);
            }
        }

        // Separar notas tipo "PB", "Local", "Piso"
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
        // Mantiene todo hasta el último número + opcionalmente PB, Local, Piso
        if (preg_match('/^(.+\d+)\s*(?:[-,]?\s*(.*))?$/i', $direccion, $m)) {
            $direccionLimpia = trim($m[1]);
            $nota = isset($m[2]) ? trim($m[2]) : null;
            return [$direccionLimpia, $nota];
        }

        return [$direccion, null];
    }
}
