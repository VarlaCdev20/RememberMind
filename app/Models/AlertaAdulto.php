<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AlertaAdulto extends Model
{
    use GeneraCodigo;
    use LogsActivity;

    protected $table      = 'alertas_adulto';
    protected $primaryKey = 'cod_alerta';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'ALA';
    protected $digitsCode = 5;
    public $timestamps   = true;

    protected $fillable = [
        'cod_am',
        'cod_turno',
        'origen',
        'tipo_alerta',
        'nivel',
        'motivo',
        'responsable_id',
        'estado',
        'accion_tomada',
        'fecha_atencion',
        'atendido_por',
        'fecha_cierre',
        'cerrado_por',
        'observacion_cierre',
    ];

    protected $casts = [
        'fecha_atencion' => 'datetime',
        'fecha_cierre'   => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['estado', 'accion_tomada', 'observacion_cierre'])
            ->logOnlyDirty()
            ->useLogName('Enfermeria')
            ->setDescriptionForEvent(fn(string $e) => match($e) {
                'created' => "Alerta [{$this->nivel}] {$this->tipo_alerta} creada para {$this->cod_am}.",
                'updated' => "Alerta #{$this->cod_alerta} actualizada. Estado: {$this->estado}.",
                default   => "Alerta #{$this->cod_alerta} {$e}.",
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

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id', 'cod_usu');
    }

    public function atendidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atendido_por', 'cod_usu');
    }

    public function cerradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrado_por', 'cod_usu');
    }

    public function acciones(): HasMany
    {
        return $this->hasMany(AccionAlerta::class, 'cod_alerta', 'cod_alerta');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeAbiertas($query)
    {
        return $query->whereIn('estado', ['ABIERTA', 'EN_ATENCION']);
    }

    public function scopeCriticas($query)
    {
        return $query->where('nivel', 'CRITICO');
    }

    public function scopePorAdulto($query, string $codAm)
    {
        return $query->where('cod_am', $codAm);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    
    public function getMotivoAttribute($value): string
    {
        return preg_replace('/^\\[[a-z_]+:[A-Za-z0-9_]+\\]\\s*/', '', (string)$value);
    }

    public function puedeCerrarse(): bool
    {
        return in_array($this->estado, ['ABIERTA', 'EN_ATENCION']);
    }

    public static function coloresPorNivel(): array
    {
        return [
            'BAJO'   => 'text-estado-exito border-estado-exitoBorde bg-estado-exitoBg',
            'MEDIO'  => 'text-estado-advertencia border-estado-advertenciaBorde bg-estado-advertenciaBg',
            'ALTO'   => 'text-boton-acento border-borde-focus bg-estado-peligroBg',
            'CRITICO'=> 'text-white border-red-700 bg-red-600',
        ];
    }
}
