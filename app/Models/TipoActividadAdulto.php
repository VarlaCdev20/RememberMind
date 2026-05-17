<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoActividadAdulto extends Model
{
    protected $table = 'tipo_actividades_adulto';
    protected $primaryKey = 'cod_tipo_act';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'descripcion'
    ];


    /**
     * Relaciones
     */

    public function actividades()
    {
        return $this->hasMany(ActividadAdulto::class, 'cod_tipo_act', 'cod_tipo_act');
    }
}