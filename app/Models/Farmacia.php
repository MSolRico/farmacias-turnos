<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Farmacia extends Model
{
    protected $table = 'farmacias';
    protected $primaryKey = 'id_farmacia';
    public $timestamps = false;
    protected $fillable = ['nombre','direccion','telefono','id_ciudad','lat','lng'];

    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class, 'id_ciudad', 'id_ciudad');
    }

    public function turnos()
    {
        return $this->belongsToMany(Turno::class, 'farmacias_turnos', 'id_farmacia', 'id_turno')
                    ->withPivot('notas','id_farmacia_turno');
    }
}
