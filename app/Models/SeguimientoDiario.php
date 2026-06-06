<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SeguimientoDiario extends Model
{
    use LogsActivity;

    protected $table      = 'seguimientos_diarios';
    protected $primaryKey = 'cod_seg_diario';

    public $incrementing = true;
    protected $keyType   = 'int';
    public $timestamps   = true;

    protected $fillable = [
        'cod_am',
        'cod_turno',
        'cod_plan',
        'registrado_por',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado_general',
        'alimentacion',
        'porcentaje_alimentacion',
        'hidratacion',
        'movilidad',
        'intento_caminar_solo',
        'higiene',
        'sueno',
        'orientacion',
        'repite_preguntas',
        'confusion_observable',
        'conducta',
        'participacion',
        'incidente',
        'requiere_medico',
        'observacion',
    ];

    protected $casts = [
        'fecha'                => 'date',
        'intento_caminar_solo' => 'boolean',
        'repite_preguntas'     => 'boolean',
        'confusion_observable' => 'boolean',
        'incidente'            => 'boolean',
        'requiere_medico'      => 'boolean',
        'porcentaje_alimentacion' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['estado_general', 'incidente', 'requiere_medico', 'observacion'])
            ->logOnlyDirty()
            ->useLogName('Enfermeria')
            ->setDescriptionForEvent(fn(string $e) => "Seguimiento diario de {$this->cod_am} ({$this->fecha}) {$e}.");
    }

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function adultoMayor(): BelongsTo
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(TurnoEnfermeria::class, 'cod_turno', 'cod_turno');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanCuidado::class, 'cod_plan', 'cod_plan');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por', 'cod_usu');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeConIncidente($query)
    {
        return $query->where('incidente', true);
    }

    public function scopeRequiereMedico($query)
    {
        return $query->where('requiere_medico', true);
    }

    public function scopePorFecha($query, string $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }
}
