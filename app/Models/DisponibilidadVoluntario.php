<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;

class DisponibilidadVoluntario extends Model
{
    use GeneraCodigo;
    protected $table = 'disponibilidad_voluntarios';
    protected $primaryKey = 'cod_hor_vol';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'HDV';
    protected $digitsCode = 4;

    public $timestamps = true;

    protected $fillable = [
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'estado',
        'obser',
        'observaciones',
        'cod_vol',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $disponibilidad) {
            $disponibilidad->obser ??= $disponibilidad->observaciones;
            $disponibilidad->observaciones ??= $disponibilidad->obser;
            $disponibilidad->estado ??= 'ACTIVO';
        });
    }


    /**
     * Relaciones
     */

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class, 'cod_vol', 'cod_vol');
    }

    public function getObservacionesAttribute(): ?string
    {
        return $this->attributes['observaciones'] ?? $this->attributes['obser'] ?? null;
    }

    public function setObservacionesAttribute(?string $value): void
    {
        $this->attributes['observaciones'] = $value;
        $this->attributes['obser'] = $value;
    }
}
