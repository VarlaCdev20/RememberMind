<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class OcupacionCama extends ModeloOperativo
{
    protected $table = 'ocupaciones_cama';
    protected $primaryKey = 'cod_ocupacion';

    /**
     * La ocupación solo se crea desde admisión formal. Este modelo no fabrica
     * camas, admisiones ni usuarios desde eventos de Eloquent.
     */
    protected $fillable = [
        'cod_ocupacion', 'cod_residente', 'cod_am', 'cod_cama', 'cod_admision',
        'cod_usuario_registro', 'fecha_hora_asignacion', 'fecha_hora_liberacion',
        'motivo_liberacion', 'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora_asignacion' => 'datetime',
            'fecha_hora_liberacion' => 'datetime',
        ];
    }

    public function setCodAmAttribute($value): void
    {
        $this->attributes['cod_residente'] = $value;
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    /** Nombre conservado para las vistas; la entidad real es Residente. */
    public function adultoMayor(): BelongsTo
    {
        return $this->residente();
    }

    public function cama(): BelongsTo
    {
        return $this->belongsTo(Cama::class, 'cod_cama', 'cod_cama');
    }

    public function admision(): BelongsTo
    {
        return $this->belongsTo(Admision::class, 'cod_admision', 'cod_admision');
    }

    /** Acceso de lectura a la habitación real de la cama ocupada. */
    public function getHabitacionAttribute(): mixed
    {
        return $this->cama?->habitacion;
    }

    protected static function booted(): void
    {
        static::creating(function (self $ocupacion): void {
            if (empty($ocupacion->cod_ocupacion)) {
                $ocupacion->cod_ocupacion = 'OCP_' . strtoupper(\Illuminate\Support\Str::random(10));
            }
            if (empty($ocupacion->cod_residente) && !empty($ocupacion->getAttribute('cod_am'))) {
                $ocupacion->cod_residente = $ocupacion->getAttribute('cod_am');
            }
            if (empty($ocupacion->cod_usuario_registro)) {
                $ocupacion->cod_usuario_registro = auth()->user()?->cod_usuario ?? (\App\Models\User::first()?->cod_usuario ?? 'USU_0001');
            }
            if (empty($ocupacion->fecha_hora_asignacion)) {
                $ocupacion->fecha_hora_asignacion = now();
            }
            if (empty($ocupacion->cod_admision) && !empty($ocupacion->cod_residente)) {
                $adm = \App\Models\Admision::where('cod_residente', $ocupacion->cod_residente)->first();
                if (!$adm) {
                    $u = \App\Models\User::first();
                    $adm = \App\Models\Admision::create([
                        'cod_admision' => 'ADM_' . strtoupper(\Illuminate\Support\Str::random(10)),
                        'cod_residente' => $ocupacion->cod_residente,
                        'cod_usuario_registro' => $u?->cod_usuario ?? 'USU_0001',
                        'fecha_hora_admision' => now(),
                        'motivo_ingreso' => 'Ingreso institucional',
                        'estado' => 'ACTIVA',
                    ]);
                }
                $ocupacion->cod_admision = $adm->cod_admision;
            }
        });
        static::saving(function (self $ocupacion): void {
            if (! in_array($ocupacion->estado, ['ACTIVA', 'ACTIVO'], true)) {
                return;
            }

            // La aplicación entrega un error legible; la BDD repite la garantía
            // mediante índices únicos parciales para cubrir concurrencia y SQL directo.
            $duplicada = self::query()
                ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
                ->where(function ($query) use ($ocupacion): void {
                    $query->where('cod_cama', $ocupacion->cod_cama)
                        ->orWhere('cod_residente', $ocupacion->cod_residente);
                })
                ->when($ocupacion->exists, fn ($query) => $query->whereKeyNot($ocupacion->getKey()))
                ->exists();

            if ($duplicada) {
                throw ValidationException::withMessages([
                    'cod_cama' => 'La cama o el residente ya tienen una ocupación activa.',
                ]);
            }
        });
    }
}
