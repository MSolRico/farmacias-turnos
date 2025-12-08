<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Farmacia;

class TurnoFarmaciaSeeder extends Seeder
{
    public function run()
    {
        // Limpiar tablas intermedias antes de sembrar para evitar duplicados
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('farmacias_turnos')->truncate();
        DB::table('turnos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // --- DEFINICIÓN DE GRUPOS DE FECHAS (NOVIEMBRE 2025) ---
        // Se definen los grupos de turnos y las farmacias que pertenecen a ellos.
        // Esto optimiza el código evitando repetir arrays de fechas por cada farmacia.

        $santaFeGroups = [
            'SF_TURNO_1' => [
                'dates' => ['2025-10-31', '2025-11-12', '2025-11-24'],
                'pharmacies' => ['Irigoyen Freyre', 'Banchio', 'Belgrano', 'Bonazzola', 'Bruno', 'Camilatto', 'Camusso', 'Costa', 'Gheco', 'Morales', 'Morello', 'Nebiolo', 'Ortiz de Zárate']
            ],
            'SF_TURNO_2' => [
                'dates' => ['2025-11-01', '2025-11-13', '2025-11-25'],
                'pharmacies' => ['Amherdt', 'Bourdin', 'Escudero', 'Ghersi', 'Jullier', 'Lauxmann', 'Theiller', 'Trucco', 'Throendly', 'Timofiejuk', 'Wuilloud']
            ],
            'SF_TURNO_3' => [
                'dates' => ['2025-11-02', '2025-11-14', '2025-11-26'],
                'pharmacies' => ['Armando', 'Arrimada', 'Daniel Lagger', 'El Inca', 'Burgués Romano', 'Lucía Banchio', 'Mazzali', 'Pellegrini', 'Plank', 'Sobrero', 'Strada']
            ],
            'SF_TURNO_4' => [
                'dates' => ['2025-11-03', '2025-11-15', '2025-11-27'],
                'pharmacies' => ['Abregu', 'Ignacio Azanza', 'Gottero', 'Liliana Martinez', 'Luero', 'Marcelo Galizzi', 'Mario Martinez', 'Pardo', 'Sabio', 'San Lorenzo', 'Santiváñez', 'Tonini', 'Zimmerman']
            ],
            'SF_TURNO_5' => [
                'dates' => ['2025-11-04', '2025-11-16', '2025-11-28'],
                'pharmacies' => ['Acosta', 'Alejandro Senn', 'Bonazzola Denise', 'Chelini', 'Giulioni', 'López', 'Mai', 'Martínez Juan José', 'Méndez', 'Naito', 'Pasteur', 'Rojas', 'Valetti']
            ],
            'SF_TURNO_6' => [
                'dates' => ['2025-11-05', '2025-11-17', '2025-11-29'],
                'pharmacies' => ['Capra', 'Costa Samita', 'Caporizzo', 'Donadio', 'Junges', 'Long', 'Mergen', 'Ortega', 'Pedro A. Kornijuk', 'Sartor', 'Valverde', 'Verónica Cano', 'Vignolo']
            ],
            'SF_TURNO_7' => [
                'dates' => ['2025-11-06', '2025-11-18', '2025-11-30'],
                'pharmacies' => ['Bolognesi', 'Cardoso', 'Curti', 'Esterkin', 'Figueroa Sobrero', 'Gómez', 'Imhof', 'Lagger Zurbriggen', 'Lencinas', 'María Selva', 'Santiago', 'Vinderola']
            ],
            'SF_TURNO_8' => [
                'dates' => ['2025-11-07', '2025-11-19'], // Solo tiene 2 fechas en Noviembre visible
                'pharmacies' => ['Barrientos', 'Caraballo', 'Clave', 'Fanessi', 'Ferro', 'Judith Acevedo', 'Montes', 'Santa Fe', 'Sen - Coltrinari', 'Suppo', 'Ugolini']
            ],
            'SF_TURNO_9' => [
                'dates' => ['2025-11-08', '2025-11-20'],
                'pharmacies' => ['Bertolin', 'Chemes', 'Coll', 'Damiani', 'Domet Hurani', 'Gabriel Jauregui', 'Irrazabal', 'Nicolau Manzur', 'Pacce', 'Peiro', 'Stricker', 'Zapata Morán']
            ],
            'SF_TURNO_10' => [
                'dates' => ['2025-11-09', '2025-11-21'],
                'pharmacies' => ['Coniglio', 'Dentesani', 'Finelli', 'Fucksmann', 'Leiva', 'Mercado Central', 'Morante', 'Queglas', 'Salvatierra', 'Sileoni']
            ],
            'SF_TURNO_11' => [
                'dates' => ['2025-11-10', '2025-11-22'],
                'pharmacies' => ['Argenti', 'Berron', 'Castro Karina', 'Facino', 'Labath', 'Martínez', 'Mónica Wagner', 'Rita Martínez', 'Rojas Sotelo', 'Pescetti Maximiliano', 'Scalzo', 'Vilarrubi']
            ],
            'SF_TURNO_12' => [
                'dates' => ['2025-11-11', '2025-11-23'],
                'pharmacies' => ['Bonazzola Estefania', 'Brambilla', 'Burgi', 'Colucci', 'Germán López', 'Giménez', 'Imvinkelried', 'Mansilla', 'Menapace', 'Pescetti']
            ],
        ];

        $santoTomeGroups = [
            'ST_TURNO_1' => [
                'dates' => ['2025-11-08', '2025-11-17', '2025-11-26'],
                'pharmacies' => ['Erica Tepp', 'Stessens', 'Villata']
            ],
            'ST_TURNO_2' => [
                'dates' => ['2025-10-31', '2025-11-09', '2025-11-18', '2025-11-27'],
                'pharmacies' => ['Sauco', 'Olivero', 'Escobar']
            ],
            'ST_TURNO_3' => [
                'dates' => ['2025-11-01', '2025-11-10', '2025-11-19', '2025-11-28'],
                'pharmacies' => ['Cirelli', 'Gómez Sto Tomé', 'Zimmermann']
            ],
            'ST_TURNO_4' => [
                'dates' => ['2025-11-02', '2025-11-11', '2025-11-20', '2025-11-29'],
                'pharmacies' => ['Marta Tepp', 'Bonino', 'Martínez Sto Tomé']
            ],
            'ST_TURNO_5' => [
                'dates' => ['2025-11-03', '2025-11-12', '2025-11-21', '2025-11-30'],
                'pharmacies' => ['Pescetti Julieta', 'Berta', 'Cruz', 'Curado']
            ],
            'ST_TURNO_6' => [
                'dates' => ['2025-11-04', '2025-11-13', '2025-11-22'],
                'pharmacies' => ['Mayoráz', 'Macagno', 'Tosello']
            ],
            'ST_TURNO_7' => [
                'dates' => ['2025-11-05', '2025-11-14', '2025-11-23'],
                'pharmacies' => ['Contini', 'Marcolini', 'San Roque']
            ],
            'ST_TURNO_8' => [
                'dates' => ['2025-11-06', '2025-11-15', '2025-11-24'],
                'pharmacies' => ['Quassolo', 'Mariana Gómez', 'Rivero', 'Terenzi']
            ],
            'ST_TURNO_9' => [
                'dates' => ['2025-11-07', '2025-11-16', '2025-11-25'],
                'pharmacies' => ['Adrián Carrizo', 'Firmani', 'Palacin']
            ],
        ];

        // --- EXCEPCIONES Y NOTAS ESPECIALES (Según Afiche) ---
        // Estas farmacias no siguen el patrón completo de su grupo
        $excepciones = [
            ['nombre' => 'Azanza', 'fechas' => ['2025-11-12']],
            ['nombre' => 'Bordignon', 'fechas' => ['2025-11-01']],
            ['nombre' => 'Asinari', 'fechas' => ['2025-11-02']],
            ['nombre' => 'García', 'fechas' => ['2025-11-08']],
            ['nombre' => 'Bosch', 'fechas' => ['2025-11-09']],
            ['nombre' => 'Wagner Burgués', 'fechas' => ['2025-11-11']],
            ['nombre' => 'Zentner', 'fechas' => ['2025-11-11']],
        ];

        // 1. Procesar Grupos de Santa Fe
        foreach ($santaFeGroups as $groupKey => $data) {
            $this->assignTurns($data['pharmacies'], $data['dates']);
        }

        // 2. Procesar Grupos de Santo Tomé
        foreach ($santoTomeGroups as $groupKey => $data) {
            $this->assignTurns($data['pharmacies'], $data['dates']);
        }

        // 3. Procesar Excepciones (usando nombres en la segunda estructura)
        foreach ($excepciones as $exc) {
            $this->assignTurns([$exc['nombre']], $exc['fechas']);
        }
    }

    /**
     * Función auxiliar para asignar fechas a una lista de farmacias.
     * Crea un nuevo registro de turno por cada fecha/farmacia y luego la relación.
     */
    private function assignTurns($pharmacyNames, $dates)
    {
        foreach ($pharmacyNames as $name) {
            // Se usa el modelo Farmacia para buscar por nombre (asumiendo que 'nombre' es único en la ciudad o que la farmacia ya existe)
            $farmacia = Farmacia::where('nombre', $name)->first();

            if ($farmacia) {
                foreach ($dates as $date) {
                    // Crea un registro en la tabla 'turnos'
                    $turnoId = DB::table('turnos')->insertGetId([
                        'nombre_turno' => 'Turno ' . $date . ' - ' . $farmacia->nombre,
                        'fecha_hora_inicio' => $date . ' 08:00:00',
                        // El turno termina a las 8 AM del día siguiente (24 horas después)
                        'fecha_hora_fin' => date('Y-m-d', strtotime($date . ' +1 day')) . ' 08:00:00',
                        'id_ciudad' => $farmacia->id_ciudad,
                    ]);

                    // Crea el registro de relación en 'farmacias_turnos'
                    DB::table('farmacias_turnos')->insert([
                        'id_farmacia' => $farmacia->id_farmacia,
                        'id_turno' => $turnoId,

                    ]);
                }
            } else {
            }
        }
    }
}
