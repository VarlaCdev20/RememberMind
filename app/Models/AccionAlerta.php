<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccionAlerta extends Model
{
    use GeneraCodigo;
    protected $table      = 'acciones_alerta';
    protected $primaryKey = 'cod_accion_alerta';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'ACA';
    protected $digitsCode = 5;
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
