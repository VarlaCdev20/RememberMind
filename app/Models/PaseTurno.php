<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PaseTurno extends Model
{
    use LogsActivity;

    protected $table      = 'pases_turno';
    protected $primaryKey = 'cod_pase';

    public $incrementing = true;
    protected $keyType   = 'int';
    public $timestamps   = true;

    protected $fillable = [
        'cod_am',
        'turno_saliente_id',
        'turno_entrante_id',
        'enfermero_saliente_id',
        'enfermero_entrante_id',
        'fecha',
        'estado_general_cierre',
        'resumen_turno',
        'tareas_realizadas_json',
        'tareas_pendientes_json',
        'alertas_activas_json',
        'recomendacion_siguiente_turno',
        'requiere_vigilancia_especial',
        'motivo_vigilancia',
        'estado',
        'fecha_recibido',
    ];

    protected $casts = [
        'fecha'                       => 'date',
        'fecha_recibido'              => 'datetime',
        'tareas_realizadas_json'      => 'array',
        'tareas_pendientes_json'      => 'array',
        'alertas_activas_json'        => 'array',
        'requiere_vigilancia_especial'=> 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['estado', 'resumen_turno'])
            ->logOnlyDirty()
            ->useLogName('Enfermeria')
            ->setDescriptionForEvent(fn(string $e) => "Pase de turno #{$this->cod_pase} de adulto {$this->cod_am} {$e}.");
    }

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function adultoMayor(): BelongsTo
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function turnoSaliente(): BelongsTo
    {
        return $this->belongsTo(TurnoEnfermeria::class, 'turno_saliente_id', 'cod_turno');
    }

    public function turnoEntrante(): BelongsTo
    {
        return $this->belongsTo(TurnoEnfermeria::class, 'turno_entrante_id', 'cod_turno');
    }

    public function enfermeroSaliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enfermero_saliente_id', 'cod_usu');
    }

    public function enfermeroEntrante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enfermero_entrante_id', 'cod_usu');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeGenerados($query)
    {
        return $query->where('estado', 'GENERADO');
    }

    public function scopePorAdulto($query, string $codAm)
    {
        return $query->where('cod_am', $codAm);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function puedeRecibirse(): bool
    {
        return $this->estado === 'GENERADO';
    }
}
