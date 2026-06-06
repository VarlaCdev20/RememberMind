<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AsignacionTurnoAdulto extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table      = 'asignaciones_turno_adulto';
    protected $primaryKey = 'cod_asig_turno';

    public $incrementing = true;
    protected $keyType   = 'int';
    public $timestamps   = true;

    protected $fillable = [
        'cod_am',
        'cod_turno',
        'cod_usu_enfermero',
        'cod_habitacion',
        'cod_cama',
        'fecha_inicio',
        'fecha_fin',
        'nivel_supervision',
        'estado',
        'motivo_asignacion',
        'asignado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Enfermeria')
            ->setDescriptionForEvent(fn(string $e) => match($e) {
                'created' => "Asignación de turno creada para adulto {$this->cod_am}.",
                'updated' => "Asignación #{$this->cod_asig_turno} actualizada. Estado: {$this->estado}.",
                'deleted' => "Asignación #{$this->cod_asig_turno} eliminada.",
                default   => "Asignación #{$this->cod_asig_turno} modificada ({$e}).",
            });
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

    public function enfermero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu_enfermero', 'cod_usu');
    }

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class, 'cod_habitacion', 'cod_habitacion');
    }

    public function cama(): BelongsTo
    {
        return $this->belongsTo(Cama::class, 'cod_cama', 'cod_cama');
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por', 'cod_usu');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActivas($query)
    {
        return $query->where('estado', 'ACTIVA');
    }

    public function scopePorAdulto($query, string $codAm)
    {
        return $query->where('cod_am', $codAm);
    }

    public function scopePorTurno($query, int $codTurno)
    {
        return $query->where('cod_turno', $codTurno);
    }
}
