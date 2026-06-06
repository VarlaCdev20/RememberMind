<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TareaPlanCuidado extends Model
{
    use LogsActivity;

    protected $table      = 'tareas_plan_cuidado';
    protected $primaryKey = 'cod_tarea';

    public $incrementing = true;
    protected $keyType   = 'int';
    public $timestamps   = true;

    protected $fillable = [
        'cod_plan',
        'cod_am',
        'cod_turno',
        'responsable_id',
        'area',
        'titulo',
        'descripcion',
        'frecuencia',
        'fecha_programada',
        'hora_programada',
        'prioridad',
        'estado',
        'fecha_realizada',
        'resultado',
        'observacion',
        'motivo_omision',
        'transferida_a_turno_id',
        'registrado_por',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_realizada'  => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['estado', 'resultado', 'motivo_omision'])
            ->logOnlyDirty()
            ->useLogName('Enfermeria')
            ->setDescriptionForEvent(fn(string $e) => "Tarea '{$this->titulo}' del plan #{$this->cod_plan}: {$e}. Estado: {$this->estado}.");
    }

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanCuidado::class, 'cod_plan', 'cod_plan');
    }

    public function adultoMayor(): BelongsTo
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(TurnoEnfermeria::class, 'cod_turno', 'cod_turno');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id', 'cod_usu');
    }

    public function turnoTransferencia(): BelongsTo
    {
        return $this->belongsTo(TurnoEnfermeria::class, 'transferida_a_turno_id', 'cod_turno');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por', 'cod_usu');
    }

    public function signosVitalesRelacionados(): HasMany
    {
        return $this->hasMany(SignosVitalesAdulto::class, 'cod_tarea', 'cod_tarea');
    }

    public function administracionesMedicacion(): HasMany
    {
        return $this->hasMany(AdministracionMedicacion::class, 'cod_tarea', 'cod_tarea');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopePendientes($query)
    {
        return $query->where('estado', 'PENDIENTE');
    }

    public function scopePorTurno($query, int $codTurno)
    {
        return $query->where('cod_turno', $codTurno);
    }

    public function scopePorArea($query, string $area)
    {
        return $query->where('area', $area);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function puedeCompletarse(): bool
    {
        return in_array($this->estado, ['PENDIENTE', 'EN_PROCESO']);
    }

    public function requiereMotivoOmision(): bool
    {
        return $this->estado === 'OMITIDA' && empty($this->motivo_omision);
    }
}
