<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionVoluntario extends Model
{
    protected $table = 'asignacion_voluntarios';
    protected $primaryKey = 'cod_asig_vol';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'fecha_asig',
        'fecha_fin',
        'estado',
        'obser',
        'cod_am',
        'cod_vol'
    ];

    /**
     * Relaciones
     */

    public function adultoMayor()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class, 'cod_vol', 'cod_vol');
    }
}