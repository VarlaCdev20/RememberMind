<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ActividadAdulto extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table      = 'actividades_adulto';
    protected $primaryKey = 'cod_act_adul';

    public $incrementing = true;
    protected $keyType   = 'int';
    public $timestamps   = true;

    protected $fillable = [
        // ── Identificación ──────────────────────────────────────────────────────
        'cod_am',
        'cod_tipo_act',

        // ── Datos descriptivos (nuevos en Fase 1) ───────────────────────────────
        'nombre',
        'descripcion',
        'objetivo',
        'lugar',
        'cupo_maximo',
        'materiales',
        'color',

        // ── Temporalidad ────────────────────────────────────────────────────────
        'fecha',
        'hora',       // hora de inicio (campo original)
        'hora_fin',   // agregado en strengthen_administrative_tables

        // ── Estado y responsable ─────────────────────────────────────────────────
        'estado',
        'responsable_tipo',  // agregado en strengthen_administrative_tables
        'responsable_id',    // agregado en strengthen_administrative_tables

        // ── Cierre y evaluación (nuevos en Fase 1) ──────────────────────────────
        'obs',
        'resultado_general',
        'nivel_cumplimiento',
        'incidencias',
        'recomendaciones',

        // ── Auditoría (nuevos en Fase 1) ─────────────────────────────────────────
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha'                  => 'date',
        'cupo_maximo'            => 'integer',
        'requiere_seguimiento'   => 'boolean',
    ];

    // ── Activity Log ─────────────────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Actividades')
            ->setDescriptionForEvent(function (string $eventName) {
                $nombre = $this->nombre ?? "#{$this->cod_act_adul}";
                return match ($eventName) {
                    'created'  => "Se registró la actividad \"{$nombre}\" para el adulto {$this->cod_am}.",
                    'updated'  => "Se actualizó la actividad \"{$nombre}\" (#{$this->cod_act_adul}).",
                    'deleted'  => "Se anuló la actividad \"{$nombre}\" (#{$this->cod_act_adul}).",
                    'restored' => "Se restauró la actividad \"{$nombre}\" (#{$this->cod_act_adul}).",
                    default    => "Actividad \"{$nombre}\" modificada ({$eventName}).",
                };
            });
    }

    // ── Relaciones ────────────────────────────────────────────────────────────────

    public function tipoActividad(): BelongsTo
    {
        return $this->belongsTo(TipoActividadAdulto::class, 'cod_tipo_act', 'cod_tipo_act');
    }

    public function adultoMayor(): BelongsTo
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function participantes(): HasMany
    {
        return $this->hasMany(ActividadParticipante::class, 'cod_act_adul', 'cod_act_adul');
    }

    public function participantesActivos(): HasMany
    {
        return $this->hasMany(ActividadParticipante::class, 'cod_act_adul', 'cod_act_adul')
            ->whereNull('deleted_at');
    }

    public function voluntariosAsignados(): HasMany
    {
        return $this->hasMany(ActividadVoluntario::class, 'cod_act_adul', 'cod_act_adul');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'cod_usu');
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'cod_usu');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────────

    public function scopeProgramadas(Builder $query): Builder
    {
        return $query->whereIn('estado', ['PROGRAMADA', 'PENDIENTE']);
    }

    public function scopeRealizadas(Builder $query): Builder
    {
        return $query->whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA']);
    }

    public function scopeCanceladas(Builder $query): Builder
    {
        return $query->whereIn('estado', ['CANCELADA', 'ANULADA']);
    }

    public function scopeReprogramadas(Builder $query): Builder
    {
        return $query->where('estado', 'REPROGRAMADA');
    }

    public function scopePorFecha(Builder $query, string $fecha): Builder
    {
        return $query->whereDate('fecha', $fecha);
    }

    public function scopeEntreFechas(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereDate('fecha', '>=', $desde)
                     ->whereDate('fecha', '<=', $hasta);
    }

    public function scopePorTipo(Builder $query, int $codTipoAct): Builder
    {
        return $query->where('cod_tipo_act', $codTipoAct);
    }

    public function scopePorAdulto(Builder $query, string|int $codAm): Builder
    {
        return $query->where('cod_am', $codAm);
    }

    public function scopeHoy(Builder $query): Builder
    {
        return $query->whereDate('fecha', today());
    }

    public function scopeProximas(Builder $query, int $dias = 7): Builder
    {
        return $query->whereDate('fecha', '>=', today())
                     ->whereDate('fecha', '<=', today()->addDays($dias))
                     ->programadas();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────────

    public function duracionMinutos(): ?int
    {
        if (! $this->hora || ! $this->hora_fin) {
            return null;
        }

        $inicio = \Carbon\Carbon::createFromTimeString($this->hora);
        $fin    = \Carbon\Carbon::createFromTimeString($this->hora_fin);

        return (int) $inicio->diffInMinutes($fin);
    }

    public static function normalizarEstado(string $estado): array
    {
        return match (strtoupper(trim($estado))) {
            'COMPLETADA', 'REALIZADA', 'FINALIZADA' => [
                'etiqueta' => 'Realizada',
                'color'    => '#2A9D8F',
                'clase'    => 'border-[#8DA280]/30 bg-[#8DA280]/14 text-[#63775B]',
            ],
            'PROGRAMADA', 'PENDIENTE' => [
                'etiqueta' => 'Programada',
                'color'    => '#D9A05B',
                'clase'    => 'border-[#D9A05B]/30 bg-[#D9A05B]/12 text-[#9A6B2E]',
            ],
            'CANCELADA', 'ANULADA' => [
                'etiqueta' => 'Cancelada',
                'color'    => '#E97A5F',
                'clase'    => 'border-[#E27D60]/25 bg-[#E27D60]/10 text-[#E27D60]',
            ],
            'REPROGRAMADA' => [
                'etiqueta' => 'Reprogramada',
                'color'    => '#7A68B0',
                'clase'    => 'border-[#7A68B0]/30 bg-[#7A68B0]/10 text-[#5A4E8A]',
            ],
            default => [
                'etiqueta' => ucfirst(strtolower($estado)),
                'color'    => '#C7B5A3',
                'clase'    => 'border-[#C7B5A3]/40 bg-[#D5C7B9]/40 text-[#7C7168]',
            ],
        };
    }
}
