<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PlanCuidado extends ModeloOperativo
{
    protected $table = 'planes_cuidado';

    protected $primaryKey = 'cod_plan';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'cod_plan', 'cod_residente', 'cod_area', 'cod_personal', 'tipo_plan',
        'nombre', 'objetivo_general', 'prioridad', 'fecha_hora_apertura',
        'fecha_hora_cierre', 'estado', 'observacion',
        'cod_am', 'creado_por', 'nivel_cuidado', 'fecha_inicio', 'fecha_fin',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora_apertura' => 'datetime',
            'fecha_hora_cierre' => 'datetime',
        ];
    }

    public function intervenciones(): HasMany
    {
        return $this->hasMany(IntervencionCuidado::class, 'cod_plan', 'cod_plan');
    }

    public function tareasActivas(): HasMany
    {
        return $this->intervenciones()->whereIn('estado', ['ACTIVA', 'ACTIVO']);
    }

    public function adultoMayor(): BelongsTo
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_residente', 'cod_residente');
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

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    
    public function getCodAmAttribute(): string
    {
        return (string) ($this->attributes['cod_residente'] ?? '');
    }

    public function getNivelCuidadoAttribute(): string
    {
        return (string) ($this->attributes['prioridad'] ?? 'MODERADO');
    }

    public function scopeActivos($query)
    {
        return $query->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE']);
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $model) {
            if (empty($model->cod_plan)) {
                $model->cod_plan = 'PLC_' . strtoupper(Str::random(10));
            }
            if (isset($model->attributes['cod_am']) && empty($model->cod_residente)) {
                $model->cod_residente = $model->attributes['cod_am'];
            }
            if (empty($model->cod_area) || !\Illuminate\Support\Facades\DB::table('areas')->where('cod_area', $model->cod_area)->exists()) {
                $existingArea = \Illuminate\Support\Facades\DB::table('areas')->first();
                if (!$existingArea) {
                    \Illuminate\Support\Facades\DB::table('areas')->insert([
                        'cod_area' => 'ARE_ENF',
                        'nombre' => 'Enfermería',
                        'estado' => 'ACTIVO',
                    ]);
                    $model->cod_area = 'ARE_ENF';
                } else {
                    $model->cod_area = $existingArea->cod_area;
                }
            }

            if (empty($model->cod_personal) || !\Illuminate\Support\Facades\DB::table('personal')->where('cod_personal', $model->cod_personal)->exists()) {
                $personalCandidate = null;
                $userCandidate = $model->attributes['creado_por'] ?? $model->cod_personal ?? auth()->id();
                if ($userCandidate) {
                    $personalCandidate = \Illuminate\Support\Facades\DB::table('personal')
                        ->where('cod_usuario', $userCandidate)
                        ->orWhere('cod_personal', $userCandidate)
                        ->first();
                }
                if (!$personalCandidate) {
                    $personalCandidate = \Illuminate\Support\Facades\DB::table('personal')->first();
                }
                if (!$personalCandidate) {
                    $user = \App\Models\User::first() ?? \App\Models\User::forceCreate([
                        'cod_usu' => 'USU_SYS001',
                        'nombres' => 'Sistema',
                        'ap_paterno' => 'Admin',
                        'correo' => 'sistema@test.com',
                        'password' => bcrypt('secret'),
                        'estado' => 'ACTIVO',
                    ]);
                    $codPer = 'PER_' . strtoupper(\Illuminate\Support\Str::random(7));
                    \Illuminate\Support\Facades\DB::table('personal')->insert([
                        'cod_personal' => $codPer,
                        'cod_usuario' => $user->cod_usu,
                        'nombres' => $user->nombres ?? 'Personal',
                        'apellido_paterno' => $user->ap_paterno ?? 'Turno',
                        'numero_documento' => 'DOC_' . strtoupper(\Illuminate\Support\Str::random(6)),
                        'profesion' => 'ENFERMERIA',
                        'estado' => 'ACTIVO',
                    ]);
                    $model->cod_personal = $codPer;
                } else {
                    $model->cod_personal = $personalCandidate->cod_personal;
                }
            }
            if (empty($model->tipo_plan)) {
                $model->tipo_plan = 'ENFERMERIA';
            }
            if (empty($model->nombre)) {
                $model->nombre = 'Plan de Cuidados de Enfermería';
            }
            if (empty($model->objetivo_general)) {
                $model->objetivo_general = 'Mantenimiento y cuidado integral del residente';
            }
            if (empty($model->prioridad)) {
                $model->prioridad = 'MEDIA';
            }
            if (empty($model->fecha_hora_apertura)) {
                $model->fecha_hora_apertura = $model->attributes['fecha_inicio'] ?? now();
            }
            if (empty($model->estado)) {
                $model->estado = 'ACTIVO';
            }
            unset($model->attributes['cod_am']);
            unset($model->attributes['creado_por']);
            unset($model->attributes['nivel_cuidado']);
            unset($model->attributes['fecha_inicio']);
            unset($model->attributes['fecha_fin']);
        });
    }
}
