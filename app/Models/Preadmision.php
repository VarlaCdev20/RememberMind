<?php

namespace App\Models;

use App\Traits\GeneraCodigo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Preadmision extends Model
{
    use GeneraCodigo;

    protected $table = 'preadmisiones';
    protected $primaryKey = 'cod_pre';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'PRE';
    protected $digitsCode = 5;

    protected $fillable = [
        'cod_pre',
        'estado',
        'fecha_solicitud',
        'fecha_asignacion',
        'nombres',
        'ap_paterno',
        'ap_materno',
        'ci',
        'expedicion_ci',
        'fecha_nac',
        'genero',
        'estado_civil',
        'telefono',
        'celular',
        'departamento_residencia',
        'ciudad_municipio',
        'zona',
        'calle',
        'direccion_referencia',
        'familiar_nombres',
        'familiar_ap_paterno',
        'familiar_ap_materno',
        'familiar_ci',
        'familiar_parentesco',
        'familiar_celular',
        'familiar_correo',
        'familiar_direccion',
        'motivo_ingreso',
        'procedencia_ingreso',
        'tipo_ingreso',
        'permanencia',
        'prioridad',
        'descripcion_caso',
        'documentos_iniciales_completos',
        'documentos_institucionales_generados',
        'enfermero_asignado',
        'creado_por',
        'observaciones',
        'motivo_rechazo',
        'observacion_rechazo',
        'fecha_rechazo',
        'rechazado_por',
        'fecha_aprobacion',
        'aprobado_por',
        'cod_am_generado',
        'cod_fam_generado',
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        'fecha_asignacion' => 'datetime',
        'fecha_nac' => 'date',
        'fecha_rechazo' => 'datetime',
        'fecha_aprobacion' => 'datetime',
        'documentos_iniciales_completos' => 'boolean',
        'documentos_institucionales_generados' => 'boolean',
    ];

    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoPreadmision::class, 'cod_pre', 'cod_pre');
    }

    public function enfermero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enfermero_asignado', 'cod_usu');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por', 'cod_usu');
    }

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por', 'cod_usu');
    }

    public function rechazador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rechazado_por', 'cod_usu');
    }

    public function adultoGenerado(): BelongsTo
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am_generado', 'cod_am');
    }

    public function familiarGenerado(): BelongsTo
    {
        return $this->belongsTo(Familiar::class, 'cod_fam_generado', 'cod_fam');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->ap_paterno} {$this->ap_materno}");
    }

    public function getFamiliarCompletoAttribute(): string
    {
        return trim("{$this->familiar_nombres} {$this->familiar_ap_paterno} {$this->familiar_ap_materno}");
    }
}
