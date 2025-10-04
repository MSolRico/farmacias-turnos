<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Farmacia;
use App\Models\Ciudad;

class TurnoFarmaciaSeeder extends Seeder
{
    public function run()
    {
        $santaFeId = Ciudad::where('nombre_ciudad', 'Santa Fe')->value('id_ciudad');
        $santoTomeId = Ciudad::where('nombre_ciudad', 'Santo Tomé')->value('id_ciudad');

        $pharmaciesData = [
            // Santa Fe
            'sf_1' => ['name' => 'Farmacia Banchio', 'dutyDates' => ["2025-09-01", "2025-09-13", "2025-09-25"]],
            'sf_2' => ['name' => 'Farmacia Belgrano', 'dutyDates' => ["2025-09-01", "2025-09-13", "2025-09-25"]],
            'sf_3' => ['name' => 'Farmacia Amherdt', 'dutyDates' => ["2025-09-02", "2025-09-14", "2025-09-26"]],
            'sf_4' => ['name' => 'Farmacia Bourdin', 'dutyDates' => ["2025-09-02", "2025-09-14", "2025-09-26"]],
            'sf_5' => ['name' => 'Farmacia Bonazzola', 'dutyDates' => ["2025-09-02", "2025-09-14", "2025-09-26"]],
            'sf_6' => ['name' => 'Farmacia Escudero', 'dutyDates' => ["2025-09-03", "2025-09-15", "2025-09-27"]],
            'sf_7' => ['name' => 'Farmacia Armando', 'dutyDates' => ["2025-09-03", "2025-09-15", "2025-09-27"]],
            'sf_8' => ['name' => 'Farmacia Arrimada', 'dutyDates' => ["2025-09-04", "2025-09-16", "2025-09-28"]],
            'sf_9' => ['name' => 'Farmacia Bruno', 'dutyDates' => ["2025-09-04", "2025-09-16", "2025-09-28"]],
            'sf_10' => ['name' => 'Farmacia El Inca', 'dutyDates' => ["2025-09-05", "2025-09-17", "2025-09-29"]],
            'sf_11' => ['name' => 'Farmacia Camilatto', 'dutyDates' => ["2025-09-05", "2025-09-17", "2025-09-29"]],
            'sf_12' => ['name' => 'Farmacia Jullier', 'dutyDates' => ["2025-09-06", "2025-09-18", "2025-09-30"]],
            'sf_13' => ['name' => 'Farmacia Burgués Romano', 'dutyDates' => ["2025-09-06", "2025-09-18", "2025-09-30"]],
            'sf_14' => ['name' => 'Farmacia Camusso', 'dutyDates' => ["2025-09-07", "2025-09-19"]],
            'sf_15' => ['name' => 'Farmacia Lauxmann', 'dutyDates' => ["2025-09-07", "2025-09-19"]],

            // Santo Tomé
            'st_1' => ['name' => 'Farmacia Erica Tepp', 'dutyDates' => ["2025-09-06", "2025-09-15", "2025-09-24"]],
            'st_2' => ['name' => 'Farmacia Stessens', 'dutyDates' => ["2025-09-06", "2025-09-15", "2025-09-24"]],
            'st_3' => ['name' => 'Farmacia Daniel Lagger', 'dutyDates' => ["2025-09-07", "2025-09-16", "2025-09-25"]],
            'st_4' => ['name' => 'Farmacia Villata', 'dutyDates' => ["2025-09-07", "2025-09-16", "2025-09-25"]],
            'st_5' => ['name' => 'Farmacia Ghersi', 'dutyDates' => ["2025-09-08", "2025-09-17", "2025-09-26"]],
            'st_6' => ['name' => 'Farmacia Capra', 'dutyDates' => ["2025-09-08", "2025-09-17", "2025-09-26"]],
        ];

        foreach ($pharmaciesData as $id => $data) {
            $farmacia = Farmacia::where('nombre', $data['name'])->first();

            if ($farmacia) {
                foreach ($data['dutyDates'] as $date) {
                    // Crea un registro en la tabla turnos
                    $turnoId = DB::table('turnos')->insertGetId([
                        'nombre_turno' => 'Turno ' . $date,
                        'fecha_hora_inicio' => $date . ' 08:00:00',
                        'fecha_hora_fin' => $date . ' 20:00:00',
                        'id_ciudad' => $farmacia->id_ciudad,
                    ]);

                    // Crea el registro de relación en la tabla farmacias_turnos
                    DB::table('farmacias_turnos')->insert([
                        'id_farmacia' => $farmacia->id_farmacia,
                        'id_turno' => $turnoId,
                    ]);
                }
            }
        }
    }
}