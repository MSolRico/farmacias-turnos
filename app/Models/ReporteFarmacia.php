<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteFarmacia extends Model
{
    protected $table = 'reportes_farmacia';
    protected $primaryKey = 'id_reporte';

    protected $fillable = [
        'id_farmacia',
        'id_usuario',
        'id_turno',
        'comentario',
        'estado',
        'fecha_reporte',
    ];

    protected $casts = [
        'fecha_reporte' => 'datetime',
    ];

    /**
     * Relación con Farmacia
     */
    public function farmacia(): BelongsTo
    {
        return $this->belongsTo(Farmacia::class, 'id_farmacia', 'id_farmacia');
    }

    /**
     * Relación con Usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    /**
     * Relación con Turno
     */
    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'id_turno', 'id_turno');
    }

    /**
     * Scope: Reportes de hoy
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_reporte', today());
    }

    /**
     * Scope: Reportes pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    /**
     * Scope: Reportes verificados
     */
    public function scopeVerificados($query)
    {
        return $query->where('estado', 'verificado');
    }
}