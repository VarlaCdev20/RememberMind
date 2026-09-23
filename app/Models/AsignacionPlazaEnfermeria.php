<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionPlazaEnfermeria extends Model
{
    protected $table = 'asignaciones_plazas_enfermeria';

    protected $fillable = [
        'plaza',
        'cod_usu',
        'tipo',
        'fecha',
        'motivo',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }
}
