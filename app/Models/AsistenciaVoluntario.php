<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaVoluntarios extends Model
{
    protected $table = 'asistencia_voluntarios';
    protected $primaryKey = 'cod_asis_vol';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'hora_entrada',
        'hora_salida',
        'estado',
        'actividad_realizada',
        'observaciones',
        'cod_vol'
    ];

    /**
     * Relaciones
     */

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class, 'cod_vol', 'cod_vol');
    }
}