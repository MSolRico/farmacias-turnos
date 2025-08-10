<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFarmaciasTurnosTable extends Migration
{
    public function up()
    {
        Schema::create('farmacias_turnos', function (Blueprint $table) {
            $table->increments('id_farmacia_turno');
            $table->unsignedInteger('id_farmacia');
            $table->unsignedInteger('id_turno');
            $table->text('notas')->nullable();

            $table->foreign('id_farmacia')
                  ->references('id_farmacia')->on('farmacias')
                  ->onDelete('cascade');

            $table->foreign('id_turno')
                  ->references('id_turno')->on('turnos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('farmacias_turnos');
    }
}

