<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFarmaciasTable extends Migration
{
    public function up()
    {
        Schema::create('farmacias', function (Blueprint $table) {
            $table->increments('id_farmacia');
            $table->string('nombre', 255);
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 100)->nullable();
            $table->unsignedInteger('id_ciudad')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();

            $table->foreign('id_ciudad')
                  ->references('id_ciudad')->on('ciudades')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('farmacias');
    }
}

