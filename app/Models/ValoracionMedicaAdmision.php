<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ValoracionMedicaAdmision extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table      = 'valoraciones_medicas_admision';
    protected $primaryKey = 'cod_val_med';

    public $incrementing = true;
    protected $keyType   = 'int';
    public $timestamps   = true;

    protected $fillable = [
        'cod_am',
        'cod_val_enf',
        'fecha',
        'hora',
        'diagnosticos_referidos',
        'antecedentes_relevantes',
        'medicacion_actual_resumen',
        'alergias_referidas',
        'condicion_medica_general',
        'estado_neurologico_basico',
        'nivel_dependencia_sugerido',
        'resultado_admision',
        'motivo_decision',
        'recomendacion_medica',
        'requiere_seguimiento_especial',
        'registrado_por',
        'estado',
    ];

    protected $casts = [
        'fecha'                        => 'date',
        'requiere_seguimiento_especial'=> 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Admision')
            ->setDescriptionForEvent(fn(string $e) => match($e) {
                'created' => "Valoración médica admisión creada para {$this->cod_am}. Resultado: {$this->resultado_admision}.",
                'updated' => "Valoración médica #{$this->cod_val_med} actualizada.",
                'deleted' => "Valoración médica #{$this->cod_val_med} eliminada.",
                default   => "Valoración médica #{$this->cod_val_med} modificada ({$e}).",
            });
    }

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function adultoMayor(): BelongsTo
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function valoracionEnfermeria(): BelongsTo
    {
        return $this->belongsTo(ValoracionEnfermeriaAdmision::class, 'cod_val_enf', 'cod_val_enf');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por', 'cod_usu');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeAdmitidos($query)
    {
        return $query->where('resultado_admision', 'ADMITIDO');
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'COMPLETADA');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function esAdmitido(): bool
    {
        return $this->resultado_admision === 'ADMITIDO';
    }
}
