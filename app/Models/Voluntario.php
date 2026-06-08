<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;

class Voluntario extends Model
{
    use GeneraCodigo;
    protected $table = 'voluntarios';
    protected $primaryKey = 'cod_vol';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'VOL';
    protected $digitsCode = 3;

    public $timestamps = false;

    protected $fillable = [
        'fecha_ing',
        'estado',
        'observaciones',
        'cod_usu',
        'disponibilidad_inicial',
        'area_apoyo_preferente',
    ];

    public function getAreaApoyoAttribute(): ?string
    {
        return $this->area_apoyo_preferente ?? null;
    }

    public function setAreaApoyoAttribute(?string $value): void
    {
        $this->attributes['area_apoyo_preferente'] = $value;
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
