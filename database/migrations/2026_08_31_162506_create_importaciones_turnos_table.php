<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('importaciones_turnos', function (Blueprint $table) {
            $table->id('id_importacion');

            // Mes al que corresponden los turnos
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('anio');

            // Estado del proceso
            $table->string('estado', 30)->default('pendiente');

            // Información del PDF utilizado
            $table->text('pdf_url')->nullable();

            // Estadísticas de la importación
            $table->unsignedInteger('farmacias_nuevas')->default(0);
            $table->unsignedInteger('farmacias_actualizadas')->default(0);
            $table->unsignedInteger('farmacias_rechazadas')->default(0);

            $table->unsignedInteger('turnos_nuevos')->default(0);
            $table->unsignedInteger('asignaciones_creadas')->default(0);

            $table->unsignedInteger('columnas_con_error')->default(0);

            // Mensaje de error o información adicional
            $table->text('mensaje')->nullable();

            // Último intento de procesamiento
            $table->timestamp('ultimo_intento')->nullable();

            $table->timestamps();

            // Un único registro por mes/año
            $table->unique(['mes', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('importaciones_turnos');
    }
};