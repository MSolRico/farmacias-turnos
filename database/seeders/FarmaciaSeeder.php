<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Ciudad;

class FarmaciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $santaFeId = DB::table('ciudades')->where('nombre_ciudad', 'Santa Fe')->value('id_ciudad');
        $santoTomeId = DB::table('ciudades')->where('nombre_ciudad', 'Santo Tomé')->value('id_ciudad');


        $farmacias = [
            // --- SANTA FE (id_ciudad = $santaFeId) ---
            // GRUPO 1 (IDs 1-13)
            ['nombre' => 'Banchio', 'direccion' => 'Irigoyen Freyre 2200', 'telefono' => '452 2268', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Belgrano', 'direccion' => 'Rivadavia 3237', 'telefono' => '456 1118', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Bonazzola', 'direccion' => 'Av. Freyre 2298', 'telefono' => '452 7939', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Bruno', 'direccion' => 'Av. Gral. Paz 5550', 'telefono' => '460 2204', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Camilatto', 'direccion' => 'Estanislao Zeballos 2702', 'telefono' => '460 3413', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Camusso', 'direccion' => '4 de Enero 1594', 'telefono' => '459 4678', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Costa', 'direccion' => 'Av. Blas Parera 5671', 'telefono' => '489 5215', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Gheco', 'direccion' => 'Av. Blas Parera 7621', 'telefono' => '155 927199', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Morales', 'direccion' => 'Av. A. del Valle 5855', 'telefono' => '469 6396', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Morello', 'direccion' => 'Av. F. Zuviría 4201', 'telefono' => '453 1215', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Nebiolo', 'direccion' => 'Av. A. del Valle 7781', 'telefono' => '460 9883', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Ortiz de Zárate', 'direccion' => 'Av. J.J. Paso 3299', 'telefono' => '459 9865', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Azanza', 'direccion' => 'San Martín 2983', 'telefono' => '481 1118', 'id_ciudad' => $santaFeId], // ID 13

            // GRUPO 2 (IDs 14-25)
            ['nombre' => 'Amherdt', 'direccion' => 'Saavedra 2498', 'telefono' => '452 4846', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Bourdin', 'direccion' => 'Alberdi 3500', 'telefono' => '452 8066', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Escudero', 'direccion' => 'Av. Galicia 1881', 'telefono' => '460 7386', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Ghersi', 'direccion' => 'Pte Roca 2993', 'telefono' => '580 8542', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Jullier', 'direccion' => 'Av. Freyre 3313', 'telefono' => '455 2838', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Lauxmann', 'direccion' => 'Bv. Zavalla 1417', 'telefono' => '459 9610', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Theiller', 'direccion' => 'Av. Gral. Paz 5773', 'telefono' => '460 4334', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Trucco', 'direccion' => 'Las Heras 4501', 'telefono' => '452 8523', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Throendly', 'direccion' => 'Av. Blas Parera 8235', 'telefono' => '489 5440', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Timofiejuk', 'direccion' => 'Rivadavia 2443', 'telefono' => '155 922104', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Wuilloud', 'direccion' => 'San Jerónimo 1801', 'telefono' => '459 7895', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Bordignon', 'direccion' => 'Suipacha 2310', 'telefono' => '452 7618', 'id_ciudad' => $santaFeId], // ID 25

            // GRUPO 3 (IDs 26-37)
            ['nombre' => 'Armando', 'direccion' => '25 de Mayo 3441', 'telefono' => '452 0603', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Arrimada', 'direccion' => 'Marcial Candioti 3298', 'telefono' => '456 1397', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Daniel Lagger', 'direccion' => 'Av. Fdo. Zuviría 6536', 'telefono' => '489 5994', 'id_ciudad' => $santaFeId],
            ['nombre' => 'El Inca', 'direccion' => 'San Martín 2255', 'telefono' => '452 1747', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Burgués Romano', 'direccion' => 'Av. Blas Parera 7001', 'telefono' => '489 1807', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Lucía Banchio', 'direccion' => 'Av. Gral. Paz 7165', 'telefono' => '460 3500', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Mazzali', 'direccion' => 'Juan de Garay 2915', 'telefono' => '458 1286', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Pellegrini', 'direccion' => 'Bv. Pellegrini 3465', 'telefono' => '452 1233', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Plank', 'direccion' => 'Av. Gral. Paz 4880', 'telefono' => '412 1360', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Sobrero', 'direccion' => 'Mendoza 3306', 'telefono' => '154 056175', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Strada', 'direccion' => 'Bv. Zavalla 1067', 'telefono' => '459 9798', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Asinari', 'direccion' => 'Crespo 2808', 'telefono' => '453 0042', 'id_ciudad' => $santaFeId], // ID 37

            // GRUPO 4 (IDs 38-50)
            ['nombre' => 'Abregu', 'direccion' => 'Av. Freyre 3029', 'telefono' => '453 1300', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Ignacio Azanza', 'direccion' => 'Av. L. y Planes 3901', 'telefono' => '452 6526', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Gottero', 'direccion' => 'San Martín 2699', 'telefono' => '453 0644', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Liliana Martinez', 'direccion' => 'Belgrano 6254', 'telefono' => '460 6780', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Luero', 'direccion' => '9 de Julio 1898', 'telefono' => '459 4785', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Marcelo Galizzi', 'direccion' => 'Av. Blas Parera 8953', 'telefono' => '484 0578', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Mario Martinez', 'direccion' => 'Av. A. del Valle 4499', 'telefono' => '453 1597', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Pardo', 'direccion' => '9 de Julio 3443', 'telefono' => '456 6267', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Sabio', 'direccion' => 'Angel Cassanello 898', 'telefono' => '460 8172', 'id_ciudad' => $santaFeId],
            ['nombre' => 'San Lorenzo', 'direccion' => 'Av. Freyre 1598', 'telefono' => '459 3487', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Santiváñez', 'direccion' => 'Estanislao Zeballos 4453', 'telefono' => '489 2739', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Tonini', 'direccion' => 'Avellaneda 3498', 'telefono' => '453 8391', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Zimmerman', 'direccion' => 'Padre Genesio 2971', 'telefono' => '469 1616', 'id_ciudad' => $santaFeId],

            // GRUPO 5 (IDs 51-63)
            ['nombre' => 'Acosta', 'direccion' => 'Suipacha 2506', 'telefono' => '455 6677', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Alejandro Senn', 'direccion' => '4 de Enero 2599', 'telefono' => '452 9302', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Bonazzola Denise', 'direccion' => 'Gral. López 2740', 'telefono' => '156 139373', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Chelini', 'direccion' => 'Av. Facundo Zuviría 4679', 'telefono' => '453 0348', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Giulioni', 'direccion' => 'Gorostiaga 3038', 'telefono' => '488 9970', 'id_ciudad' => $santaFeId],
            ['nombre' => 'López', 'direccion' => 'Av. López y Planes 4267', 'telefono' => '453 4829', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Mai', 'direccion' => 'Av. A. del Valle 7431', 'telefono' => '460 2224', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Martínez Juan José', 'direccion' => 'Urquiza 1859', 'telefono' => '452 9949', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Méndez', 'direccion' => 'Güemes 4356', 'telefono' => '453 1945', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Naito', 'direccion' => 'Mendoza 4098', 'telefono' => '456 3842', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Pasteur', 'direccion' => 'Marcial Candioti 3499', 'telefono' => '452 0608', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Rojas', 'direccion' => 'Blas Parera 7202', 'telefono' => '489 2747', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Valetti', 'direccion' => 'Av. Gral. Paz 6440', 'telefono' => '469 1813', 'id_ciudad' => $santaFeId],

            // GRUPO 6 (IDs 64-76)
            ['nombre' => 'Capra', 'direccion' => 'Entre Ríos 3115', 'telefono' => '459 7877', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Costa Samita', 'direccion' => 'Av. A. del Valle 6378', 'telefono' => '460 2586', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Caporizzo', 'direccion' => 'Av. A. del Valle 9284', 'telefono' => '412 2564', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Donadio', 'direccion' => 'Javier de la Rosa 322', 'telefono' => '460 6020', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Junges', 'direccion' => 'Güemes 3701', 'telefono' => '456 1203', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Long', 'direccion' => 'Av. A. del Valle 4026', 'telefono' => '455 2457', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Mergen', 'direccion' => 'Av. Peñaloza 7308', 'telefono' => '155 849314', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Ortega', 'direccion' => 'Blas Parera 8448', 'telefono' => '484 2593', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Pedro A. Kornijuk', 'direccion' => 'Av. F. Zuviría 5323', 'telefono' => '488 2156', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Sartor', 'direccion' => 'Rivadavia 3300', 'telefono' => '455 5412', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Valverde', 'direccion' => '1º de Mayo 2215', 'telefono' => '453 5891', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Verónica Cano', 'direccion' => 'Suipacha 2912', 'telefono' => '455 8880', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Vignolo', 'direccion' => 'Av. Gral. Paz 4698', 'telefono' => '453 8276', 'id_ciudad' => $santaFeId],

            // GRUPO 7 (IDs 77-87)
            ['nombre' => 'Bolognesi', 'direccion' => 'Av. Fdo. Zuviría 5079', 'telefono' => '488 7805', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Cardoso', 'direccion' => 'Av. Freyre 2638', 'telefono' => '453 7137', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Curti', 'direccion' => 'Entre Ríos 3288', 'telefono' => '459 8218', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Esterkin', 'direccion' => 'San Jerónimo 1995', 'telefono' => '459 7766', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Figueroa Sobrero', 'direccion' => 'Av. Gral. Paz 7471', 'telefono' => '460 1179', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Gómez', 'direccion' => 'Av. Vera Peñaloza 6839', 'telefono' => '484 3684', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Imhof', 'direccion' => 'Marcial Candioti 2796', 'telefono' => '456 6137', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Lagger Zurbriggen', 'direccion' => 'Las Heras 5401', 'telefono' => '460 3331', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Lencinas', 'direccion' => 'Balcarce 2384', 'telefono' => '481 1613', 'id_ciudad' => $santaFeId],
            ['nombre' => 'María Selva', 'direccion' => 'Av. A. del Valle 6600', 'telefono' => '484 7455', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Santiago', 'direccion' => 'Necochea 4162', 'telefono' => '481 1560', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Vinderola', 'direccion' => 'Bv. Pellegrini 3060', 'telefono' => '453 1674', 'id_ciudad' => $santaFeId],

            // GRUPO 8 (IDs 88-99)
            ['nombre' => 'Barrientos', 'direccion' => '9 de Julio 2198', 'telefono' => '459 9601', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Caraballo', 'direccion' => 'Diagonal Goyena 3460', 'telefono' => '155 555286', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Clavé', 'direccion' => 'Av. G. Paz 4548', 'telefono' => '154 622615', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Fanessi', 'direccion' => '1º de Mayo 2899', 'telefono' => '453 9000', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Ferro', 'direccion' => 'Francia 2998', 'telefono' => '581 7632', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Judith Acevedo', 'direccion' => 'Castelli 3642', 'telefono' => '155 329661', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Montes', 'direccion' => 'Av. Gral. Paz 6765', 'telefono' => '460 5026', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Santa Fe', 'direccion' => 'Saavedra 1822', 'telefono' => '459 2301', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Sen - Coltrinari', 'direccion' => 'Salv. del Carril 1672', 'telefono' => '460 5463', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Suppo', 'direccion' => 'Av. A. del Valle 6897', 'telefono' => '460 4141', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Ugolini', 'direccion' => 'Av. Gorriti 4512', 'telefono' => '480 2555', 'id_ciudad' => $santaFeId],

            // GRUPO 9 (IDs 100-111)
            ['nombre' => 'Bertolin', 'direccion' => 'Urquiza 1067', 'telefono' => '458 0047', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Chemes', 'direccion' => 'Av. Urquiza 2179', 'telefono' => '459 7575', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Coll', 'direccion' => 'Av. A. del Valle 6029', 'telefono' => '581 8682', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Damiani', 'direccion' => 'Urquiza 3180', 'telefono' => '456 5287', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Domet Hurani', 'direccion' => 'Av. Gorriti 3751-Loc.2', 'telefono' => '155 139889', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Gabriel Jauregui', 'direccion' => 'Echagüe 7254', 'telefono' => '460 4556', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Irrazabal', 'direccion' => 'Av. López y Planes 4499', 'telefono' => '456 3029', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Nicolau Manzur', 'direccion' => '1º de Mayo 2699', 'telefono' => '452 3002', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Pacce', 'direccion' => 'Av. A. del Valle 4900', 'telefono' => '455 8517', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Peiro', 'direccion' => 'Av. Fdo. Zuviría 6253', 'telefono' => '154 725272', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Stricker', 'direccion' => 'Chacabuco 2116', 'telefono' => '453 9096', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Zapata Morán', 'direccion' => 'Blas Parera 6751', 'telefono' => '489 1979', 'id_ciudad' => $santaFeId],
            ['nombre' => 'García', 'direccion' => 'Rivadavia 3193', 'telefono' => '455 0724', 'id_ciudad' => $santaFeId], // ID 111

            // GRUPO 10 (IDs 112-122)
            ['nombre' => 'Coniglio', 'direccion' => 'Av. Facundo Zuviría 5912', 'telefono' => '489 3327', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Dentesani', 'direccion' => 'Ituzaingó 1357 P.B.', 'telefono' => '154 492052', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Finelli', 'direccion' => 'Las Heras 7304', 'telefono' => '460 0104', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Fucksmann', 'direccion' => 'Saavedra 3081/85', 'telefono' => '452 3165', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Leiva', 'direccion' => 'La Rioja 2511', 'telefono' => '453 7968', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Mercado Central', 'direccion' => 'San Gerónimo 2179', 'telefono' => '459 7200', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Morante', 'direccion' => 'Barrio El Pozo M.8 Vda. 43', 'telefono' => '451 1485', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Queglas', 'direccion' => '12 de Infantería 4400', 'telefono' => '489 7439', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Salvatierra', 'direccion' => 'Av. Gral. López 3272', 'telefono' => '459 8352', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Sileoni', 'direccion' => 'Angel Casanello 519', 'telefono' => '460 4940', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Bosch', 'direccion' => 'Stgo. del Estero 2764', 'telefono' => '456 9919', 'id_ciudad' => $santaFeId], // ID 122

            // GRUPO 11 (IDs 123-136)
            ['nombre' => 'Argenti', 'direccion' => 'San Jerónimo 1746', 'telefono' => '459 2943', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Berron', 'direccion' => 'Diagonal Goyena 3221', 'telefono' => '489 4530', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Castro Karina', 'direccion' => 'Dra. Grienson 8231', 'telefono' => '155 691900', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Facino', 'direccion' => 'Diagonal España 3176', 'telefono' => '580 5041', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Labath', 'direccion' => 'Stgo. del Estero 3142 - Loc. 5', 'telefono' => '455 5146', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Martínez', 'direccion' => 'Av. Galicia 1519', 'telefono' => '460 6375', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Mónica Wagner', 'direccion' => 'Marcial Candioti 3899', 'telefono' => '452 2242', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Rita Martínez', 'direccion' => 'Av. A. del Valle 10479', 'telefono' => '469 4874', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Rojas Sotelo', 'direccion' => 'Alvear 6303', 'telefono' => '460 7603', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Pescetti Maximiliano', 'direccion' => 'Mendoza 2676', 'telefono' => '452 2928', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Scalzo', 'direccion' => 'Gobernador Vera 3206', 'telefono' => '455 3083', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Vilarrubi', 'direccion' => 'Av. Peñaloza 7512', 'telefono' => '488 3608', 'id_ciudad' => $santaFeId],

            // GRUPO 12 (IDs 135-147)
            ['nombre' => 'Bonazzola Estefania', 'direccion' => 'Av. del Valle 5118', 'telefono' => '452 6519', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Brambilla', 'direccion' => 'E. Zeballos 3731', 'telefono' => '489 4243', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Burgi', 'direccion' => 'Bv. Pellegrini 3187', 'telefono' => '581 5787', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Colucci', 'direccion' => 'Av. Freyre 1908', 'telefono' => '459 9418', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Germán López', 'direccion' => 'Av. Gral Paz 5210', 'telefono' => '452 8048', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Giménez', 'direccion' => 'Urquiza 2332', 'telefono' => '452 6256', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Imvinkelried', 'direccion' => 'Lavalle 4201', 'telefono' => '456 6576', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Mansilla', 'direccion' => '9 de Julio 1181', 'telefono' => '459 8798', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Menapace', 'direccion' => 'Blas Parera 7831', 'telefono' => '489 0660', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Pescetti', 'direccion' => 'P. Genesio 1901', 'telefono' => '460 4447', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Ranzuglia', 'direccion' => 'Fray C. Rodriguez 3889', 'telefono' => '488 4801', 'id_ciudad' => $santaFeId],
            ['nombre' => 'Wagner Burgués', 'direccion' => 'Rivadavia 3098', 'telefono' => '456 1500', 'id_ciudad' => $santaFeId], // ID 146
            ['nombre' => 'Zentner', 'direccion' => 'San Gerónimo 3101', 'telefono' => '453 2805', 'id_ciudad' => $santaFeId], // ID 147

            // --- SANTO TOMÉ (id_ciudad = $santoTomeId) ---
            // ST: PRIMER TURNO (IDs 148-150)
            ['nombre' => 'Erica Tepp', 'direccion' => 'Av. Richeri 3296', 'telefono' => '474 1041', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Stessens', 'direccion' => 'Av. 7 de Marzo 1882', 'telefono' => '474 3669', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Villata', 'direccion' => 'Av. Luján 2979', 'telefono' => '474 3371', 'id_ciudad' => $santoTomeId],

            // ST: SEGUNDO TURNO (IDs 151-153)
            ['nombre' => 'Sauco', 'direccion' => 'Alberdi 2154', 'telefono' => '474 6180', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Olivero', 'direccion' => 'Hernandarias 1793', 'telefono' => '475 1075', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Escobar', 'direccion' => 'Av. 7 de Marzo 1527', 'telefono' => '474 0133', 'id_ciudad' => $santoTomeId],

            // ST: TERCER TURNO (IDs 154-156)
            ['nombre' => 'Cirelli', 'direccion' => '25 de Mayo 1654', 'telefono' => '474 0427', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Gómez', 'direccion' => 'La Rioja 2295', 'telefono' => '475 3219', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Zimmermann', 'direccion' => 'Mariano Candioti 3226', 'telefono' => '474 7279', 'id_ciudad' => $santoTomeId],

            // ST: CUARTO TURNO (IDs 157-159)
            ['nombre' => 'Marta Tepp', 'direccion' => 'Av. Luján 3636', 'telefono' => '474 4804', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Bonino', 'direccion' => 'Obispo Gelabert 2198', 'telefono' => '474 4484', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Martínez', 'direccion' => 'Hipólito Yrigoyen 2235', 'telefono' => '474 3995', 'id_ciudad' => $santoTomeId],

            // ST: QUINTO TURNO (IDs 160-163)
            ['nombre' => 'Pescetti Julieta', 'direccion' => 'Av. 7 de Marzo 1760', 'telefono' => '474 0375', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Berta', 'direccion' => 'Ejército Argentino 2695', 'telefono' => '474 9893', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Cruz', 'direccion' => 'M. Candioti 2493', 'telefono' => '474 3501', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Curado', 'direccion' => 'Paseo Las Acacias - Local 15', 'telefono' => '155317070', 'id_ciudad' => $santoTomeId],

            // ST: SEXTO TURNO (IDs 164-166)
            ['nombre' => 'Mayoráz', 'direccion' => '25 de Mayo 2017', 'telefono' => '474 7057', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Macagno', 'direccion' => 'Av. 7 de Marzo 2601', 'telefono' => '474 2196', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Tosello', 'direccion' => 'López y Planes 2865', 'telefono' => '154 470671', 'id_ciudad' => $santoTomeId],

            // ST: SÉPTIMO TURNO (IDs 167-169)
            ['nombre' => 'Contini', 'direccion' => 'Sarmiento 1776', 'telefono' => '474 0422', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Marcolini', 'direccion' => 'Av. Luján 2659', 'telefono' => '474 1354', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'San Roque', 'direccion' => 'Av. 7 de Marzo 2118', 'telefono' => '474 0466', 'id_ciudad' => $santoTomeId],

            // ST: OCTAVO TURNO (IDs 170-173)
            ['nombre' => 'Quassolo', 'direccion' => 'Sarmiento 2343', 'telefono' => '474 7428', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Mariana Gómez', 'direccion' => 'Avenida Luján 2468', 'telefono' => '474 2737', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Rivero', 'direccion' => 'Mariano Candioti 2801', 'telefono' => '154 623606', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Terenzi', 'direccion' => 'Belgrano 3018', 'telefono' => '474 4371', 'id_ciudad' => $santoTomeId],

            // ST: NOVENO TURNO (IDs 174-176)
            ['nombre' => 'Adrián Carrizo', 'direccion' => 'Belgrano 3405', 'telefono' => '474 1865', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Firmani', 'direccion' => '25 de Mayo 1878', 'telefono' => '474 4847', 'id_ciudad' => $santoTomeId],
            ['nombre' => 'Palacin', 'direccion' => 'Mariano Candioti 3677', 'telefono' => '474 1674', 'id_ciudad' => $santoTomeId],
        ];

        DB::table('farmacias')->insert($farmacias);
    }
}
