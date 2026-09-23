<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
class Residente extends ModeloOperativo {
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    private static bool $creacionDesdeAdmision = false;
    protected $table='residentes'; protected $primaryKey='cod_residente';
    protected function casts(): array { return ['fecha_nacimiento'=>'date']; }
    protected static function booted(): void { static::creating(function (): void { if (! self::$creacionDesdeAdmision) { throw new \LogicException('Los residentes solo pueden crearse mediante la admisión formal.'); } }); }
    public static function crearDesdeAdmision(array $attributes): static { self::$creacionDesdeAdmision=true; try { return static::query()->create($attributes); } finally { self::$creacionDesdeAdmision=false; } }
    public function admisiones(): HasMany { return $this->hasMany(Admision::class,'cod_residente','cod_residente'); }
    public function contactos(): BelongsToMany { return $this->belongsToMany(Contacto::class,'residentes_contactos','cod_residente','cod_contacto')->using(ResidenteContacto::class); }
    public function vinculosContacto(): HasMany { return $this->hasMany(ResidenteContacto::class,'cod_residente','cod_residente'); }
    public function ocupacionesCama(): HasMany { return $this->hasMany(OcupacionCama::class,'cod_residente','cod_residente'); }
    public function ocupacionActiva(): HasOne { return $this->hasOne(OcupacionCama::class,'cod_residente','cod_residente')->whereIn('estado',['ACTIVA','ACTIVO']); }
    public function atenciones(): HasMany { return $this->hasMany(Atencion::class,'cod_residente','cod_residente'); }
    public function asignacionesJornada(): HasMany { return $this->hasMany(AsignacionResidenteJornada::class,'cod_residente','cod_residente'); }
    public function prescripciones(): HasMany { return $this->hasMany(Prescripcion::class,'cod_residente','cod_residente'); }
        public function diagnosticos(): HasMany { return $this->hasMany(Diagnostico::class,'cod_residente','cod_residente'); }
    public function antecedentesClinicos(): HasMany { return $this->hasMany(AntecedenteClinico::class,'cod_residente','cod_residente'); }
    public function alergias(): HasMany { return $this->hasMany(Alergia::class,'cod_residente','cod_residente'); }
    public function notasClinicas(): HasMany { return $this->hasMany(NotaClinica::class,'cod_residente','cod_residente'); }
    public function seguros(): HasMany { return $this->hasMany(SeguroResidente::class,'cod_residente','cod_residente'); }
    public function dispositivos(): HasMany { return $this->hasMany(DispositivoClinico::class,'cod_residente','cod_residente'); }
    public function dispositivosActivos(): HasMany { return $this->dispositivos()->whereIn('estado', ['ACTIVO', 'ACTIVA']); }
    public function heridas(): HasMany { return $this->hasMany(Herida::class, 'cod_residente', 'cod_residente'); }
    public function curaciones(): HasManyThrough { return $this->hasManyThrough(CuracionHerida::class, Herida::class, 'cod_residente', 'cod_herida', 'cod_residente', 'cod_herida'); }
    public function incidentesClinicos(): HasMany { return $this->hasMany(Incidente::class, 'cod_residente', 'cod_residente'); }
    public function registrosIngesta(): HasMany { return $this->hasMany(RegistroIngesta::class, 'cod_residente', 'cod_residente'); }
    public function registrosHidratacion(): HasMany { return $this->hasMany(RegistroHidratacion::class, 'cod_residente', 'cod_residente'); }
    public function registrosEliminacion(): HasMany { return $this->hasMany(RegistroEliminacion::class, 'cod_residente', 'cod_residente'); }
    public function registrosMovilidad(): HasMany { return $this->hasMany(RegistroMovilidad::class, 'cod_residente', 'cod_residente'); }
    public function registrosSueno(): HasMany { return $this->hasMany(RegistroSueno::class, 'cod_residente', 'cod_residente'); }
    public function registrosConductuales(): HasMany { return $this->hasMany(RegistroConductual::class, 'cod_residente', 'cod_residente'); }
    public function controlesCognitivos(): HasMany { return $this->hasMany(ControlCognitivo::class, 'cod_residente', 'cod_residente'); }
    public function valoracionesDolor(): HasMany { return $this->hasMany(ValoracionDolor::class, 'cod_residente', 'cod_residente'); }
    public function fichasMedicas(): HasMany { return $this->hasMany(Atencion::class,'cod_residente','cod_residente'); }
    public function medicaciones(): HasMany { return $this->hasMany(Prescripcion::class,'cod_residente','cod_residente'); }
    public function valoracionesFuncionales(): HasMany { return $this->hasMany(ValoracionFuncional::class,'cod_residente','cod_residente'); }
    public function administracionesMedicacion(): HasMany { return $this->hasMany(AdministracionMedicacion::class,'cod_residente','cod_residente'); }
    public function alertas(): HasMany { return $this->hasMany(Alerta::class,'cod_residente','cod_residente'); }
    public function alertasAbiertas(): HasMany { return $this->hasMany(Alerta::class,'cod_residente','cod_residente')->whereIn('estado',['ABIERTA','EN_ATENCION','PENDIENTE']); }
    public function valoracionesEnfermeria(): HasMany { return $this->hasMany(Atencion::class,'cod_residente','cod_residente'); }
    public function signosVitales(): HasMany { return $this->hasMany(SignoVital::class,'cod_residente','cod_residente'); }
    public function planesCuidado(): HasMany { return $this->hasMany(PlanCuidado::class,'cod_residente','cod_residente'); }
    public function planCuidadoActivo(): HasOne { return $this->hasOne(PlanCuidado::class,'cod_residente','cod_residente')->whereIn('estado',['ACTIVO','ACTIVA']); }
    public function ejecucionesCuidado(): HasMany { return $this->hasMany(EjecucionCuidado::class,'cod_residente','cod_residente'); }
    public function pasesTurno(): HasMany { return $this->hasMany(PaseTurno::class,'cod_residente','cod_residente')->orderByDesc('fecha_hora'); }
    public function aplicacionesInstrumento(): HasMany { return $this->hasMany(AplicacionInstrumento::class,'cod_residente','cod_residente'); }
    public function notas(): HasMany { return $this->hasMany(NotaClinica::class,'cod_residente','cod_residente'); }
    /** Adaptadores de lectura para las pestañas restauradas; todos consultan tablas V2. */
    public function seguimientosDiarios(): HasMany { return $this->hasMany(Atencion::class, 'cod_residente', 'cod_residente'); }
    public function valoracionesMedicas(): HasMany { return $this->atenciones(); }
    public function registrosCuidados(): HasMany { return $this->ejecucionesCuidado(); }
    public function incidentes(): HasMany { return $this->hasMany(Alerta::class,'cod_residente','cod_residente')->whereIn('tipo',['INCIDENTE','CAIDA','EVENTO_ADVERSO']); }
    public function tareasActuales(): HasMany { return $this->ejecucionesCuidado(); }
    public function asignacionesTurno(): HasMany { return $this->asignacionesJornada(); }
    public function asignacionTurnoActiva(): HasOne { return $this->hasOne(AsignacionResidenteJornada::class, 'cod_residente', 'cod_residente')->where('asignaciones_residente_jornada.estado', 'ACTIVA'); }
    public function cama(): HasOneThrough { return $this->hasOneThrough(Cama::class,OcupacionCama::class,'cod_residente','cod_cama','cod_residente','cod_cama')->whereIn('ocupaciones_cama.estado',['ACTIVA','ACTIVO']); }
    public function getHabitacionAttribute(): ?Habitacion { return $this->cama?->habitacion; }
    public function getCodAmAttribute(): string { return (string) $this->cod_residente; }
    public function getApPaternoAttribute(): string { return (string) $this->apellido_paterno; }
    public function getApMaternoAttribute(): string { return (string) $this->apellido_materno; }
    public function getFechaNacAttribute() { return $this->fecha_nacimiento; }
    public function getCiAttribute(): ?string { return $this->numero_documento; }
    public function getCodCamaAttribute(): ?string { return $this->ocupacionActiva?->cod_cama; }
    public function getCodHabitacionAttribute(): ?string { return $this->cama?->cod_habitacion; }

    public function familiares(): BelongsToMany {
        return $this->belongsToMany(Contacto::class, 'residentes_contactos', 'cod_residente', 'cod_contacto')
            ->withPivot(['parentesco', 'responsable_principal', 'contacto_emergencia', 'autoriza_informacion', 'autoriza_salida', 'estado']);
    }
    public function observaciones(): HasMany { return $this->hasMany(NotaClinica::class, 'cod_residente', 'cod_residente'); }
    public function actividades(): BelongsToMany { return $this->belongsToMany(Actividad::class, 'participantes_actividad', 'cod_residente', 'cod_actividad'); }
    public function evaluacionesGeriatricas(): HasMany { return $this->hasMany(AplicacionInstrumento::class, 'cod_residente', 'cod_residente'); }
    public function documentos(): HasMany { return $this->hasMany(Documento::class, 'cod_residente', 'cod_residente'); }
    public function historialEstados(): HasMany { return $this->hasMany(HistorialEstadoResidente::class, 'cod_residente', 'cod_residente'); }
    public function getNombreCompletoAttribute(): string { return trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}"); }
    public function getEdadAttribute(): ?int { return $this->fecha_nacimiento?->age; }
    public function getEstadoAdultoAttribute(): string { return (string) ($this->attributes['estado'] ?? 'ACTIVO'); }
        public function getFichaResumenAttribute(): ?object {
        return app(\App\Services\Clinica\FichaMedicaService::class)->obtenerFichaAgregada($this->cod_residente);
    }
    public function getFechaIngAttribute(): ?\Carbon\Carbon { $adm = $this->admisiones()->orderBy('fecha_hora_admision', 'asc')->first(); return $adm && $adm->fecha_hora_admision ? \Carbon\Carbon::parse($adm->fecha_hora_admision) : null; }

    /** Devuelve "Hab. X" o "Sin habitación asignada" */
    public function getHabitacionTextoAttribute(): string
    {
        $num = $this->cama?->habitacion?->numero;
        return $num ? "Hab. {$num}" : "Sin ubicación asignada";
    }

    /** Devuelve "Cama Y" o "" si no existe */
    public function getCamaTextoAttribute(): string
    {
        $num = $this->cama?->numero ?? $this->cama?->codigo ?? null;
        return $num ? "Cama {$num}" : "";
    }

    /** Devuelve "Hab. X · Cama Y" formateado para enfermería */
    public function getUbicacionFormateadaAttribute(): string
    {
        $hab  = $this->cama?->habitacion?->numero;
        $cama = $this->cama?->numero ?? $this->cama?->codigo ?? null;
        if (!$hab && !$cama) return "Sin ubicación asignada";
        if (!$cama) return "Hab. {$hab}";
        return "Hab. {$hab} · Cama {$cama}";
    }
}