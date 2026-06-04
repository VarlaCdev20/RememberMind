<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocCalle extends Model
{
    protected $table = 'loc_calles';
    protected $fillable = ['zona_id', 'nombre', 'activo'];

    public function zona()
    {
        return $this->belongsTo(LocZona::class, 'zona_id');
    }
}
