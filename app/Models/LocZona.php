<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocZona extends Model
{
    protected $table = 'loc_zonas';
    protected $fillable = ['municipio_id', 'nombre', 'activo'];

    public function municipio()
    {
        return $this->belongsTo(LocMunicipio::class, 'municipio_id');
    }

    public function calles()
    {
        return $this->hasMany(LocCalle::class, 'zona_id');
    }
}
