<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    protected $table = 'turnos';
    protected $primaryKey = 'id_turno';
    public $timestamps = false;
    protected $fillable = ['nombre_turno','fecha_hora_inicio','fecha_hora_fin','id_ciudad'];

    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class, 'id_ciudad', 'id_ciudad');
    }

    public function farmacias()
    {
        return $this->belongsToMany(Farmacia::class, 'farmacias_turnos', 'id_turno', 'id_farmacia')
                    ->withPivot('notas','id_farmacia_turno');
    }
}
