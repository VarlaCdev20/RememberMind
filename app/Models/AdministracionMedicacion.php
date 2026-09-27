<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdministracionMedicacion extends ModeloOperativo
{
    protected $table = 'administraciones_medicacion';

    protected $primaryKey = 'cod_administracion';

    protected function casts(): array
    {
        return [
            'dosis_administrada' => 'decimal:3',
            'fecha_hora_programada' => 'datetime',
            'fecha_hora_administracion' => 'datetime',
        ];
    }

    protected static array $columnasValidas = [
        'cod_administracion',
        'cod_prescripcion',
        'cod_horario_prescripcion',
        'cod_residente',
        'cod_jornada',
        'cod_personal',
        'fecha_hora_programada',
        'fecha_hora_administracion',
        'resultado',
        'dosis_administrada',
        'motivo_omision',
        'efecto_observado',
        'reaccion_adversa',
        'observacion',
        'estado',
    ];

    public function setCodMedAdultoAttribute($value): void
    {
        $this->attributes['cod_prescripcion'] = $value;
    }

    public function setAdministradoAttribute($value): void
    {
        $this->attributes['resultado'] = $value ? 'ADMINISTRADA' : 'OMITIDA';
    }

    public function getAdministradoAttribute(): bool
    {
        return $this->esAdministrada();
    }

    public function getCodMedAdultoAttribute(): string
    {
        return (string) $this->cod_prescripcion;
    }

    protected static function booted(): void
    {
        static::creating(function (self $registro): void {
            if (empty($registro->cod_administracion)) {
                $registro->cod_administracion = 'ADM_'.strtoupper(Str::random(10));
            }
            $rawMed = $registro->attributes['cod_med_adulto'] ?? $registro->attributes['cod_prescripcion'] ?? null;
            if (empty($registro->cod_prescripcion) && ! empty($rawMed)) {
                $registro->cod_prescripcion = $rawMed;
            }

            if (empty($registro->cod_residente) && ! empty($registro->cod_prescripcion)) {
                $p = Prescripcion::find($registro->cod_prescripcion);
                if ($p) {
                    $registro->cod_residente = $p->cod_residente;
                }
            }
            if (empty($registro->cod_personal)) {
                throw new \LogicException('La administración requiere el personal responsable.');
            }
            if (empty($registro->cod_jornada)) {
                throw new \LogicException('La administración requiere una jornada clínica activa.');
            }
            if (empty($registro->fecha_hora_programada)) {
                $fec = $registro->attributes['fecha'] ?? today()->toDateString();
                $hora = $registro->attributes['hora_programada'] ?? '08:00';
                $registro->fecha_hora_programada = Carbon::parse($fec.' '.$hora);
            }
            if (empty($registro->fecha_hora_administracion) && ! empty($registro->attributes['hora_real'])) {
                $fec = $registro->attributes['fecha'] ?? today()->toDateString();
                $registro->fecha_hora_administracion = Carbon::parse($fec.' '.$registro->attributes['hora_real']);
            }
            if (empty($registro->resultado) || $registro->resultado === 'ADMINISTRADA') {
                $adm = $registro->attributes['administrado'] ?? null;
                if ($adm !== null) {
                    $registro->resultado = $adm ? 'ADMINISTRADA' : 'OMITIDA';
                } elseif (empty($registro->resultado)) {
                    $registro->resultado = 'ADMINISTRADA';
                }
            }
            if (empty($registro->estado)) {
                $registro->estado = 'FINALIZADO';
            }
            $validos = [
                'cod_administracion', 'cod_prescripcion', 'cod_horario_prescripcion',
                'cod_residente', 'cod_jornada', 'cod_personal', 'fecha_hora_programada',
                'fecha_hora_administracion', 'resultado', 'dosis_administrada',
                'motivo_omision', 'efecto_observado', 'reaccion_adversa', 'observacion', 'estado',
            ];
            $registro->attributes = array_intersect_key($registro->attributes, array_flip($validos));
        });

        static::saving(function (self $registro): void {

            if (! empty($registro->cod_prescripcion)) {
                $presc = Prescripcion::query()->whereKey($registro->cod_prescripcion)->first();
                if ($presc) {
                    $registro->cod_prescripcion = $presc->cod_prescripcion;
                    if (empty($registro->cod_residente)) {
                        $registro->cod_residente = $presc->cod_residente;
                    }
                }
            }

            if (! empty($registro->cod_prescripcion) && ! empty($registro->cod_residente)
                && ! Prescripcion::query()->whereKey($registro->cod_prescripcion)
                    ->where('cod_residente', $registro->cod_residente)->exists()) {
                throw ValidationException::withMessages([
                    'cod_prescripcion' => 'La prescripción no corresponde al residente.',
                ]);
            }

            if (! empty($registro->cod_horario_prescripcion) && ! empty($registro->cod_prescripcion)
                && ! HorarioPrescripcion::query()->whereKey($registro->cod_horario_prescripcion)
                    ->where('cod_prescripcion', $registro->cod_prescripcion)->exists()) {
                throw ValidationException::withMessages([
                    'cod_horario_prescripcion' => 'El horario no corresponde a la prescripción indicada.',
                ]);
            }
        });
    }

    public function esAdministrada(): bool
    {
        return in_array(strtoupper(trim((string) $this->resultado)), ['ADMINISTRADA', 'ADMINISTRADO', 'REALIZADA', 'APLICADA', 'SUMINISTRADA'], true);
    }

    public function esOmitida(): bool
    {
        return strtoupper(trim((string) $this->resultado)) === 'OMITIDA';
    }

    public function esRechazada(): bool
    {
        return strtoupper(trim((string) $this->resultado)) === 'RECHAZADA';
    }

    public function prescripcion(): BelongsTo
    {
        return $this->belongsTo(Prescripcion::class, 'cod_prescripcion', 'cod_prescripcion');
    }

    public function medicacion(): BelongsTo
    {
        return $this->prescripcion();
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function registrador(): BelongsTo
    {
        return $this->personal();
    }

    public function adultoMayor(): BelongsTo
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_residente', 'cod_residente');
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(HorarioPrescripcion::class, 'cod_horario_prescripcion', 'cod_horario_prescripcion');
    }
}
