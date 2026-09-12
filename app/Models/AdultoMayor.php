<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AdultoMayor extends Model
{
    use GeneraCodigo;
    use HasFactory, LogsActivity;

    protected $table = 'adulto_mayor';
    protected $primaryKey = 'cod_am';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'AM';
    protected $digitsCode = 3;

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
        'estado_operativo',
        'motivo_estado_operativo',
        'estado_operativo_desde',
        'foto',
        'archivado_en',
        'motivo_archivado',
        // ── Fase 6: flujo de admisión ──────────────────────────────────────────
        'motivo_ingreso',
        'procedencia_ingreso',
        'cod_pre_origen',
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
        'estado_operativo_desde' => 'datetime',
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

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombres . ' ' . ($this->ap_paterno ?? '') . ' ' . ($this->ap_materno ?? ''));
    }

    public function getEdadAttribute(): ?int
    {
        return $this->fecha_nac ? (int) \Carbon\Carbon::parse($this->fecha_nac)->age : null;
    }

    public function getEdadTextoAttribute(): string
    {
        $edad = $this->edad;
        return $edad !== null ? "{$edad} años" : 'Edad no registrada';
    }

    public function getEstadoTextoAttribute(): string
    {
        return $this->estado?->estado ?? 'SIN ESTADO';
    }

    public function getEstadoHumanoAttribute(): string
    {
        $codigo = $this->estado?->estado ?? $this->estado_operativo ?? '';
        if (empty($codigo)) {
            return 'No registrado';
        }

        return match (strtoupper($codigo)) {
            'EN_SEGUIMIENTO_ACTIVO' => 'En seguimiento activo',
            'EN_CENTRO' => 'En el centro',
            'SALIDA_TEMPORAL' => 'Salida temporal',
            'HOSPITALIZADO' => 'Hospitalizado',
            'EGRESADO' => 'Egresado',
            'FALLECIDO' => 'Fallecido',
            'ACTIVO', 'ACTIVA' => 'Activo',
            'INACTIVO', 'INACTIVA' => 'Inactivo',
            'ARCHIVADO' => 'Archivado',
            'SEGUIMIENTO_ESPECIAL' => 'Seguimiento especial',
            'RETIRADO' => 'Retirado',
            'TRASLADADO' => 'Trasladado',
            'RESTAURADO' => 'Restaurado',
            'PREADMISION' => 'Preadmisión',
            'PENDIENTE_VALORACION_ENFERMERIA' => 'Pend. valoración enfermería',
            'VALORACION_ENFERMERIA_COMPLETADA' => 'Valoración enfermería completada',
            'PENDIENTE_VALORACION_MEDICA' => 'Pend. valoración médica',
            'VALORACION_MEDICA_COMPLETADA' => 'Valoración médica completada',
            'ADMITIDO' => 'Admitido',
            'NO_ADMITIDO' => 'No admitido',
            'DERIVADO' => 'Derivado',
            'OBSERVADO' => 'Observado',
            'ASIGNADO' => 'Asignado',
            default => ucwords(strtolower(str_replace('_', ' ', $codigo))),
        };
    }

    public function getEstadoBadgeColorAttribute(): string
    {
        $codigo = $this->estado?->estado ?? $this->estado_operativo ?? '';
        return match (strtoupper($codigo)) {
            'ACTIVO', 'ACTIVA', 'EN_CENTRO', 'EN_SEGUIMIENTO_ACTIVO', 'ADMITIDO' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50',
            'HOSPITALIZADO', 'SEGUIMIENTO_ESPECIAL', 'OBSERVADO' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/50',
            'SALIDA_TEMPORAL', 'DERIVADO', 'TRASLADADO' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/50',
            'FALLECIDO', 'INACTIVO', 'INACTIVA', 'ARCHIVADO', 'EGRESADO', 'RETIRADO' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
            default => 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
        };
    }

    public function getHabitacionTextoAttribute(): string
    {
        if ($this->habitacion) {
            return $this->habitacion->codigo ?? ($this->habitacion->nombre ?? 'Habitación no asignada');
        }
        if ($this->cod_habitacion) {
            $direct = Habitacion::find($this->cod_habitacion);
            if ($direct) {
                return $direct->codigo ?? ($direct->nombre ?? 'Habitación no asignada');
            }
        }
        return 'Habitación no asignada';
    }

    public function getCamaTextoAttribute(): string
    {
        if ($this->cama) {
            $cod = $this->cama->codigo ?? ($this->cama->nombre ?? '');
            return $cod ? "Cama {$cod}" : 'Cama no asignada';
        }
        if ($this->cod_cama) {
            $direct = Cama::find($this->cod_cama);
            if ($direct) {
                $cod = $direct->codigo ?? ($direct->nombre ?? '');
                return $cod ? "Cama {$cod}" : 'Cama no asignada';
            }
        }
        return 'Cama no asignada';
    }

    public function getUbicacionTextoAttribute(): string
    {
        $hab = $this->habitacion_texto;
        $cama = $this->cama_texto;

        $hasHab = $hab !== 'Habitación no asignada';
        $hasCama = $cama !== 'Cama no asignada';

        if ($hasHab && $hasCama) {
            return "{$hab} • {$cama}";
        }
        if ($hasHab) {
            return "{$hab} • Cama no asignada";
        }
        if ($hasCama) {
            return "Habitación no asignada • {$cama}";
        }
        return 'Sin ubicación asignada';
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
    // ── Relaciones FASE 2: Módulos médicos y administrativos ──

    public function fichasMedicas()
    {
        return $this->hasMany(FichaMedicaAdulto::class, 'cod_am', 'cod_am');
    }

    public function notas()
    {
        return $this->hasMany(\App\Models\NotaEvolucionMedica::class, 'cod_am', 'cod_am');
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
        return $this->hasMany(FichaMedicaAdulto::class, 'cod_am', 'cod_am');
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

    public function registrosCuidados()
    {
        return $this->hasMany(RegistroCuidado::class, 'cod_am', 'cod_am');
    }

    public function dispositivos()
    {
        return $this->hasMany(DispositivoResidente::class, 'cod_am', 'cod_am');
    }

    public function dispositivosActivos()
    {
        return $this->dispositivos()->where('estado', 'ACTIVO');
    }

    public function incidentes()
    {
        return $this->hasMany(IncidenteResidente::class, 'cod_am', 'cod_am');
    }

    public function lesiones()
    {
        return $this->hasMany(LesionResidente::class, 'cod_am', 'cod_am');
    }

    public function historialEstadoOperativo()
    {
        return $this->hasMany(HistorialEstadoOperativo::class, 'cod_am', 'cod_am');
    }

    public function habitacion()
    {
        return $this->hasOneThrough(
            Habitacion::class,
            AsignacionAdultoMayor::class,
            'cod_am',
            'cod_habitacion',
            'cod_am',
            'cod_habitacion'
        )->where('asignacion_adulto_mayor.estado', 'ACTIVO');
    }

    public function cama()
    {
        return $this->hasOneThrough(
            Cama::class,
            AsignacionAdultoMayor::class,
            'cod_am',
            'cod_cama',
            'cod_am',
            'cod_cama'
        )->where('asignacion_adulto_mayor.estado', 'ACTIVO');
    }

    public function asignaciones()
    {
        return $this->hasMany(AsignacionAdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function asignacionesTurno()
    {
        return $this->hasMany(AsignacionTurnoAdulto::class, 'cod_am', 'cod_am');
    }

    public function asignacionTurnoActiva()
    {
        return $this->hasOne(AsignacionTurnoAdulto::class, 'cod_am', 'cod_am')
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->latest('fecha_inicio');
    }

    public function preadmisionOrigen()
    {
        return $this->belongsTo(Preadmision::class, 'cod_pre_origen', 'cod_pre');
    }

    public function evaluacionesGeriatricas()
    {
        return $this->hasMany(EvaluacionGeriatrica::class, 'cod_am', 'cod_am');
    }

    public function getAsignacionTurnoActivaAttribute()
    {
        if ($this->relationLoaded('asignacionTurnoActiva')) {
            return $this->relations['asignacionTurnoActiva'];
        }

        return $this->asignacionTurnoActiva()->first();
    }
}
