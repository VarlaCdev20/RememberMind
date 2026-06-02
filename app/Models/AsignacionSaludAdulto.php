<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AsignacionSaludAdulto extends Model
{
    use HasFactory, LogsActivity;

    public const ESTADO_ACTIVA = 'activa';
    public const ESTADO_FINALIZADA = 'finalizada';
    public const ESTADO_SUSPENDIDA = 'suspendida';

    public const TIPOS = ['principal', 'apoyo', 'evaluador', 'seguimiento'];
    public const ESTADOS = [self::ESTADO_ACTIVA, self::ESTADO_FINALIZADA, self::ESTADO_SUSPENDIDA];

    protected $table = 'asignaciones_salud_adulto';

    protected $fillable = [
        'cod_am',
        'cod_per_sal',
        'asignado_por',
        'tipo_asignacion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'motivo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Asignaciones clinicas')
            ->setDescriptionForEvent(function (string $eventName) {
                if ($eventName === 'created') {
                    return "Se creo una asignacion clinica {$this->tipo_asignacion} para el adulto mayor {$this->cod_am}.";
                }

                if ($eventName === 'updated') {
                    if ($this->wasChanged('estado')) {
                        return "Se cambio la asignacion clinica #{$this->id} a estado {$this->estado}.";
                    }

                    return "Se actualizo la asignacion clinica #{$this->id}.";
                }

                return "Se modifico la asignacion clinica #{$this->id}.";
            });
    }

    public function adultoMayor()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function personalSalud()
    {
        return $this->belongsTo(PersonalSalud::class, 'cod_per_sal', 'cod_per_sal');
    }

    public function asignador()
    {
        return $this->belongsTo(User::class, 'asignado_por', 'cod_usu');
    }

    public function scopeActiva(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_ACTIVA)
            ->where(function (Builder $subQuery) {
                $subQuery->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', today());
            });
    }

    public function scopePorMedico(Builder $query, int|string|null $codPerSal): Builder
    {
        return $query->when($codPerSal, fn (Builder $q) => $q->where('cod_per_sal', $codPerSal));
    }

    public function scopePorAdultoMayor(Builder $query, string|null $codAm): Builder
    {
        return $query->when($codAm, fn (Builder $q) => $q->where('cod_am', $codAm));
    }

    public function estaActiva(): bool
    {
        return $this->estado === self::ESTADO_ACTIVA
            && ($this->fecha_fin === null || $this->fecha_fin->isToday() || $this->fecha_fin->isFuture());
    }
}
