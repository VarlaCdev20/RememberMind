<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voluntario extends Model
{
    protected $table = 'voluntarios';
    protected $primaryKey = 'cod_vol';

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cod_vol',
        'fecha_ing',
        'area_apoyo',
        'estado',
        'observaciones',
        'cod_usu',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->cod_vol) {
                $ultimo = self::where('cod_vol', 'like', 'VOL_%')
                    ->orderByDesc('cod_vol')
                    ->value('cod_vol');

                $numero = $ultimo
                    ? ((int) substr($ultimo, 4)) + 1
                    : 1;

                $model->cod_vol = 'VOL_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Relaciones
     */

    public function usuario()
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

    public function asignaciones()
    {
        return $this->hasMany(AsignacionVoluntario::class, 'cod_vol', 'cod_vol');
    }

    public function disponibilidades()
    {
        return $this->hasMany(DisponibilidadVoluntario::class, 'cod_vol', 'cod_vol');
    }

    public function asistencias()
    {
        return $this->hasMany(AsistenciaVoluntario::class, 'cod_vol', 'cod_vol');
    }

    public function adultosMayores()
    {
        return $this->belongsToMany(
            AdultoMayor::class,
            'asignacion_voluntarios',
            'cod_vol',
            'cod_am'
        )->withPivot('fecha_asig', 'fecha_fin', 'estado', 'obser');
    }
}