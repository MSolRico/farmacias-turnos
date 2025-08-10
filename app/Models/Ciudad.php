<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model
{
    protected $table = 'ciudades';
    protected $primaryKey = 'id_ciudad'; // 👈 clave primaria real
    public $timestamps = false;

    protected $fillable = ['nombre_ciudad'];

    public function farmacias()
    {
        return $this->hasMany(Farmacia::class, 'id_ciudad', 'id_ciudad');
    }

    public function turnos()
    {
        return $this->hasMany(Turno::class, 'id_ciudad', 'id_ciudad');
    }
}
