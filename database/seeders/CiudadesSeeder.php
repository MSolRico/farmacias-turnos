<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CiudadesSeeder extends Seeder
{
    public function run()
    {
        DB::table('ciudades')->insert([
            ['nombre_ciudad' => 'Santa Fe'],
            ['nombre_ciudad' => 'Santo Tomé'],
        ]);
    }
}
