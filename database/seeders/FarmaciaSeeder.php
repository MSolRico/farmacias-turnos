<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Ciudad;

class FarmaciaSeeder extends Seeder
{
    public function run()
    {
        $santaFeId = Ciudad::where('nombre_ciudad', 'Santa Fe')->value('id_ciudad');
        $santoTomeId = Ciudad::where('nombre_ciudad', 'Santo Tomé')->value('id_ciudad');

        $pharmacies = [
            // SANTA FE
            [ 'nombre' => 'Farmacia Banchio', 'direccion' => 'Irigoyen Freyre 2200, Santa Fe', 'telefono' => '452 2268', 'lat' => -31.6107, 'lng' => -60.6973, 'id_ciudad' => $santaFeId ],
            [ 'nombre' => 'Farmacia Belgrano', 'direccion' => 'Rivadavia 3237, Santa Fe', 'telefono' => '456 1118', 'lat' => -31.6117, 'lng' => -60.6983, 'id_ciudad' => $santaFeId ],
            [ 'nombre' => 'Farmacia Amherdt', 'direccion' => 'Av. Freyre 2298, Santa Fe', 'telefono' => '452 7939', 'lat' => -31.6127, 'lng' => -60.6993, 'id_ciudad' => $santaFeId ],
            [ 'nombre' => 'Farmacia Bourdin', 'direccion' => 'Saavedra 2498, Santa Fe', 'telefono' => '452 4846', 'lat' => -31.6137, 'lng' => -60.7003, 'id_ciudad' => $santaFeId ],
            [ 'nombre' => 'Farmacia Bonazzola', 'direccion' => 'Alberdi 3500, Santa Fe', 'telefono' => '452 8066', 'lat' => -31.6147, 'lng' => -60.7013, 'id_ciudad' => $santaFeId ],
            [ 'nombre' => 'Farmacia Escudero', 'direccion' => 'Av. Galicia 1881, Santa Fe', 'telefono' => '460 7386', 'lat' => -31.6157, 'lng' => -60.7023, 'id_ciudad' => $santaFeId ],
            [ 'nombre' => 'Farmacia Armando', 'direccion' => '25 de Mayo 3441, Santa Fe', 'telefono' => '452 0603', 'lat' => -31.6167, 'lng' => -60.7033, 'id_ciudad' => $santaFeId ],
            [ 'nombre' => 'Farmacia Arrimada', 'direccion' => 'Marcial Candioti 3298, Santa Fe', 'telefono' => '456 1397', 'lat' => -31.6177, 'lng' => -60.7043, 'id_ciudad' => $santaFeId ],
            [ 'nombre' => 'Farmacia Bruno', 'direccion' => 'Av. Gral. Paz 5550, Santa Fe', 'telefono' => '460 2204', 'lat' => -31.6187, 'lng' => -60.7053, 'id_ciudad' => $santaFeId ],
            [ 'nombre' => 'Farmacia El Inca', 'direccion' => 'San Martín 2255, Santa Fe', 'telefono' => '452 1747', 'lat' => -31.6197, 'lng' => -60.7063, 'id_ciudad' => $santaFeId ],
            [ 'nombre' => 'Farmacia Camilatto', 'direccion' => 'Estanislao Zeballos 2702, Santa Fe', 'telefono' => '460 3413', 'lat' => -31.6207, 'lng' => -60.7073, 'id_ciudad' => $santaFeId ],
            [ 'nombre' => 'Farmacia Jullier', 'direccion' => 'Av. Freyre 3313, Santa Fe', 'telefono' => '455 2838', 'lat' => -31.6217, 'lng' => -60.7083, 'id_ciudad' => $santaFeId ],
            [ 'nombre' => 'Farmacia Burgués Romano', 'direccion' => 'Av. Blas Parera 7001, Santa Fe', 'telefono' => '489 1807', 'lat' => -31.6227, 'lng' => -60.7093, 'id_ciudad' => $santaFeId ],
            [ 'nombre' => 'Farmacia Camusso', 'direccion' => '4 de Enero 1594, Santa Fe', 'telefono' => '459 4678', 'lat' => -31.6237, 'lng' => -60.7103, 'id_ciudad' => $santaFeId ],
            [ 'nombre' => 'Farmacia Lauxmann', 'direccion' => 'Bv. Zavalla 1417, Santa Fe', 'telefono' => '459 9610', 'lat' => -31.6247, 'lng' => -60.7113, 'id_ciudad' => $santaFeId ],

            // SANTO TOMÉ
            [ 'nombre' => 'Farmacia Erica Tepp', 'direccion' => 'Av. Richeri 3296, Santo Tomé', 'telefono' => '474 1041', 'lat' => -31.6607, 'lng' => -60.7473, 'id_ciudad' => $santoTomeId ],
            [ 'nombre' => 'Farmacia Stessens', 'direccion' => 'Av. 7 de Marzo 1882, Santo Tomé', 'telefono' => '474 3669', 'lat' => -31.6617, 'lng' => -60.7483, 'id_ciudad' => $santoTomeId ],
            [ 'nombre' => 'Farmacia Daniel Lagger', 'direccion' => 'Av. Fdo. Zuviría 6536, Santo Tomé', 'telefono' => '489 5994', 'lat' => -31.6627, 'lng' => -60.7493, 'id_ciudad' => $santoTomeId ],
            [ 'nombre' => 'Farmacia Villata', 'direccion' => 'Alberdi 2154, Santo Tomé', 'telefono' => '474 6180', 'lat' => -31.6637, 'lng' => -60.7503, 'id_ciudad' => $santoTomeId ],
            [ 'nombre' => 'Farmacia Ghersi', 'direccion' => 'Hernandarias 1793, Santo Tomé', 'telefono' => '475 1075', 'lat' => -31.6647, 'lng' => -60.7513, 'id_ciudad' => $santoTomeId ],
            [ 'nombre' => 'Farmacia Capra', 'direccion' => 'Av. 7 de Marzo 1527, Santo Tomé', 'telefono' => '474 0133', 'lat' => -31.6657, 'lng' => -60.7523, 'id_ciudad' => $santoTomeId ],
        ];

        foreach ($pharmacies as $pharmacy) {
            DB::table('farmacias')->insert($pharmacy);
        }
    }
}