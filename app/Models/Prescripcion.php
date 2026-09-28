<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescripcion extends ModeloOperativo
{
    public ?string $hora_programada_temp = null;
    protected $table = 'prescripciones';
    protected $primaryKey = 'cod_prescripcion';

    /**
     * Columnas reales de PostgreSQL V2. Una prescripción exige residente,
     * atención, medicamento y personal médico ya existentes; el modelo no crea
     * registros auxiliares ni traduce atributos legacy de manera silenciosa.
     */
    protected $fillable = [
        'cod_prescripcion', 'cod_residente', 'cod_atencion', 'cod_medicamento',
        'cod_personal', 'cod_personal_suspension', 'dosis', 'unidad_dosis',
        'via_administracion', 'frecuencia', 'indicacion', 'segun_necesidad',
        'fecha_hora_prescripcion', 'fecha_hora_suspension', 'motivo_suspension',
        'estado', 'observacion',
        'nombre_medicamento', 'hora_programada', 'fecha_inicio',
    ];

    protected function casts(): array
    {
        return [
            'dosis' => 'decimal:3',
            'segun_necesidad' => 'boolean',
            'fecha_hora_prescripcion' => 'datetime',
            'fecha_hora_suspension' => 'datetime',
        ];
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    /** Nombre conservado para las vistas existentes; consulta residentes V2. */
    public function adultoMayor(): BelongsTo
    {
        return $this->residente();
    }

    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class, 'cod_medicamento', 'cod_medicamento');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function personalSuspension(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal_suspension', 'cod_personal');
    }

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'cod_atencion', 'cod_atencion');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(HorarioPrescripcion::class, 'cod_prescripcion', 'cod_prescripcion');
    }

    public function administraciones(): HasMany
    {
        return $this->hasMany(AdministracionMedicacion::class, 'cod_prescripcion', 'cod_prescripcion');
    }

    // Accessors de presentación: no deben utilizarse como nombres de columna en queries.
    public function getCodMedAdultoAttribute(): string
    {
        return (string) $this->cod_prescripcion;
    }

    public function getCodMedAttribute(): string
    {
        return (string) $this->cod_prescripcion;
    }

    public function getNombreMedicamentoAttribute(): string
    {
        return (string) ($this->medicamento?->nombre_comercial
            ?: ($this->medicamento?->nombre_generico ?: $this->indicacion ?: 'Medicamento'));
    }

    public function getEsPrnAttribute(): bool
    {
        return (bool) $this->segun_necesidad;
    }

    public function getHoraProgramadaAttribute(): mixed
    {
        return $this->horarios->firstWhere('estado', 'ACTIVO')?->hora_programada
            ?? $this->horarios->first()?->hora_programada;
    }

    public function getFechaInicioAttribute(): mixed
    {
        return $this->fecha_hora_prescripcion;
    }

    public function getFechaFinAttribute(): mixed
    {
        return $this->fecha_hora_suspension;
    }

    public function getMedicoIndicaAttribute(): ?string
    {
        if (! $this->personal) {
            return null;
        }

        return trim("{$this->personal->nombres} {$this->personal->apellido_paterno} {$this->personal->apellido_materno}");
    }



    public function setNombreMedicamentoAttribute($value): void
    {
        $this->attributes['indicacion'] = $value;
    }


    public function setDosisAttribute($value): void
    {
        if (is_string($value) && !is_numeric($value)) {
            if (preg_match('/^(\d+(?:\.\d+)?)/', trim($value), $matches)) {
                $this->attributes['dosis'] = (float) $matches[1];
                if (empty($this->attributes['unidad_dosis'])) {
                    $unit = trim(str_replace($matches[1], '', $value));
                    $this->attributes['unidad_dosis'] = !empty($unit) ? $unit : 'comp';
                }
                return;
            }
        }
        $this->attributes['dosis'] = $value;
    }


    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {

                return parent::where($column, $operator, $value, $boolean);
            }
        };
    }

    protected static function booted(): void
    {

        static::created(function (self $model) {
            $horaProg = $model->hora_programada_temp ?? $model->attributes['hora_programada'] ?? null;
            if ($horaProg) {
                \App\Models\HorarioPrescripcion::create([
                    'cod_horario_prescripcion' => 'HPR_' . strtoupper(\Illuminate\Support\Str::random(10)),
                    'cod_prescripcion' => $model->cod_prescripcion,
                    'hora_programada' => $horaProg,
                    'dosis_programada' => $model->dosis ?? 1.0,
                    'estado' => 'ACTIVO',
                ]);
            }
        });
        static::creating(function (self $model) {

            if (empty($model->fecha_hora_prescripcion)) {
                $model->fecha_hora_prescripcion = $model->attributes['fecha_inicio'] ?? now();
            }
            if (isset($model->attributes['hora_programada'])) {
                $model->hora_programada_temp = $model->attributes['hora_programada'];
            }
            if (empty($model->cod_prescripcion)) {
                $model->cod_prescripcion = "PRS_" . strtoupper(\Illuminate\Support\Str::random(10));
            }

            if (empty($model->cod_medicamento)) {
                throw new \LogicException('La prescripción requiere un medicamento del catálogo.');
            }
            if (empty($model->cod_atencion)) {
                throw new \LogicException('La prescripción requiere una atención clínica existente.');
            }

            unset(
                                $model->attributes['nombre_medicamento'],
                $model->attributes['hora_programada'],
                $model->attributes['fecha_inicio']
            );
            if (empty($model->cod_personal)) {
                throw new \LogicException('La prescripción requiere el profesional responsable.');
            }
            if (!isset($model->segun_necesidad)) {
                $model->segun_necesidad = false;
            }
            if (empty($model->fecha_hora_prescripcion)) {
                $model->fecha_hora_prescripcion = isset($model->attributes["fecha_inicio"]) ? \Carbon\Carbon::parse($model->attributes["fecha_inicio"]) : now();
            }
            if (empty($model->estado)) {
                $model->estado = "ACTIVO";
            }
            if (is_string($model->dosis) && !is_numeric($model->dosis)) {
                if (preg_match("/^([0-9]+(?:\.[0-9]+)?)\s*(.*)$/", trim($model->dosis), $m)) {
                    $model->dosis = (float) $m[1];
                    $model->unidad_dosis = $m[2] ?: "unidad";
                } else {
                    $model->dosis = 1.0;
                    $model->unidad_dosis = $model->dosis;
                }
            }
        });

    }

}
