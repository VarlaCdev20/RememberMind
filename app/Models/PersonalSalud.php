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

    public $timestamps = false;

    protected $fillable = [
        'fecha_ing',
        'anios_exp',
        'matricula_prof',
        'estado_laboral',
        'observaciones',
        'cod_usu',
        'cod_esp',
        'institucion_formacion',
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

    public function asignacionesSalud()
    {
        return $this->hasMany(AsignacionSaludAdulto::class, 'cod_per_sal', 'cod_per_sal');
    }

    public function adultosMayoresAsignados()
    {
        return $this->belongsToMany(
            AdultoMayor::class,
            'asignaciones_salud_adulto',
            'cod_per_sal',
            'cod_am',
            'cod_per_sal',
            'cod_am'
        )->withPivot([
            'id',
            'tipo_asignacion',
            'fecha_inicio',
            'fecha_fin',
            'estado',
            'motivo',
            'asignado_por',
        ])->withTimestamps();
    }
}
