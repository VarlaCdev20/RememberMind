<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ValoracionEnfermeriaAdmision extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table      = 'valoraciones_enfermeria_admision';
    protected $primaryKey = 'cod_val_enf';

    public $incrementing = true;
    protected $keyType   = 'int';
    public $timestamps   = true;

    protected $fillable = [
        'cod_am',
        'fecha',
        'hora',
        'estado_general',
        'nivel_conciencia',
        'orientacion',
        'comunicacion',
        'dolor_actual',
        'intensidad_dolor',
        'ubicacion_dolor',
        'movilidad',
        'usa_apoyo_movilidad',
        'riesgo_caida',
        'piel_estado',
        'presenta_heridas',
        'ubicacion_heridas',
        'higiene_ingreso',
        'requiere_atencion_inmediata',
        'puede_pasar_valoracion_medica',
        'recomendacion_enfermeria',
        'observacion',
        'registrado_por',
        'estado',
    ];

    protected $casts = [
        'fecha'                        => 'date',
        'dolor_actual'                 => 'boolean',
        'presenta_heridas'             => 'boolean',
        'requiere_atencion_inmediata'  => 'boolean',
        'puede_pasar_valoracion_medica'=> 'boolean',
        'intensidad_dolor'             => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Admision')
            ->setDescriptionForEvent(fn(string $e) => match($e) {
                'created' => "Valoración enfermería admisión creada para {$this->cod_am}.",
                'updated' => "Valoración enfermería #{$this->cod_val_enf} actualizada.",
                'deleted' => "Valoración enfermería #{$this->cod_val_enf} eliminada.",
                default   => "Valoración enfermería #{$this->cod_val_enf} modificada ({$e}).",
            });
    }

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function adultoMayor(): BelongsTo
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por', 'cod_usu');
    }

    public function valoracionesMedicas(): HasMany
    {
        return $this->hasMany(ValoracionMedicaAdmision::class, 'cod_val_enf', 'cod_val_enf');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'COMPLETADA');
    }

    public function scopePuedenPasarMedica($query)
    {
        return $query->where('puede_pasar_valoracion_medica', true);
    }
}
