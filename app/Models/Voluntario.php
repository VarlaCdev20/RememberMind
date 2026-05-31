<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voluntario extends Model
{
    protected $table = 'voluntarios';
    protected $primaryKey = 'cod_vol';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'fecha_ing',
        'area_apoyo',
        'estado',
        'observaciones',
        'cod_usu',
    ];

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