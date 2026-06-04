<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocMunicipio extends Model
{
    protected $table = 'loc_municipios';
    protected $fillable = ['departamento_id', 'nombre', 'activo'];

    public function departamento()
    {
        return $this->belongsTo(LocDepartamento::class, 'departamento_id');
    }

    public function zonas()
    {
        return $this->hasMany(LocZona::class, 'municipio_id');
    }
}
