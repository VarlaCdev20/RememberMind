<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaGeriatrica extends Model
{
    protected $table = 'areas_geriatricas';
    protected $primaryKey = 'cod_area';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['cod_area', 'nombre', 'descripcion', 'estado'];

    public function instrumentos()
    {
        return $this->hasMany(InstrumentoGeriatrico::class, 'cod_area', 'cod_area');
    }
}
