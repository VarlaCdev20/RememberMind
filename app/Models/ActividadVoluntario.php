<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ActividadVoluntario extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table      = 'actividad_voluntarios';
    protected $primaryKey = 'id';

    public $incrementing = true;
    protected $keyType   = 'int';
    public $timestamps   = true;

    protected $fillable = [
        'cod_act_adul',
        'cod_vol',
        'rol_apoyo',     // Animador | Asistente | Coordinador | etc.
        'estado',        // ASIGNADO | ASISTIO | FALTO | CANCELADO
        'observaciones',
    ];

    // ── Activity Log ─────────────────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Actividades')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created'  => "Se asignó el voluntario #{$this->cod_vol} a la actividad #{$this->cod_act_adul}.",
                    'updated'  => "Se actualizó la asignación del voluntario #{$this->cod_vol} en actividad #{$this->cod_act_adul}.",
                    'deleted'  => "Se removió al voluntario #{$this->cod_vol} de la actividad #{$this->cod_act_adul}.",
                    'restored' => "Se restauró la asignación del voluntario #{$this->cod_vol} en actividad #{$this->cod_act_adul}.",
                    default    => "Asignación del voluntario #{$this->cod_vol} modificada ({$eventName}).",
                };
            });
    }

    // ── Relaciones ────────────────────────────────────────────────────────────────

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(ActividadAdulto::class, 'cod_act_adul', 'cod_act_adul');
    }

    public function voluntario(): BelongsTo
    {
        return $this->belongsTo(Voluntario::class, 'cod_vol', 'cod_vol');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────────

    public function scopeAsignados(Builder $query): Builder
    {
        return $query->where('estado', 'ASIGNADO');
    }

    public function scopeAsistieron(Builder $query): Builder
    {
        return $query->where('estado', 'ASISTIO');
    }

    public function scopePorActividad(Builder $query, int $codActAdul): Builder
    {
        return $query->where('cod_act_adul', $codActAdul);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────────

    public static function estadosAsignacion(): array
    {
        return [
            'ASIGNADO'  => 'Asignado',
            'ASISTIO'   => 'Asistió',
            'FALTO'     => 'Faltó',
            'CANCELADO' => 'Cancelado',
        ];
    }
}
