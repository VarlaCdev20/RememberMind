<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEvaluacionCognitiva extends Model
{
    protected $table = 'tipo_evaluacion_cognitiva';
    protected $primaryKey = 'cod_tipo_eval';

    protected $fillable = [
        'nombre',
        'descripcion',
        'puntaje_maximo',
        'punto_corte_normal',
        'punto_corte_riesgo',
        'estado',
    ];

    public function evaluaciones()
    {
        return $this->hasMany(EvaluacionCognitiva::class, 'cod_tipo_eval', 'cod_tipo_eval');
    }
}