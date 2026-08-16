<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * ANTES: esta clase tenía ~250 líneas dedicadas a adivinar dónde terminaba
 * el nombre y empezaba la dirección en una línea de texto sucio de
 * Tesseract (extractName/extractAddress/esLineaValidaDeFarmacia), más una
 * lista hardcodeada de 150+ nombres conocidos para fuzzy-match
 * (encontrarNombreSimilar) que duplicaba la tabla `farmacias` de la BD.
 *
 * Con Gemini Vision leyendo la imagen directamente, esos campos ya vienen
 * separados y correctos en el JSON (ver GeminiVisionOcrService). Lo único
 * que sigue teniendo sentido acá es:
 *   - validar que el teléfono extraído tenga forma de teléfono real
 *   - inferir el año cuando el turno cruza fin de año (el afiche solo
 *     imprime DD/MM)
 *
 * Si en el futuro se necesita canonizar nombres contra la BD, eso lo hace
 * FarmaciaMatchingService (que sí consulta la tabla `farmacias` real, en
 * vez de una lista hardcodeada paralela).
 */
class OcrFarmaciasValidator
{
    /**
     * Validación de teléfonos
     */
    public function validarTelefono(string $telefono): ?string
    {
        $telefono = preg_replace('/\D/', '', $telefono);

        if (strlen($telefono) < 6 || strlen($telefono) > 10) {
            return null;
        }

        if (substr($telefono, 0, 4) === '0342') {
            $telefono = substr($telefono, 4);
        }

        // Rechazar números obviamente incorrectos
        if (preg_match('/^[0-9]{1,2}$/', $telefono) || preg_match('/^0+$/', $telefono)) {
            return null;
        }

        return $telefono;
    }

    /**
     * Lógica para inferir el año correcto en turnos que cruzan fin de año
     */
    public function inferYear(int $d1, int $m1, int $d2, int $m2): int
    {
        $y = Carbon::now()->year;
        $inicio = Carbon::create($y, $m1, $d1);
        $fin = Carbon::create($y, $m2, $d2);
        return $fin->lessThan($inicio) ? $y + 1 : $y;
    }
}