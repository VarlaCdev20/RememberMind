<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AdultoMayor extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'adulto_mayor';
    protected $primaryKey = 'cod_am';

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = true;

    protected $fillable = [
        'cod_am',
        'nombres',
        'ap_paterno',
        'ap_materno',
        'ci',
        'complemento_ci',
        'expedicion_ci',
        'fecha_nac',
        'genero',
        'estado_civil',
        'telefono',
        'tiene_celular',
        'celular',
        'sabe_usar_whatsapp',
        'telefono_fijo',
        'departamento_residencia',
        'ciudad_municipio',
        'zona',
        'calle',
        'fecha_ing',
        'hora_ing',
        'tipo_ing',
        'permanencia',
        'nivel_educat',
        'grupo_sanguineo',
        'factor_rh',
        'alergias',
        'seguro_salud',
        'contacto_emergencia_nombre',
        'contacto_emergencia_parentesco',
        'contacto_emergencia_celular',
        'contacto_emergencia_direccion',
        'responsable_principal',
        'autorizado_informacion_medica',
        'consentimiento_datos',
        'observaciones',
        'cod_est_adul',
        'foto',
        'archivado_en',
        'motivo_archivado',
        // ── Fase 6: flujo de admisión ──────────────────────────────────────────
        'motivo_ingreso',
        'procedencia_ingreso',
        // ── Fase 7: asignación de cama ────────────────────────────────────────
        'cod_habitacion',
        'cod_cama',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Adulto Mayor')
            ->setDescriptionForEvent(function (string $eventName) {
                $nombreCompleto = trim("{$this->nombres} {$this->ap_paterno} {$this->ap_materno}");
                
                if ($eventName === 'created') {
                    return "Se registró el adulto mayor {$nombreCompleto} con ficha {$this->cod_am}.";
                }
                
                if ($eventName === 'updated') {
                    if ($this->wasChanged('cod_est_adul')) {
                        // Asumiendo 2 es archivado y 1 es activo
                        if ($this->cod_est_adul == 2) {
                            return "Se archivó la ficha del adulto mayor {$this->cod_am}.";
                        } elseif ($this->cod_est_adul == 1) {
                            return "Se restauró la ficha del adulto mayor {$this->cod_am}.";
                        }
                    }

                    if ($this->wasChanged('foto') && count($this->getChanges()) === 2) { // foto and updated_at
                        return "Se actualizó la fotografía del adulto mayor {$this->cod_am}.";
                    }

                    return "Se actualizó la ficha institucional del adulto mayor {$this->cod_am}.";
                }

                return "Se modificó la ficha del adulto mayor {$this->cod_am}.";
            });
    }

    protected $casts = [
        'fecha_nac' => 'date',
        'fecha_ing' => 'date',
        'archivado_en' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (AdultoMayor $adultoMayor) {
            if (!$adultoMayor->cod_am) {
                $ultimo = self::where('cod_am', 'like', 'AM_%')
                    ->orderByDesc('cod_am')
                    ->value('cod_am');

                $numero = $ultimo
                    ? ((int) substr($ultimo, 3)) + 1
                    : 1;

                $adultoMayor->cod_am = 'AM_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function estado()
    {
        return $this->belongsTo(EstadoAdulto::class, 'cod_est_adul', 'cod_est_adul');
    }

    public function getEstadoTextoAttribute(): string
    {
        return $this->estado?->estado ?? 'SIN ESTADO';
    }

    public function observaciones()
    {
        return $this->hasMany(ObsAdulto::class, 'cod_am', 'cod_am');
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoAdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function actividades()
    {
        return $this->hasMany(ActividadAdulto::class, 'cod_am', 'cod_am');
    }

    public function atenciones()
    {
        return $this->hasMany(AtencionAdulto::class, 'cod_am', 'cod_am');
    }

    public function familiares()
    {
        return $this->belongsToMany(
            Familiar::class,
            'familiar_adulto',
            'cod_am',
            'cod_fam'
        )->withPivot([
            'parentesco_vinculo',
            'es_responsable',
            'estado',
            'observaciones',
            'deleted_at',
        ])->withTimestamps();
    }

    public function voluntarios()
    {
        return $this->belongsToMany(
            Voluntario::class,
            'asignacion_voluntarios',
            'cod_am',
            'cod_vol'
        )->withPivot([
            'fecha_asig',
            'fecha_fin',
            'estado',
            'obser',
        ]);
    }
    public function evaluacionesCognitivas()
    {
        return $this->hasMany(EvaluacionCognitiva::class, 'cod_am', 'cod_am');
    }

    // ── Relaciones FASE 2: Módulos médicos y administrativos ──

    public function fichasMedicas()
    {
        return $this->hasMany(FichaMedicaAdulto::class, 'cod_am', 'cod_am');
    }

    public function medicaciones()
    {
        return $this->hasMany(MedicacionAdulto::class, 'cod_am', 'cod_am');
    }

    public function administracionesMedicacion()
    {
        return $this->hasMany(AdministracionMedicacion::class, 'cod_am', 'cod_am');
    }

    public function signosVitales()
    {
        return $this->hasMany(SignosVitalesAdulto::class, 'cod_am', 'cod_am');
    }

    public function valoracionesFuncionales()
    {
        return $this->hasMany(ValoracionFuncionalAdulto::class, 'cod_am', 'cod_am');
    }

    public function historialEstados()
    {
        return $this->hasMany(HistorialEstadoAdulto::class, 'cod_am', 'cod_am');
    }

    // ── Relaciones del flujo médico clínico (Fase 6) ──────────────────────────

    public function valoracionesEnfermeria()
    {
        return $this->hasMany(ValoracionEnfermeriaAdmision::class, 'cod_am', 'cod_am');
    }

    public function valoracionesMedicas()
    {
        return $this->hasMany(ValoracionMedicaAdmision::class, 'cod_am', 'cod_am');
    }

    public function asignacionesTurno()
    {
        return $this->hasMany(AsignacionTurnoAdulto::class, 'cod_am', 'cod_am');
    }

    public function asignacionTurnoActiva()
    {
        return $this->hasOne(AsignacionTurnoAdulto::class, 'cod_am', 'cod_am')
            ->where('estado', 'ACTIVA');
    }

    public function planesCuidado()
    {
        return $this->hasMany(PlanCuidado::class, 'cod_am', 'cod_am');
    }

    public function planCuidadoActivo()
    {
        return $this->hasOne(PlanCuidado::class, 'cod_am', 'cod_am')
            ->where('estado', 'ACTIVO');
    }

    public function tareasActuales()
    {
        return $this->hasMany(TareaPlanCuidado::class, 'cod_am', 'cod_am')
            ->whereNotIn('estado', ['ANULADA', 'VENCIDA', 'REALIZADA']);
    }

    public function seguimientosDiarios()
    {
        return $this->hasMany(SeguimientoDiario::class, 'cod_am', 'cod_am');
    }

    public function alertas()
    {
        return $this->hasMany(AlertaAdulto::class, 'cod_am', 'cod_am');
    }

    public function alertasAbiertas()
    {
        return $this->alertas()->whereIn('estado', ['ABIERTA', 'EN_ATENCION']);
    }

    public function pasesTurno()
    {
        return $this->hasMany(PaseTurno::class, 'cod_am', 'cod_am');
    }

    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class, 'cod_habitacion', 'cod_habitacion');
    }

    public function cama()
    {
        return $this->belongsTo(Cama::class, 'cod_cama', 'cod_cama');
    }
}