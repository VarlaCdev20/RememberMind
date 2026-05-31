<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstrumentoGeriatrico extends Model
{
    protected $table = 'instrumentos_geriatricos';
    protected $primaryKey = 'cod_instrumento';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_instrumento', 'cod_area', 'nombre', 'siglas', 'tipo_resultado',
        'puntaje_maximo', 'punto_corte_normal', 'punto_corte_riesgo',
        'descripcion', 'estado'
    ];

    public function area()
    {
        return $this->belongsTo(AreaGeriatrica::class, 'cod_area', 'cod_area');
    }

    public function evaluaciones()
    {
        return $this->hasMany(EvaluacionGeriatrica::class, 'cod_instrumento', 'cod_instrumento');
    }
}
