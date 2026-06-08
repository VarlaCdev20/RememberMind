<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TurnoEnfermeria extends Model
{
    use GeneraCodigo;
    protected $table      = 'turnos_enfermeria';
    protected $primaryKey = 'cod_turno';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'TEN';
    protected $digitsCode = 3;
    public $timestamps   = true;

    protected $fillable = [
        'nombre',
        'hora_inicio',
        'hora_fin',
        'orden',
        'estado',
        'observacion',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    

    public function tareasActuales(): HasMany
    {
        return $this->hasMany(TareaPlanCuidado::class, 'cod_turno', 'cod_turno');
    }

    public function seguimientos(): HasMany
    {
        return $this->hasMany(SeguimientoDiario::class, 'cod_turno', 'cod_turno');
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(AlertaAdulto::class, 'cod_turno', 'cod_turno');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('estado', 'ACTIVO')->orderBy('orden');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function getHorarioAttribute(): string
    {
        return substr($this->hora_inicio, 0, 5) . ' – ' . substr($this->hora_fin, 0, 5);
    }
}
