<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    use HasFactory;

    protected $table = 'reportes';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id_farmacia',
        'id_usuario',
        'motivo',
    ];

    // Relación con Farmacia (opcional pero recomendable)
    public function farmacia()
    {
        return $this->belongsTo(Farmacia::class, 'id_farmacia', 'id_farmacia');
    }

    // Relación con Usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}
