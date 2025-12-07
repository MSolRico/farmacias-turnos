<?php

namespace App\Helpers;

class OcrCleaner
{
    public static function fixPhone(string $line): ?string
    {
        // Buscar patrones de teléfono con separadores mal reconocidos
        if (preg_match('/(\d{3,4})[\s\-=£\*E27]+(\d{3,5})/', $line, $m)) {
            return $m[1] . $m[2];
        }

        // Buscar solo números largos
        if (preg_match('/\b(\d{6,8})\b/', $line, $m)) {
            return $m[1];
        }

        return null;
    }

    public static function normalizeName(string $nombre): string
    {
        // Eliminar basura común de OCR
        $nombre = preg_replace('/\s+(nn|rrr|eee|uuu|cen|mer|nar|ana|ac|ee)\s+/i', ' ', $nombre);
        $nombre = preg_replace('/\.\s*\.+/', '', $nombre);
        $nombre = preg_replace('/\s+/', ' ', $nombre);

        return trim($nombre);
    }

    public static function normalizeAddress(string $direccion): string
    {
        if (empty($direccion)) return '';

        // Eliminar basura de OCR
        $direccion = preg_replace('/\s+(nn|ac|ee)\s+/i', ' ', $direccion);
        $direccion = preg_replace('/\.{2,}/', '', $direccion);
        $direccion = preg_replace('/\s+/', ' ', $direccion);

        // APLICAR LOS REEMPLAZOS EXACTOS DE LAS CALLES
        foreach (self::$streetReplacements as $needle => $replacement) {
            if (stripos($direccion, $needle) !== false) {
                $direccion = str_ireplace($needle, $replacement, $direccion);
            }
        }

        return $direccion;
    }

    private static array $streetReplacements = [
        'Barrio El Pozo M.8 Vda. 43' => 'Manzana 8, El Pozo',
        'Av. A. del Valle'          => 'Avenida Aristóbulo del Valle',
        'Av. Blas Parera'           => 'Avenida Blas Parera',
        'Blas Parera'               => 'Avenida Blas Parera',
        'Av. Fdo. Zuviría'          => 'Avenida Facundo Zuviría',
        'Av. F. Zuviría'            => 'Avenida Facundo Zuviría',
        'Av. J.J. Paso'             => 'Avenida Juan José Paso',
        'Av. Gral. López'           => 'Avenida General López',
        'Gral. López'               => 'Avenida General López',
        'Av. Gral. Paz'             => 'Avenida General Paz',
        'Av. G. Paz'                => 'Avenida General Paz',
        'Av. Gorriti'               => 'Gorriti',
        'Av. L. y Planes'           => 'Avenida López y Planes',
        'Av. Peñaloza'              => 'Avenida Peñaloza',
        'Av. Richeri'               => 'Avenida Richieri',
        'Av. Urquiza'               => 'Urquiza',
        'Pte Roca'                  => 'Presidente Roca',
        'Bv. Zavalla'               => 'Bulevar Doctor Zavalla',
        'Salv. del Carril'          => 'Salvador del Carril',
        'Angel Casanello'           => 'Cassanello',
        'Dra. Grierson'             => 'Doctora Cecilia Grierson',
        'Stgo. del Estero'          => 'Santiago del Estero',
        '1º de Mayo'                => '1 de Mayo',
        'Rivadavia'                 => 'Avenida Rivadavia',
        'M. Candioti'               => 'Marcial Candioti',
    ];


    public static function fixStreetNames(string $direccion): string
    {
        if (empty($direccion)) return '';

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

        return $direccion;
    }

    public static function splitAddressNotes(string $direccion): array
    {
        // Separar notas de la dirección (ej: "Calle 123 - Local 2")
        if (preg_match('/^(.+?)\s*-\s*(Local|Loc|P\.B\.|Piso|Dto).*$/i', $direccion, $m)) {
            return [trim($m[1]), trim($m[0])];
        }

        return [$direccion, null];
    }
}
