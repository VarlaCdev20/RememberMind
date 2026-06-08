<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;

class TipoActividadAdulto extends Model
{
    use GeneraCodigo;
    protected $table = 'tipo_actividades_adulto';
    protected $primaryKey = 'cod_tipo_act';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'TAC';
    protected $digitsCode = 3;

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