<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocDepartamento extends Model
{
    protected $table = 'loc_departamentos';
    protected $fillable = ['nombre', 'activo'];

    public function municipios()
    {
        return $this->hasMany(LocMunicipio::class, 'departamento_id');
    }
}
