<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCiudadesTable extends Migration
{
    public function up()
    {
        Schema::create('ciudades', function (Blueprint $table) {
            $table->increments('id_ciudad');
            $table->string('nombre_ciudad', 100)->unique();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ciudades');
    }
}
