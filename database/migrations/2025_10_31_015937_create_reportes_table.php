<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_farmacia', function (Blueprint $table) {
            $table->id('id_reporte');
            $table->unsignedInteger('id_farmacia');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedInteger('id_turno')->nullable();
            $table->text('comentario')->nullable();
            $table->enum('estado', ['pendiente', 'verificado', 'rechazado'])->default('pendiente');
            $table->date('fecha_reporte');
            $table->timestamps();

            $table->foreign('id_farmacia')
                  ->references('id_farmacia')
                  ->on('farmacias')
                  ->onDelete('cascade');
            
            $table->foreign('id_usuario')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            $table->foreign('id_turno')
                  ->references('id_turno')
                  ->on('turnos')
                  ->onDelete('set null');

            // Índices
            $table->index('fecha_reporte');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_farmacia');
    }
};