<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PersonalSalud extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Personal Salud');
    }
    protected $table = 'personal_salud';
    protected $primaryKey = 'cod_per_sal';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'fecha_ing',
        'anios_exp',
        'matricula_prof',
        'estado_laboral',
        'observaciones',
        'cod_usu',
        'cod_esp',
        'institucion_formacion',
        // ── Fase 6 ──────────────────────────────────────────────────────────
        'tipo_personal_salud',  // MEDICO, ENFERMERO, PSICOLOGO, FISIOTERAPEUTA, OTRO
        'subtipo_enfermeria',   // GENERAL_ADMISION, ESPECIALIZADO_TURNO
    ];

    protected $casts = [
        'fecha_ing' => 'date',
    ];

    /**
     * Relaciones
     */

    public function usuario()
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'cod_esp', 'cod_esp');
    }

    public function horarios()
    {
        return $this->hasMany(HorarioPersonalSalud::class, 'cod_per_sal', 'cod_per_sal');
    }
    public function evaluacionesCognitivas()
{
    return $this->hasMany(EvaluacionCognitiva::class, 'cod_per_sal', 'cod_per_sal');
}
}
