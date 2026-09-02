<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportacionTurno extends Model
{
    protected $table = 'importaciones_turnos';

    protected $primaryKey = 'id_importacion';

    protected $fillable = [
        'mes',
        'anio',
        'estado',
        'pdf_url',
        'farmacias_nuevas',
        'farmacias_actualizadas',
        'farmacias_rechazadas',
        'turnos_nuevos',
        'asignaciones_creadas',
        'columnas_con_error',
        'mensaje',
        'ultimo_intento',
    ];

    protected $casts = [
        'mes' => 'integer',
        'anio' => 'integer',
        'farmacias_nuevas' => 'integer',
        'farmacias_actualizadas' => 'integer',
        'farmacias_rechazadas' => 'integer',
        'turnos_nuevos' => 'integer',
        'asignaciones_creadas' => 'integer',
        'columnas_con_error' => 'integer',
        'ultimo_intento' => 'datetime',
    ];
}