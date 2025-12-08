<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Farmacia;

class TurnoFarmaciaSeeder extends Seeder
{
    public function run()
    {
        $santaFeId = Ciudad::where('nombre_ciudad', 'Santa Fe')->value('id_ciudad');
        $santoTomeId = Ciudad::where('nombre_ciudad', 'Santo Tomé')->value('id_ciudad');

        $pharmaciesData = [
            // Santa Fe
            'sf_1' => ['name' => 'Farmacia Banchio', 'dutyDates' => ["2025-10-01", "2025-10-13", "2025-10-25"]],
            'sf_2' => ['name' => 'Farmacia Belgrano', 'dutyDates' => ["2025-10-01", "2025-10-13", "2025-10-25"]],
            'sf_3' => ['name' => 'Farmacia Amherdt', 'dutyDates' => ["2025-10-02", "2025-10-14", "2025-10-26"]],
            'sf_4' => ['name' => 'Farmacia Bourdin', 'dutyDates' => ["2025-10-02", "2025-10-14", "2025-10-26"]],
            'sf_5' => ['name' => 'Farmacia Bonazzola', 'dutyDates' => ["2025-10-02", "2025-10-14", "2025-10-26"]],
            'sf_6' => ['name' => 'Farmacia Escudero', 'dutyDates' => ["2025-10-03", "2025-10-15", "2025-10-27"]],
            'sf_7' => ['name' => 'Farmacia Armando', 'dutyDates' => ["2025-10-03", "2025-10-15", "2025-10-27"]],
            'sf_8' => ['name' => 'Farmacia Arrimada', 'dutyDates' => ["2025-10-04", "2025-10-16", "2025-10-28"]],
            'sf_9' => ['name' => 'Farmacia Bruno', 'dutyDates' => ["2025-10-04", "2025-10-16", "2025-10-28"]],
            'sf_10' => ['name' => 'Farmacia El Inca', 'dutyDates' => ["2025-10-05", "2025-10-17", "2025-10-29"]],
            'sf_11' => ['name' => 'Farmacia Camilatto', 'dutyDates' => ["2025-10-05", "2025-10-17", "2025-10-29"]],
            'sf_12' => ['name' => 'Farmacia Jullier', 'dutyDates' => ["2025-10-06", "2025-10-18", "2025-10-30"]],
            'sf_13' => ['name' => 'Farmacia Burgués Romano', 'dutyDates' => ["2025-10-06", "2025-10-18", "2025-10-30"]],
            'sf_14' => ['name' => 'Farmacia Camusso', 'dutyDates' => ["2025-10-07", "2025-10-19", "2025-10-22"]],
            'sf_15' => ['name' => 'Farmacia Lauxmann', 'dutyDates' => ["2025-10-07", "2025-10-19"]],

            // Santo Tomé
            'st_1' => ['name' => 'Farmacia Erica Tepp', 'dutyDates' => ["2025-10-06", "2025-10-15", "2025-10-24"]],
            'st_2' => ['name' => 'Farmacia Stessens', 'dutyDates' => ["2025-10-06", "2025-10-15", "2025-10-24"]],
            'st_3' => ['name' => 'Farmacia Daniel Lagger', 'dutyDates' => ["2025-10-07", "2025-10-16", "2025-10-25"]],
            'st_4' => ['name' => 'Farmacia Villata', 'dutyDates' => ["2025-10-07", "2025-10-16", "2025-10-25"]],
            'st_5' => ['name' => 'Farmacia Ghersi', 'dutyDates' => ["2025-10-08", "2025-10-17", "2025-10-26"]],
            'st_6' => ['name' => 'Farmacia Capra', 'dutyDates' => ["2025-10-08", "2025-10-17", "2025-10-26"]],
        ];

        // 1. Procesar Grupos de Santa Fe
        foreach ($santaFeGroups as $groupKey => $data) {
            $this->assignTurns($data['pharmacies'], $data['dates']);
        }

        // 2. Procesar Grupos de Santo Tomé
        foreach ($santoTomeGroups as $groupKey => $data) {
            $this->assignTurns($data['pharmacies'], $data['dates']);
        }

        // 3. Procesar Excepciones
        foreach ($excepciones as $exc) {
            $this->assignTurns([$exc['nombre']], $exc['fechas']);
        }
    }

    /**
     * Función auxiliar para asignar fechas a una lista de farmacias
     */
    private function assignTurns($pharmacyNames, $dates)
    {
        foreach ($pharmacyNames as $name) {
            $farmacia = Farmacia::where('nombre', $name)->first();

            if ($farmacia) {
                foreach ($dates as $date) {
                    // Crea un registro en la tabla turnos (Uno nuevo por cada fecha/farmacia según tu estructura original)
                    $turnoId = DB::table('turnos')->insertGetId([
                        'nombre_turno' => 'Turno ' . $date,
                        'fecha_hora_inicio' => $date . ' 08:00:00',
                        // El turno termina a las 8 AM del día siguiente
                        'fecha_hora_fin' => date('Y-m-d', strtotime($date . ' +1 day')) . ' 08:00:00',
                        'id_ciudad' => $farmacia->id_ciudad,
                    ]);

                    // Crea el registro de relación
                    DB::table('farmacias_turnos')->insert([
                        'id_farmacia' => $farmacia->id_farmacia,
                        'id_turno' => $turnoId,
                    ]);
                }
            } else {
                // Opcional: Loguear si no se encuentra alguna farmacia (útil para debug)
                // echo "Warning: Farmacia no encontrada: $name \n";
            }
        }
    }
}
