<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EjecucionCuidado extends ModeloOperativo
{
    protected $table = 'ejecuciones_cuidado';
    protected $primaryKey = 'cod_ejecucion';

    protected function casts(): array
    {
        return [
            'fecha_hora_programada' => 'datetime',
            'fecha_hora_ejecucion' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (EjecucionCuidado $model) {
            if (empty($model->cod_ejecucion)) {
                $digits = fake()->unique()->numerify('########');
                $model->cod_ejecucion = 'EJE_' . $digits;
            }

            // Mapear cod_am a cod_residente
            if (!empty($model->attributes['cod_am']) && empty($model->attributes['cod_residente'])) {
                $model->cod_residente = $model->attributes['cod_am'];
            }
            unset($model->attributes['cod_am']);

            // Si aún no hay cod_residente, resolver primer residente disponible
            if (empty($model->cod_residente)) {
                $res = Residente::first();
                $model->cod_residente = $res?->cod_residente ?? 'RES_00000001';
            }

            // Resolver cod_intervencion si no viene provisto
            if (empty($model->attributes['cod_intervencion'])) {
                $intervencion = IntervencionCuidado::first();
                if (!$intervencion) {
                    $plan = PlanCuidado::first();
                    if (!$plan) {
                        $plan = PlanCuidado::create([
                            'cod_plan' => $model->attributes['cod_plan'] ?? ('PLC_' . strtoupper(substr(uniqid(), -6))),
                            'cod_residente' => $model->cod_residente,
                            'tipo_plan' => 'GENERAL',
                            'fecha_inicio' => today(),
                            'estado' => 'ACTIVO',
                        ]);
                    }
                    $intervencion = IntervencionCuidado::create([
                        'cod_intervencion' => 'INT_' . strtoupper(substr(uniqid(), -6)),
                        'cod_plan' => $plan->cod_plan,
                        'nombre' => $model->attributes['titulo'] ?? 'Cuidado Asistencial General',
                        'descripcion' => 'Intervención de cuidado general asistencial',
                        'prioridad' => $model->attributes['prioridad'] ?? 'MEDIA',
                        'estado' => 'ACTIVO',
                    ]);
                }
                $model->cod_intervencion = $intervencion->cod_intervencion;
            }

            // Resolver cod_jornada si no viene provista
            if (empty($model->attributes['cod_jornada'])) {
                $jornada = Jornada::whereDate('fecha_jornada', today())->first() ?: Jornada::first();
                if (!$jornada) {
                    $turno = Turno::first() ?: Turno::create([
                        'cod_turno' => 'TUR_GEN_01',
                        'nombre' => 'Turno General',
                        'hora_inicio' => '07:00:00',
                        'hora_fin' => '15:00:00',
                        'estado' => 'ACTIVO',
                    ]);
                    $jornada = Jornada::create([
                        'cod_jornada' => 'JOR_EJE_001',
                        'cod_turno' => $turno->cod_turno,
                        'fecha_jornada' => today(),
                        'estado' => 'ABIERTA',
                    ]);
                }
                $model->cod_jornada = $jornada->cod_jornada;
            }

            // Resolver cod_personal si no viene provisto
            if (empty($model->attributes['cod_personal'])) {
                $personal = null;
                if (!empty($model->attributes['registrado_por'])) {
                    $user = User::find($model->attributes['registrado_por']);
                    $personal = $user?->personal;
                }
                if (!$personal) {
                    $personal = Personal::first();
                }
                if (!$personal) {
                    $personal = Personal::create([
                        'cod_personal' => 'PER_00000001',
                        'cod_usuario' => 'USU_SYS00001',
                        'nombres' => 'Sistema',
                        'apellido_paterno' => 'Enfermeria',
                        'numero_documento' => '00000001',
                        'profesion' => 'ENFERMERIA',
                        'estado' => 'ACTIVO',
                    ]);
                }
                $model->cod_personal = $personal->cod_personal;
            }

            // Mapear fecha_programada + hora_programada a fecha_hora_programada
            if (empty($model->attributes['fecha_hora_programada'])) {
                $f = $model->attributes['fecha_programada'] ?? today()->toDateString();
                $h = $model->attributes['hora_programada'] ?? '08:00:00';
                $model->fecha_hora_programada = "{$f} {$h}";
            }

            if (empty($model->attributes['estado'])) {
                $model->estado = 'PENDIENTE';
            }

            // Limpiar atributos heredados para evitar colisiones SQL en BDD V2
            unset(
                $model->attributes['cod_plan'],
                $model->attributes['cod_turno'],
                $model->attributes['area'],
                $model->attributes['titulo'],
                $model->attributes['fecha_programada'],
                $model->attributes['hora_programada'],
                $model->attributes['prioridad'],
                $model->attributes['registrado_por']
            );
        });
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    public function intervencion(): BelongsTo
    {
        return $this->belongsTo(IntervencionCuidado::class, 'cod_intervencion', 'cod_intervencion');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class, 'cod_jornada', 'cod_jornada');
    }

    // Accessors de compatibilidad con V1
    public function getCodRegistroCuidadoAttribute(): string
    {
        return (string) $this->cod_ejecucion;
    }

    public function getCodTareaAttribute(): string
    {
        return (string) $this->cod_ejecucion;
    }

    public function getTituloAttribute(): string
    {
        return $this->intervencion?->nombre ?? 'Cuidado Asistencial';
    }

    public function getAreaAttribute(): string
    {
        return 'CUIDADOS';
    }

    public function getHoraProgramadaAttribute(): string
    {
        return $this->fecha_hora_programada ? $this->fecha_hora_programada->format('H:i') : '--:--';
    }

    public function getPrioridadAttribute(): string
    {
        return 'MEDIA';
    }
}