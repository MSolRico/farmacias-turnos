<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up()
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_farmacia');
            $table->string('motivo')->default('cerrada');
            $table->timestamps();

            $table->foreign('id_farmacia')
                  ->references('id_farmacia')
                  ->on('farmacias')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reportes');
    }
};
