<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    protected $table = 'especialidades';
    protected $primaryKey = 'cod_esp';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Relaciones
     */

    public function personalSalud()
    {
        return $this->hasMany(PersonalSalud::class, 'cod_esp', 'cod_esp');
    }
}