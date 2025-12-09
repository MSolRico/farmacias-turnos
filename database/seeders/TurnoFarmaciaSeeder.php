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
        // ATENCIÓN: Esto trunca las tablas 'farmacias_turnos' y 'turnos'.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('farmacias_turnos')->truncate();
        DB::table('turnos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // --- DEFINICIÓN DE GRUPOS DE FECHAS (DICIEMBRE 2025) ---

        $santaFeGroups = [
            // Turno 1 (Fechas: 01, 13, 25)
            'SF_TURNO_1' => [
                'dates' => ['2025-12-01', '2025-12-13', '2025-12-25'],
                'pharmacies' => ['Irigoyen Freyre', 'Banchio', 'Belgrano', 'Bonazzola', 'Bruno', 'Camilatto', 'Camusso', 'Costa', 'Gheco', 'Morales', 'Morello', 'Nebiolo']
            ],
            // Turno 2 (Fechas: 02, 14, 26)
            'SF_TURNO_2' => [
                'dates' => ['2025-12-02', '2025-12-14', '2025-12-26'],
                'pharmacies' => ['Amherdt', 'Bourdin', 'Escudero', 'Ghersi', 'Jullier', 'Lauxmann', 'Theiller', 'Trucco', 'Throendly', 'Timofiejuk', 'Wuilloud']
            ],
            // Turno 3 (Fechas: 03, 15, 27)
            'SF_TURNO_3' => [
                'dates' => ['2025-12-03', '2025-12-15', '2025-12-27'],
                'pharmacies' => ['Armando', 'Arrimada', 'Daniel Lagger', 'El Inca', 'Burgués Romano', 'Lucía Banchio', 'Mazzali', 'Pellegrini', 'Plank', 'Sobrero', 'Strada']
            ],
            // Turno 4 (Fechas: 04, 16, 28)
            'SF_TURNO_4' => [
                'dates' => ['2025-12-04', '2025-12-16', '2025-12-28'],
                'pharmacies' => ['Abregu', 'Ignacio Azanza', 'Gottero', 'Liliana Martinez', 'Luero', 'Marcelo Galizzi', 'Mario Martinez', 'Pardo', 'Sabio', 'San Lorenzo', 'Santiváñez', 'Tonini', 'Zimmerman']
            ],
            // Turno 5 (Fechas: 05, 17, 29)
            'SF_TURNO_5' => [
                'dates' => ['2025-12-05', '2025-12-17', '2025-12-29'],
                'pharmacies' => ['Acosta', 'Alejandro Senn', 'Bonazzola Denise', 'Chelini', 'Giulioni', 'López', 'Mai', 'Martínez Juan José', 'Méndez', 'Naito', 'Pasteur', 'Rojas', 'Valetti']
            ],
            // Turno 6 (Fechas: 06, 18, 30)
            'SF_TURNO_6' => [
                'dates' => ['2025-12-06', '2025-12-18', '2025-12-30'],
                'pharmacies' => ['Capra', 'Costa Samita', 'Caporizzo', 'Donadio', 'Junges', 'Long', 'Mergen', 'Ortega', 'Pedro A. Kornijuk', 'Sartor', 'Valverde', 'Verónica Cano', 'Vignolo']
            ],
            // Turno 7 (Fechas: 07, 19, 31)
            'SF_TURNO_7' => [
                'dates' => ['2025-12-07', '2025-12-19', '2025-12-31'],
                'pharmacies' => ['Bolognesi', 'Cardoso', 'Curti', 'Esterkin', 'Figueroa Sobrero', 'Gómez', 'Imhof', 'Lagger Zurbriggen', 'Lencinas', 'María Selva', 'Santiago', 'Vinderola']
            ],
            // Turno 8 (Fechas: 08, 20)
            'SF_TURNO_8' => [
                'dates' => ['2025-12-08', '2025-12-20'],
                'pharmacies' => ['Barrientos', 'Caraballo', 'Clave', 'Fanessi', 'Ferro', 'Judith Acevedo', 'Montes', 'Santa Fe', 'Sen - Coltrinari', 'Suppo', 'Ugolini']
            ],
            // Turno 9 (Fechas: 09, 21)
            'SF_TURNO_9' => [
                'dates' => ['2025-12-09', '2025-12-21'],
                'pharmacies' => ['Bertolin', 'Chemes', 'Coll', 'Damiani', 'Domet Hurani', 'Gabriel Jauregui', 'Irrazabal', 'Nicolau Manzur', 'Pacce', 'Peiro', 'Stricker', 'Zapata Morán']
            ],
            // Turno 10 (Fechas: 10, 22)
            'SF_TURNO_10' => [
                'dates' => ['2025-12-10', '2025-12-22'],
                'pharmacies' => ['Coniglio', 'Dentesani', 'Finelli', 'Fucksmann', 'Leiva', 'Mercado Central', 'Morante', 'Queglas', 'Salvatierra', 'Sileoni']
            ],
            // Turno 11 (Fechas: 11, 23)
            'SF_TURNO_11' => [
                'dates' => ['2025-12-11', '2025-12-23'],
                'pharmacies' => ['Argenti', 'Berron', 'Castro Karina', 'Facino', 'Labath', 'Martínez', 'Mónica Wagner', 'Rita Martínez', 'Rojas Sotelo', 'Pescetti Maximiliano', 'Scalzo', 'Vilarrubi']
            ],
            // Turno 12 (Fechas: 12, 24)
            'SF_TURNO_12' => [
                'dates' => ['2025-12-12', '2025-12-24'],
                'pharmacies' => ['Bonazzola Estefania', 'Brambilla', 'Burgi', 'Colucci', 'Germán López', 'Giménez', 'Imvinkelried', 'Mansilla', 'Menapace', 'Pescetti']
            ],
        ];

        // --- SANTO TOMÉ (Corregido según PDF Diciembre) ---
        $santoTomeGroups = [
            'ST_TURNO_1' => [
                'dates' => ['2025-12-05', '2025-12-14', '2025-12-23'],
                'pharmacies' => ['Erica Tepp', 'Stessens', 'Villata']
            ],
            'ST_TURNO_2' => [
                'dates' => ['2025-12-06', '2025-12-15', '2025-12-24'],
                'pharmacies' => ['Sauco', 'Olivero', 'Escobar']
            ],
            'ST_TURNO_3' => [
                'dates' => ['2025-12-07', '2025-12-16', '2025-12-25'],
                'pharmacies' => ['Cirelli', 'Gómez', 'Zimmermann'] // Ojo: en FarmaciaSeeder es 'Gómez', no 'Gómez Sto Tomé'
            ],
            'ST_TURNO_4' => [
                'dates' => ['2025-12-08', '2025-12-17', '2025-12-26'],
                'pharmacies' => ['Marta Tepp', 'Bonino', 'Martínez'] // Ojo: en FarmaciaSeeder es 'Martínez', no 'Martínez Sto Tomé'
            ],
            'ST_TURNO_5' => [
                'dates' => ['2025-11-30', '2025-12-09', '2025-12-18', '2025-12-27'],
                'pharmacies' => ['Pescetti Julieta', 'Berta', 'Cruz', 'Curado']
            ],
            'ST_TURNO_6' => [
                'dates' => ['2025-12-01', '2025-12-10', '2025-12-19', '2025-12-28'],
                'pharmacies' => ['Mayoráz', 'Macagno', 'Tosello']
            ],
            'ST_TURNO_7' => [
                'dates' => ['2025-12-02', '2025-12-11', '2025-12-20', '2025-12-29'],
                'pharmacies' => ['Contini', 'Marcolini', 'San Roque']
            ],
            'ST_TURNO_8' => [
                'dates' => ['2025-12-03', '2025-12-12', '2025-12-21', '2025-12-30'],
                'pharmacies' => ['Quassolo', 'Mariana Gómez', 'Rivero', 'Terenzi']
            ],
            'ST_TURNO_9' => [
                'dates' => ['2025-12-04', '2025-12-13', '2025-12-22', '2025-12-31'],
                'pharmacies' => ['Adrián Carrizo', 'Firmani', 'Palacin']
            ],
        ];

        // --- EXCEPCIONES Y NOTAS ESPECIALES (DICIEMBRE) ---
        $excepciones = [
            // Azanza (ID 13) -> Turno 1 (Azanza solo tiene el turno 1)
            ['nombre' => 'Ortiz de Zárate', 'fechas' => ['2025-12-01']],
            // Bordignon (ID 25) -> Turno 2 (Ortiz de Zárate y Azanza rotan, Ortiz de Zárate está en T1, Azanza aparte)
            ['nombre' => 'Ortiz de Zárate', 'fechas' => ['2025-12-01']], // Excepción Azanza (en el primero solo tiene 12), Asumí Ortiz de Zárate para completar el T1
            // Asinari (ID 37) -> Turno 3
            ['nombre' => 'Ortiz de Zárate', 'fechas' => ['2025-12-03']], // Excepción Bordignon. Asumí Ortiz de Zárate para el T2
            ['nombre' => 'Ortiz de Zárate', 'fechas' => ['2025-12-03']], // Excepción Asinari
            // García (ID 111) -> Turno 9
            ['nombre' => 'Ortiz de Zárate', 'fechas' => ['2025-12-09']], // Excepción García
            // Bosch (ID 122) -> Turno 10
            ['nombre' => 'Ortiz de Zárate', 'fechas' => ['2025-12-10']], // Excepción Bosch
            // Wagner Burgués y Zentner (IDs 146, 147) -> Turno 12
            ['nombre' => 'Wagner Burgués', 'fechas' => ['2025-12-12']],
            ['nombre' => 'Zentner', 'fechas' => ['2025-12-12']],
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
