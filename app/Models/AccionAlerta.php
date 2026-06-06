<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccionAlerta extends Model
{
    protected $table      = 'acciones_alerta';
    protected $primaryKey = 'cod_accion_alerta';

    public $incrementing = true;
    protected $keyType   = 'int';
    public $timestamps   = true;

    protected $fillable = [
        'cod_alerta',
        'accion',
        'responsable_id',
        'fecha_accion',
        'estado',
        'observacion',
    ];

    protected $casts = [
        'fecha_accion' => 'datetime',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function alerta(): BelongsTo
    {
        return $this->belongsTo(AlertaAdulto::class, 'cod_alerta', 'cod_alerta');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id', 'cod_usu');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopePendientes($query)
    {
        return $query->where('estado', 'PENDIENTE');
    }

    public function scopeRealizadas($query)
    {
        return $query->where('estado', 'REALIZADA');
    }
}
