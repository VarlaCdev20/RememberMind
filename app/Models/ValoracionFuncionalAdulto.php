<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ValoracionFuncionalAdulto extends Model
{
    use GeneraCodigo;
    use LogsActivity;

    protected $table = 'valoracion_funcional_adulto';
    protected $primaryKey = 'cod_val_func';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'VFA';
    protected $digitsCode = 5;

    public $timestamps = true;

    protected $fillable = [
        'cod_am',
        'fecha_valoracion',
        // Autonomía básica (AVD)
        'come_solo',
        'se_bana_solo',
        'se_viste_solo',
        'va_bano_solo',
        'camina_solo',
        // Dispositivos de asistencia
        'usa_baston',
        'usa_andador',
        'usa_silla_ruedas',
        // Sensoriales y conductuales
        'baja_vision',
        'baja_audicion',
        'dificultad_hablar',
        'molestia_luz',
        'molestia_ruido',
        'se_asusta_facil',
        'necesita_supervision',
        // Clasificación funcional
        'nivel_dependencia',
        'estado',
        'riesgo_caida',
        'indice_barthel',
        // Observaciones
        'observacion',
        // Registro
        'registrado_por',
        // Anulación
        'motivo_anulacion',
        'anulado_por',
        'fecha_anulacion',
    ];

    protected $casts = [
        'fecha_valoracion'    => 'date',
        'come_solo'           => 'boolean',
        'se_bana_solo'        => 'boolean',
        'se_viste_solo'       => 'boolean',
        'va_bano_solo'        => 'boolean',
        'camina_solo'         => 'boolean',
        'usa_baston'          => 'boolean',
        'usa_andador'         => 'boolean',
        'usa_silla_ruedas'    => 'boolean',
        'baja_vision'         => 'boolean',
        'baja_audicion'       => 'boolean',
        'dificultad_hablar'   => 'boolean',
        'molestia_luz'        => 'boolean',
        'molestia_ruido'      => 'boolean',
        'se_asusta_facil'     => 'boolean',
        'necesita_supervision' => 'boolean',
        'indice_barthel'      => 'integer',
        'fecha_anulacion'     => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────────────────────────

    public function scopeVigente($query)
    {
        return $query->where('estado', 'VIGENTE');
    }

    public function scopeHistorica($query)
    {
        return $query->where('estado', 'HISTORICA');
    }

    public function scopeAnulada($query)
    {
        return $query->where('estado', 'ANULADA');
    }

    public function scopePorAdulto($query, string $codAm)
    {
        return $query->where('cod_am', $codAm);
    }

    public function scopeRecientes($query)
    {
        return $query->orderByDesc('fecha_valoracion');
    }

    // ── Relaciones ───────────────────────────────────────────────────────────

    public function adultoMayor()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'cod_usu');
    }

    public function anuladoPor()
    {
        return $this->belongsTo(User::class, 'anulado_por', 'cod_usu');
    }

    // ── Bitácora (Spatie Activitylog) ────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Valoración Funcional')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "Registró valoración funcional del adulto mayor {$this->cod_am} — nivel: {$this->nivel_dependencia}.",
                    'updated' => "Actualizó valoración funcional del adulto mayor {$this->cod_am}.",
                    default   => "Evento '{$eventName}' en valoración funcional del adulto mayor {$this->cod_am}.",
                };
            });
    }
}
