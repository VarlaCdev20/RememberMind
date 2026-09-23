<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionResidenteJornada extends ModeloOperativo
{
    protected $table = 'asignaciones_residente_jornada';
    protected $primaryKey = 'cod_asignacion';

    /** Columnas exactas de asignaciones_residente_jornada en PostgreSQL V2. */
    protected $fillable = [
        'cod_asignacion', 'cod_residente', 'cod_am', 'cod_jornada', 'cod_turno', 'cod_personal', 'cod_usu_enfermero',
        'nivel_supervision', 'fecha_hora', 'estado', 'observacion',
    ];

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $asig): void {
            if (empty($asig->cod_asignacion)) {
                $asig->cod_asignacion = 'ARJ_' . strtoupper(\Illuminate\Support\Str::random(10));
            }
            if (empty($asig->fecha_hora)) {
                $asig->fecha_hora = now();
            }
            if (empty($asig->cod_residente) && !empty($asig->getAttribute('cod_am'))) {
                $asig->cod_residente = $asig->getAttribute('cod_am');
            }
            if (empty($asig->cod_jornada)) {
                $targetTurno = $asig->getAttribute('cod_turno') ?: (\App\Models\TurnoEnfermeria::first()?->cod_turno ?? 'TUR_001');
                $j = \App\Models\Jornada::whereDate('fecha_jornada', today())->where('cod_turno', $targetTurno)->first();
                if (!$j) {
                    $j = \App\Models\Jornada::create([
                        'cod_jornada' => 'JOR_' . strtoupper(\Illuminate\Support\Str::random(10)),
                        'cod_turno' => $targetTurno,
                        'fecha_jornada' => today(),
                        'estado' => 'ACTIVA',
                    ]);
                }
                $asig->cod_jornada = $j->cod_jornada;
            }
            if (empty($asig->cod_personal)) {
                $p = \App\Models\Personal::first();
                if (!$p) {
                    $u = \App\Models\User::first();
                    $p = \App\Models\Personal::create([
                        'cod_personal' => 'PER_' . strtoupper(\Illuminate\Support\Str::random(10)),
                        'cod_usuario' => $u?->cod_usuario ?? 'USU_0001',
                        'nombres' => 'Personal',
                        'apellido_paterno' => 'Enfermeria',
                        'numero_documento' => 'DOC_' . strtoupper(\Illuminate\Support\Str::random(8)),
                        'profesion' => 'ENFERMERIA',
                        'estado' => 'ACTIVO',
                    ]);
                }
                $asig->cod_personal = $p->cod_personal;
            }
        });
    }


    public function setCodAmAttribute($value): void
    {
        $this->attributes['cod_residente'] = $value;
    }

    public function setCodUsuEnfermeroAttribute($value): void
    {
        $personal = \App\Models\Personal::where('cod_usuario', $value)->first();
        if (!$personal) {
            $u = \App\Models\User::where('cod_usuario', $value)->first();
            $nom = !empty($u?->nombres) ? $u->nombres : 'Enfermero';
            $ape = !empty($u?->ap_paterno) ? $u->ap_paterno : 'Turno';
            $personal = \App\Models\Personal::create([
                'cod_personal' => 'PER_' . strtoupper(\Illuminate\Support\Str::random(10)),
                'cod_usuario' => $value,
                'nombres' => $nom,
                'apellido_paterno' => $ape,
                'numero_documento' => 'DOC_' . strtoupper(\Illuminate\Support\Str::random(8)),
                'profesion' => 'ENFERMERIA',
                'estado' => 'ACTIVO',
            ]);
        }
        $this->attributes['cod_personal'] = $personal->cod_personal;
    }

    public function setCodTurnoAttribute($value): void
    {
        $jornada = \App\Models\Jornada::where('cod_turno', $value)->whereDate('fecha_jornada', today())->first();
        if (!$jornada) {
            $jornada = \App\Models\Jornada::where('cod_turno', $value)->first();
        }
        if ($jornada) {
            $this->attributes['cod_jornada'] = $jornada->cod_jornada;
        } else {
            $jornada = \App\Models\Jornada::create([
                'cod_jornada' => 'JOR_' . strtoupper(\Illuminate\Support\Str::random(10)),
                'cod_turno' => $value,
                'fecha_jornada' => today(),
                'estado' => 'ACTIVA',
            ]);
            $this->attributes['cod_jornada'] = $jornada->cod_jornada;
        }
    }
    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    /** Nombre conservado para las vistas existentes; la entidad real es Residente. */
    public function adultoMayor(): BelongsTo
    {
        return $this->residente();
    }

    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class, 'cod_jornada', 'cod_jornada');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    // Propiedades calculadas para UI; las consultas deben usar columnas V2.
    public function getTurnoAttribute(): mixed
    {
        return $this->jornada?->turno;
    }

    public function getEnfermeroAttribute(): mixed
    {
        return $this->personal?->usuario;
    }

    public function getCodTurnoAttribute(): ?string
    {
        return $this->jornada?->cod_turno;
    }
}
