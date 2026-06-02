<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ActividadParticipante extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table     = 'actividad_participantes';
    protected $primaryKey = 'id';

    public $incrementing = true;
    protected $keyType   = 'int';
    public $timestamps   = true;

    protected $fillable = [
        'cod_act_adul',
        'cod_am',
        'estado_asistencia',    // INSCRITO | ASISTIO | FALTO | JUSTIFICADO
        'nivel_participacion',  // ALTA | MEDIA | BAJA | NO_APLICA
        'estado_observado',     // ACTIVO | TRANQUILO | AISLADO | IRRITABLE | CANSADO | COLABORADOR | DESORIENTADO
        'observacion_individual',
        'requiere_seguimiento',
        'registrado_por',
    ];

    protected $casts = [
        'requiere_seguimiento' => 'boolean',
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
                    'created'  => "Se inscribió al adulto {$this->cod_am} en la actividad #{$this->cod_act_adul}.",
                    'updated'  => "Se actualizó la participación del adulto {$this->cod_am} en actividad #{$this->cod_act_adul}.",
                    'deleted'  => "Se eliminó la participación del adulto {$this->cod_am} en actividad #{$this->cod_act_adul}.",
                    'restored' => "Se restauró la participación del adulto {$this->cod_am} en actividad #{$this->cod_act_adul}.",
                    default    => "Participación del adulto {$this->cod_am} modificada ({$eventName}).",
                };
            });
    }

    // ── Relaciones ────────────────────────────────────────────────────────────────

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(ActividadAdulto::class, 'cod_act_adul', 'cod_act_adul');
    }

    public function adultoMayor(): BelongsTo
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por', 'cod_usu');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────────

    public function scopeAsistieron(Builder $query): Builder
    {
        return $query->where('estado_asistencia', 'ASISTIO');
    }

    public function scopeFaltaron(Builder $query): Builder
    {
        return $query->whereIn('estado_asistencia', ['FALTO', 'JUSTIFICADO']);
    }

    public function scopeRequierenSeguimiento(Builder $query): Builder
    {
        return $query->where('requiere_seguimiento', true);
    }

    public function scopePorActividad(Builder $query, int $codActAdul): Builder
    {
        return $query->where('cod_act_adul', $codActAdul);
    }

    public function scopePorAdulto(Builder $query, string|int $codAm): Builder
    {
        return $query->where('cod_am', $codAm);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────────

    public static function estadosAsistencia(): array
    {
        return [
            'INSCRITO'    => 'Inscrito',
            'ASISTIO'     => 'Asistió',
            'FALTO'       => 'Faltó',
            'JUSTIFICADO' => 'Justificado',
        ];
    }

    public static function nivelesParticipacion(): array
    {
        return [
            'ALTA'     => 'Alta',
            'MEDIA'    => 'Media',
            'BAJA'     => 'Baja',
            'NO_APLICA' => 'No aplica',
        ];
    }

    public static function estadosObservados(): array
    {
        return [
            'ACTIVO'        => 'Activo',
            'TRANQUILO'     => 'Tranquilo',
            'COLABORADOR'   => 'Colaborador',
            'AISLADO'       => 'Aislado',
            'IRRITABLE'     => 'Irritable',
            'CANSADO'       => 'Cansado',
            'DESORIENTADO'  => 'Desorientado',
        ];
    }
}
