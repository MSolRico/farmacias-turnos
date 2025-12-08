<?php

namespace App\Services;

use App\Helpers\OcrCleaner;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OcrFarmaciaValidator
{
    protected $farmaciaMatching;

    // Patrones de nombres válidos (Movidos del Service principal)
    private array $nombresConocidos = [
        // Santa Fe
        'Adrián Carrizo', 'Banchio', 'Belgrano', 'Bonazzola', 'Bruno', 'Camilatto',
        'Camusso', 'Costa', 'Gheco', 'Morales', 'Morello', 'Nebiolo', 'Ortiz de Zárate',
        'Azanza', 'Ignacio Azanza', 'Abregú', 'Gotero', 'Luero', 'Galizzi', 'Marcelo Galizzi',
        'Martínez', 'Rita Martínez', 'Mario Martínez', 'Liliana Martínez', 'Pardo', 'Sabio',
        'San Lorenzo', 'Santiváñez', 'Zimmerman', 'Bolognesi', 'Cardoso', 'Esterkil',
        'Figueroa Sobrero', 'Gómez', 'Imhof', 'Lagger', 'Lagger Zurbriggen', 'Lencinas',
        'María Selva', 'Santiago', 'Vinderola', 'Coniglio', 'Dentesani', 'Finelli',
        'Fucksmann', 'Leiva', 'Mercado Central', 'Morante', 'Queglas', 'Las Flores II',
        'Salvatierra', 'Sileoni', 'Bosch', 'Amherdt', 'Bourdil', 'Escudero', 'Ghersi',
        'Jullier', 'Lauxmann', 'Theiller', 'Tio', 'Throendly', 'Timofiejuk', 'Wailloud',
        'Bordignon', 'Acosta', 'Alejandro Senn', 'Bonazzola Denise', 'Chelini', 'Giulioni',
        'López', 'Germán López', 'Mai', 'Martínez Juan', 'Méndez', 'Naito', 'Pasteur',
        'Rojas', 'Valetti', 'Barrientos', 'Diagonal', 'Clavé', 'Fanessi', 'Felo',
        'Judith Acevedo', 'Montes', 'Sen', 'Coltrinari', 'Suppo', 'Ugolini',
        'Argenti', 'Berron', 'Castro Karina', 'Facino', 'Labath', 'Mónica Wagner',
        'Rojas Sotelo', 'Pescetti', 'Pescetti Maximiliano', 'Scalzo', 'Vilarrubi',
        'Armando', 'Arrimada', 'Daniel Lagger', 'Del Barco', 'Burgués Romano',
        'Lucía Banchio', 'Mazzali', 'Pellegrini', 'Plank', 'Sobrero', 'Strada',
        'Assinari', 'Capra', 'Costa Samita', 'Caporizzo', 'Donadío', 'Junges',
        'Long', 'Mergen', 'Ortega', 'Pedro Kornijuk', 'Sartor', 'Valverde',
        'Verónica Cano', 'Vignolo', 'Bertolif', 'Chemes', 'Col', 'Damiani',
        'Domet Hurani', 'Gabriel Jauregui', 'Irrazabal', 'Nicolau Manzur', 'Pa',
        'Peiro', 'Stricker', 'Zapata Morán', 'García', 'Bonazzola Estefanía',
        'Brambilla', 'Buil', 'Coli', 'Giménez', 'Imvinkelried', 'Mansilla',
        'Menapace', 'Pescetti P', 'Ranzuglia', 'Wagner Burgués', 'Zeniner',
        // Santo Tomé
        'Erica Tepp', 'Stessens', 'Villata', 'Sauco', 'Olivero', 'Escobar',
        'Cirelli', 'Zimmermann', 'Marta Tepp', 'Bonino', 'Pescetti Julieta',
        'Berta', 'Cruz', 'Curado', 'Mayoráz', 'Macagno', 'Contini', 'Marcolini',
        'San Roque', 'Quassolo', 'Mariana Gómez', 'Terenzi', 'Firmani', 'Palacin',
    ];

    // Palabras que NO pueden ser nombres de farmacias (Movidos del Service principal)
    private array $stopwords = [
        'COLEGIO', 'FARMACEUTICOS', 'PROVINCIA', 'LEY', 'PRIMERA', 'CIRCI',
        'TURNO', 'URGENCIAS', 'TOXICOLOGICAS', 'HOSPITAL', 'ALASSIA', 'CULLEN',
        'PRIMER', 'SEGUNDO', 'TERCER', 'CUARTO', 'QUINTO', 'SEXTO', 'SEPTIMO',
        'OCTAVO', 'NOVENO', 'DECIMO', 'UNDECIMO', 'DUODECIMO', 'INSCRIPCION',
        'Desde', 'hasta', 'Tel', 'Loc'
    ];

    public function __construct()
    {
        $this->farmaciaMatching = app(\App\Services\FarmaciaMatchingService::class);
    }

    /**
     * Encontrar nombre conocido más cercano usando Levenshtein mejorado
     */
    public function encontrarNombreSimilar(string $nombreSucio): ?array
    {
        $nombreLimpio = strtolower(preg_replace('/[^a-zA-ZáéíóúñÁÉÍÓÚÑ\s]/', '', $nombreSucio));
        $mejorCoincidencia = null;
        $menorDistancia = PHP_INT_MAX;

        foreach ($this->nombresConocidos as $nombreConocido) {
            $nombreConocidoLower = strtolower($nombreConocido);
            
            // Calcular distancia Levenshtein
            $distancia = levenshtein(
                $nombreConocidoLower,
                substr($nombreLimpio, 0, strlen($nombreConocido) + 10)
            );

            // Umbral dinámico: 35% de la longitud del nombre conocido
            $umbral = max(3, (int)(strlen($nombreConocido) * 0.35));

            if ($distancia < $menorDistancia && $distancia <= $umbral) {
                $menorDistancia = $distancia;
                $mejorCoincidencia = [
                    'nombre' => $nombreConocido,
                    'distancia' => $distancia,
                    'confianza' => 100 - (($distancia / strlen($nombreConocido)) * 100)
                ];
            }
            
            // También verificar si el nombre sucio CONTIENE el nombre conocido
            if (strlen($nombreConocidoLower) >= 5 && stripos($nombreLimpio, $nombreConocidoLower) !== false) {
                $confianza = 95 - (abs(strlen($nombreLimpio) - strlen($nombreConocidoLower)) * 2);
                
                if ($confianza > 70 && (!$mejorCoincidencia || $confianza > $mejorCoincidencia['confianza'])) {
                    $mejorCoincidencia = [
                        'nombre' => $nombreConocido,
                        'distancia' => 0,
                        'confianza' => $confianza
                    ];
                }
            }
        }

        // Solo retornar si la confianza es mayor al 60%
        if ($mejorCoincidencia && $mejorCoincidencia['confianza'] >= 60) {
            return $mejorCoincidencia;
        }

        return null;
    }

    /**
     * Validación estricta de líneas
     */
    public function esLineaValidaDeFarmacia(string $line): bool
    {
        $upper = mb_strtoupper($line, 'UTF-8');
        
        // Rechazar stopwords
        foreach ($this->stopwords as $stopword) {
            if (stripos($upper, $stopword) !== false && strlen($line) < 40) {
                \Log::info("[Validación] Línea descartada por contener stopword '{$stopword}': {$line}");
                return false;
            }
        }

        // Rechazar líneas con demasiados caracteres especiales
        $caracteresRaros = preg_match_all('/[^a-zA-Z0-9\sáéíóúñÁÉÍÓÚÑ\.\-\/]/', $line);
        if ($caracteresRaros > 15) {
            \Log::info("[Validación] Línea descartada por exceso de caracteres raros ({$caracteresRaros}): {$line}");
            return false;
        }

        // Rechazar líneas muy cortas o muy largas
        $longitud = strlen($line);
        if ($longitud < 20 || $longitud > 200) {
            \Log::info("[Validación] Línea descartada por longitud inválida ({$longitud}): {$line}");
            return false;
        }

        // Debe tener al menos un número (dirección o teléfono)
        $tieneNumero = preg_match('/\b\d{3,5}\b/', $line);
        if (!$tieneNumero) {
            \Log::info("[Validación] Línea descartada por falta de números: {$line}");
            return false;
        }

        return true;
    }

    /**
     * Limpieza de nombres con fuzzy matching
     */
    public function limpiarNombreFarmacia(string $nombre): ?array
    {
        $nombreOriginal = $nombre;

        // 1) Intentar encontrar coincidencia con nombres conocidos PRIMERO
        $match = $this->encontrarNombreSimilar($nombre);
        if ($match) {
            \Log::info("[Limpieza] '{$nombreOriginal}' → '{$match['nombre']}' (confianza: {$match['confianza']}%)");
            return $match;
        }

        // 2) Si no hay coincidencia, limpiar manualmente
        $nombreLimpio = preg_replace('/\s+(nn|rrr|eee|uuu|cen|mer|nar|ana|ac|ee|rner|ene|nen|es|er)\s+/i', ' ', $nombre);
        $nombreLimpio = preg_replace('/\.{2,}/', '', $nombreLimpio);
        $nombreLimpio = preg_replace('/\s+/', ' ', $nombreLimpio);
        $nombreLimpio = trim($nombreLimpio);

        // Remover terminaciones raras
        $nombreLimpio = preg_replace('/[\-\.\s]+$/', '', $nombreLimpio);
        $nombreLimpio = preg_replace('/^[\-\.\s]+/', '', $nombreLimpio);

        // Validaciones de calidad
        if (strlen($nombreLimpio) < 4) {
            \Log::info("[Validación] Nombre muy corto después de limpieza: '{$nombreOriginal}'");
            return null;
        }

        if (preg_match_all('/\d/', $nombreLimpio) > 3) {
            \Log::info("[Validación] Demasiados números en nombre: '{$nombreOriginal}'");
            return null;
        }

        if (substr_count($nombreLimpio, '-') > 3 || substr_count($nombreLimpio, '.') > 3) {
            \Log::info("[Validación] Demasiados separadores en nombre: '{$nombreOriginal}'");
            return null;
        }

        if (!preg_match('/[a-zA-ZáéíóúñÁÉÍÓÚÑ]{3,}/', $nombreLimpio)) {
            \Log::info("[Validación] No hay suficientes letras consecutivas: '{$nombreOriginal}'");
            return null;
        }

        \Log::info("[Limpieza] '{$nombreOriginal}' → '{$nombreLimpio}' (limpieza manual, sin match)");
        
        return [
            'nombre' => $nombreLimpio,
            'confianza' => 50, // Baja confianza si no coincide con nombres conocidos
            'distancia' => -1
        ];
    }

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
     * Extracción de direcciones más robusta
     */
    public function extractAddress(string $line): ?string
    {
        $lineaLimpia = preg_replace('/\s+(nn|ac|ee|rrr|eee)\s+/i', ' ', $line);

        // Patrones de calles argentinas comunes
        $patrones = [
            '/((Av\.?|Avenida|Bv\.?|Boulevard|Calle|Diagonal|San|Dr\.?|Dra\.?|Gral\.?|Marcial|Mariano|Stgo\.?|Santiago|Fdo\.?|Fernando|Fray|Gobernador|Angel|Padre|Ejército|Obispo|Hipólito)[^\d\n]*\d{1,5})/iu',
            '/([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+(?:\s+[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+){0,3})\s+(\d{1,5})\b/u',
        ];

        foreach ($patrones as $patron) {
            if (preg_match($patron, $lineaLimpia, $m)) {
                $dir = trim($m[1] ?? $m[0]);
                $dir = preg_replace('/\.{2,}/', '', $dir);
                $dir = preg_replace('/\s+/', ' ', $dir);
                
                // Validar que la dirección tenga sentido
                if (strlen($dir) > 8 && preg_match('/\d/', $dir)) {
                    return trim($dir);
                }
            }
        }

        return null;
    }

    /**
     * Extracción de nombres más inteligente
     */
    public function extractName(string $line, ?string $direccion, ?string $telefono): string
    {
        Log::info("[OCR] Extrayendo nombre de farmacia. Línea original: '{$line}'");

        $tmp = $line;

        // Remover teléfono
        if ($telefono) {
            $pattern = '/' . preg_quote($telefono, '/') . '.*$/';
            $tmp = preg_replace($pattern, '', $tmp);
        }

        // Remover dirección (MEJORADO: también remover fragmentos incompletos)
        if ($direccion) {
            $tmp = str_ireplace($direccion, '', $tmp);
            
            // También remover solo la calle si queda
            $partesDir = explode(' ', $direccion);
            if (count($partesDir) > 0) {
                foreach ($partesDir as $parte) {
                    if (strlen($parte) > 4) {
                        $tmp = str_ireplace($parte, '', $tmp);
                    }
                }
            }
        }

        // Remover palabras comunes de direcciones
        $palabrasDireccion = ['Avenida', 'Boulevard', 'Calle', 'Diagonal', 'Bulevar', 'Av.', 'Bv.'];
        foreach ($palabrasDireccion as $palabra) {
            $tmp = str_ireplace($palabra, '', $tmp);
        }

        // Remover números sueltos (probablemente parte de dirección/teléfono)
        $tmp = preg_replace('/\b\d{3,}\b/', '', $tmp);

        // Limpiar basura de OCR
        $tmp = preg_replace('/[@\.\-]{2,}/', ' ', $tmp);
        $tmp = preg_replace('/[=£\*E27]+/', '', $tmp);
        $tmp = preg_replace('/\.{2,}/', '', $tmp);

        // Mantener solo caracteres válidos
        $tmp = preg_replace('/[^\w\sáéíóúñÁÉÍÓÚÑ\.\-]/u', '', $tmp);

        $final = trim($tmp);
        Log::info("[OCR] Nombre final extraído: '{$final}'");

        return $final;
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