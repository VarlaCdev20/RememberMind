<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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

    public function setCodAmAttribute($value): void
    {
        $this->attributes['cod_residente'] = $value;
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

    public function getCodAmAttribute(): string
    {
        return (string) $this->cod_residente;
    }

    protected static function booted(): void
    {
        static::creating(function (self $registro): void {
            if (empty($registro->cod_administracion)) {
                $registro->cod_administracion = 'ADM_' . strtoupper(Str::random(10));
            }
            $rawMed = $registro->attributes['cod_med_adulto'] ?? $registro->attributes['cod_prescripcion'] ?? null;
            if (empty($registro->cod_prescripcion) && !empty($rawMed)) {
                $registro->cod_prescripcion = $rawMed;
            }
            $rawAm = $registro->attributes['cod_am'] ?? $registro->attributes['cod_residente'] ?? null;
            if (empty($registro->cod_residente) && !empty($rawAm)) {
                $registro->cod_residente = $rawAm;
            }
            if (empty($registro->cod_residente) && !empty($registro->cod_prescripcion)) {
                $p = Prescripcion::find($registro->cod_prescripcion);
                if ($p) {
                    $registro->cod_residente = $p->cod_residente;
                }
            }
            if (empty($registro->cod_personal)) {
                $codUsu = $registro->attributes['registrado_por'] ?? null;
                $pers = null;
                if ($codUsu) {
                    $pers = Personal::where('cod_usuario', $codUsu)->first() ?? Personal::where('cod_personal', $codUsu)->first();
                }
                $pers ??= auth()->user()?->personal ?? Personal::first();
                if (!$pers) {
                    $u = auth()->user() ?? User::first();
                    $pers = Personal::create([
                        'cod_personal' => 'PER_' . strtoupper(Str::random(10)),
                        'cod_usuario' => $u?->cod_usuario ?? 'USU_0001',
                        'nombres' => 'Personal',
                        'apellido_paterno' => 'Enfermeria',
                        'numero_documento' => 'DOC_' . strtoupper(Str::random(8)),
                        'profesion' => 'ENFERMERIA',
                        'estado' => 'ACTIVO',
                    ]);
                }
                $registro->cod_personal = $pers->cod_personal;
            }
            if (empty($registro->cod_jornada)) {
                $j = Jornada::whereDate('fecha_jornada', today())->first() ?? Jornada::first();
                if (!$j) {
                    $t = TurnoEnfermeria::first();
                    if (!$t) {
                        $t = TurnoEnfermeria::create([
                            'cod_turno' => 'TUR_' . strtoupper(Str::random(10)),
                            'orden' => 1,
                            'nombre' => 'Turno Mañana',
                            'hora_inicio' => '07:00:00',
                            'hora_fin' => '15:00:00',
                            'estado' => 'ACTIVO',
                        ]);
                    }
                    $j = Jornada::create([
                        'cod_jornada' => 'JOR_' . strtoupper(Str::random(10)),
                        'cod_turno' => $t->cod_turno,
                        'fecha_jornada' => today(),
                        'estado' => 'ACTIVA',
                    ]);
                }
                $registro->cod_jornada = $j->cod_jornada;
            }
            if (empty($registro->fecha_hora_programada)) {
                $fec = $registro->attributes['fecha'] ?? today()->toDateString();
                $hora = $registro->attributes['hora_programada'] ?? '08:00';
                $registro->fecha_hora_programada = Carbon::parse($fec . ' ' . $hora);
            }
            if (empty($registro->fecha_hora_administracion) && !empty($registro->attributes['hora_real'])) {
                $fec = $registro->attributes['fecha'] ?? today()->toDateString();
                $registro->fecha_hora_administracion = Carbon::parse($fec . ' ' . $registro->attributes['hora_real']);
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
                'motivo_omision', 'efecto_observado', 'reaccion_adversa', 'observacion', 'estado'
            ];
            $registro->attributes = array_intersect_key($registro->attributes, array_flip($validos));
        });

        static::saving(function (self $registro): void {
            $rawAm = $registro->attributes['cod_am'] ?? null;
            if (empty($registro->cod_residente) && !empty($rawAm)) {
                $registro->cod_residente = $rawAm;
            }
            if (!empty($registro->cod_prescripcion)) {
                $presc = Prescripcion::where($registro->cod_prescripcion)->first() ?? Prescripcion::where('cod_med_adulto', $registro->cod_prescripcion)->first();
                if ($presc) {
                    $registro->cod_prescripcion = $presc->cod_prescripcion;
                    if (empty($registro->cod_residente)) {
                        $registro->cod_residente = $presc->cod_residente;
                    }
                }
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
