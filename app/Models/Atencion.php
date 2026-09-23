<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Atencion extends ModeloOperativo
{
    protected $table = 'atenciones';
    protected $primaryKey = 'cod_atencion';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }

    public function adultoMayor(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function getObservacionMedicaAttribute(): ?string
    {
        return $this->observacion;
    }

    public function getCreatedAtAttribute(): mixed
    {
        return $this->fecha_hora;
    }

    public function getUpdatedAtAttribute(): mixed
    {
        return $this->fecha_hora;
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'cod_area', 'cod_area');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(NotaClinica::class, 'cod_atencion', 'cod_atencion');
    }

    public function getFechaAttribute(): ?\Carbon\Carbon
    {
        return $this->fecha_hora ? \Carbon\Carbon::parse($this->fecha_hora) : null;
    }

    public function getHoraAttribute(): ?string
    {
        return $this->fecha_hora?->format('H:i');
    }

    public function getObsAttribute(): ?string
    {
        return $this->observacion;
    }

    public function getTipoAtencionAttribute(): object
    {
        return (object)['nombre' => $this->attributes['tipo_atencion'] ?? 'General', 'tipo' => $this->attributes['tipo_atencion'] ?? 'General'];
    }

    public function getCodAmAttribute(): string
    {
        return (string) $this->cod_residente;
    }

    public function getCodAtenAdulAttribute(): string
    {
        return (string) $this->cod_atencion;
    }

    public function getCodSegDiarioAttribute(): string
    {
        return (string) $this->cod_atencion;
    }

    public function getRegistradoPorAttribute()
    {
        return $this->personal?->usuario;
    }

    public function scopeConIncidente($query)
    {
        return $query->where('motivo', 'like', '%incidente%');
    }

    public function scopeRequiereMedico($query)
    {
        return $query->where('motivo', 'like', '%medico%');
    }

    public function scopePorFecha($query, string $fecha)
    {
        return $query->whereDate('fecha_hora', $fecha);
    }

    public function getCreatedAtColumn()
    {
        return 'fecha_hora';
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $model) {
            if (empty($model->cod_atencion)) {
                $model->cod_atencion = 'ATN_' . strtoupper(Str::random(10));
            }
            if (isset($model->attributes['cod_am']) && empty($model->cod_residente)) {
                $model->cod_residente = $model->attributes['cod_am'];
            }
            if (isset($model->attributes['fecha_hora_atencion'])) {
                if (empty($model->fecha_hora)) {
                    $model->fecha_hora = $model->attributes['fecha_hora_atencion'];
                }
                unset($model->attributes['fecha_hora_atencion']);
            }
            if (empty($model->fecha_hora)) {
                $model->fecha_hora = $model->attributes['fecha'] ?? now();
            }
            if (empty($model->attributes['tipo_atencion'])) {
                $model->attributes['tipo_atencion'] = 'SEGUIMIENTO_DIARIO';
            }
            if (empty($model->motivo)) {
                $obs = $model->attributes['observacion'] ?? '';
                $model->motivo = !empty($obs) ? mb_substr((string)$obs, 0, 100) : 'Seguimiento diario de enfermería';
            }
            if (empty($model->estado)) {
                $model->estado = 'FINALIZADA';
            }

            if (empty($model->cod_personal)) {
                $codUsu = $model->attributes['registrado_por'] ?? null;
                $p = null;
                if ($codUsu) {
                    $p = Personal::where('cod_usuario', $codUsu)->first() ?? Personal::where('cod_personal', $codUsu)->first();
                }
                if (!$p) {
                    $p = Personal::first();
                }
                if (!$p) {
                    $u = User::first() ?? User::factory()->create();
                    $p = Personal::create([
                        'cod_personal' => 'PER_' . strtoupper(Str::random(10)),
                        'cod_usuario' => $u->cod_usuario,
                        'nombres' => 'Personal',
                        'apellido_paterno' => 'Enfermeria',
                        'numero_documento' => (string) rand(10000000, 99999999),
                        'profesion' => 'ENFERMERO',
                        'estado' => 'ACTIVO',
                    ]);
                }
                $model->cod_personal = $p->cod_personal;
            }

            if (empty($model->cod_area) || !Area::where('cod_area', $model->cod_area)->exists()) {
                $a = Area::first();
                if (!$a) {
                    $a = Area::create([
                        'cod_area' => 'ARE_ENF',
                        'nombre' => 'Enfermería',
                        'estado' => 'ACTIVO',
                    ]);
                }
                $model->cod_area = $a->cod_area;
            }

            // Descartar campos legacy que no pertenecen a la tabla atenciones
            unset($model->attributes['cod_seg_diario']);
            unset($model->attributes['cod_am']);
            unset($model->attributes['cod_turno']);
            unset($model->attributes['cod_plan']);
            unset($model->attributes['registrado_por']);
            unset($model->attributes['fecha']);
            unset($model->attributes['hora']);
            unset($model->attributes['hora_inicio']);
            unset($model->attributes['hora_fin']);
            unset($model->attributes['estado_general']);
            unset($model->attributes['alimentacion']);
            unset($model->attributes['porcentaje_alimentacion']);
            unset($model->attributes['hidratacion']);
            unset($model->attributes['movilidad']);
            unset($model->attributes['intento_caminar_solo']);
            unset($model->attributes['higiene']);
            unset($model->attributes['sueno']);
            unset($model->attributes['orientacion']);
            unset($model->attributes['repite_preguntas']);
            unset($model->attributes['confusion_observable']);
            unset($model->attributes['conducta']);
            unset($model->attributes['participacion']);
            unset($model->attributes['incidente']);
            unset($model->attributes['requiere_medico']);
        });
    }
}
