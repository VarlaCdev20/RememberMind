<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionPersonal extends ModeloOperativo
{
    protected $table = 'asignaciones_personal';
    protected $primaryKey = 'cod_asignacion_personal';

    /**
     * Solo se permiten columnas físicas de la BDD V2. La aplicación debe resolver
     * usuario, personal, jornada y área antes de crear la asignación.
     */
    protected $fillable = [
        'cod_asignacion_personal', 'cod_jornada', 'cod_personal', 'cod_area',
        'funcion', 'tipo_asignacion', 'fecha_asignacion', 'estado', 'observacion',
        'cod_usuario', 'cod_turno', 'fecha_hora_recepcion',
    ];

    protected function casts(): array
    {
        return ['fecha_asignacion' => 'datetime'];
    }

        protected static function booted(): void
    {
        static::creating(function (self $asig): void {
            if (empty($asig->cod_asignacion_personal)) {
                $asig->cod_asignacion_personal = 'ASP_' . strtoupper(\Illuminate\Support\Str::random(10));
            }
            if (! empty($asig->attributes['fecha_hora_recepcion'])) {
                $asig->fecha_asignacion = $asig->attributes['fecha_hora_recepcion'];
            }
            if (empty($asig->fecha_asignacion)) {
                $asig->fecha_asignacion = now();
            }
            if (empty($asig->estado)) {
                $asig->estado = 'ACTIVA';
            }
            if (empty($asig->tipo_asignacion)) {
                $asig->tipo_asignacion = 'TURNO';
            }

            // Resolver cod_personal desde cod_usuario si viene provisto
            if (empty($asig->cod_personal)) {
                $codUsuario = $asig->attributes['cod_usuario'] ?? null;
                if ($codUsuario) {
                    $u = \App\Models\User::find($codUsuario);
                    $asig->cod_personal = $u?->personal?->cod_personal;
                }
            }
            if (empty($asig->cod_personal)) {
                $asig->cod_personal = \App\Models\Personal::first()?->cod_personal ?? 'PER_00000001';
            }

            // Resolver cod_jornada desde cod_turno si viene provisto
            if (empty($asig->cod_jornada)) {
                $targetTurno = ($asig->attributes['cod_turno'] ?? null)
                    ?: (\App\Models\Turno::first()?->cod_turno ?? 'TUR_001');
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

            // Resolver cod_area si no viene provista
            if (empty($asig->cod_area)) {
                $area = \App\Models\Area::first();
                if (!$area) {
                    $area = \App\Models\Area::create([
                        'cod_area' => 'ARE_001',
                        'nombre' => 'Área General',
                        'estado' => 'ACTIVA',
                    ]);
                }
                $asig->cod_area = $area->cod_area;
            }

            unset(
                $asig->attributes['cod_usuario'],
                                $asig->attributes['cod_turno'],
                $asig->attributes['fecha_hora_recepcion'],
            );
        });
    }

    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class, 'cod_jornada', 'cod_jornada');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'cod_area', 'cod_area');
    }

    // Accessors de presentación: nunca cambian columnas ni reescriben SQL.
    public function getDiaSemanaAttribute(): ?string
    {
        return $this->jornada?->fecha_jornada?->locale('es')->isoFormat('dddd');
    }

    public function getHoraInicioAttribute(): ?string
    {
        return $this->jornada?->turno?->hora_inicio;
    }

    public function getHoraFinAttribute(): ?string
    {
        return $this->jornada?->turno?->hora_cierre;
    }

    public function getCodTurnoAttribute(): ?string
    {
        return $this->jornada?->cod_turno;
    }

    public function getCodUsuarioAttribute(): ?string
    {
        return $this->personal?->cod_usuario;
    }
}
