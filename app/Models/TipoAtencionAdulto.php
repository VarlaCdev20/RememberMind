<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoAtencionAdulto extends Model
{
    protected $table = 'tipo_atenciones_adulto';
    protected $primaryKey = 'cod_tipo_aten';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'descripcion',
    ];

    /**
     * Relaciones
     */

    public function atenciones()
    {
        return $this->hasMany(AtencionAdulto::class, 'cod_tipo_aten', 'cod_tipo_aten');
    }
}