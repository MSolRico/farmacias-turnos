<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTurnosTable extends Migration
{
    public function up()
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->increments('id_turno');
            $table->string('nombre_turno', 100)->nullable();
            $table->dateTime('fecha_hora_inicio');
            $table->dateTime('fecha_hora_fin');
            $table->unsignedInteger('id_ciudad')->nullable();

            $table->foreign('id_ciudad')
                  ->references('id_ciudad')->on('ciudades')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('turnos');
    }
}

