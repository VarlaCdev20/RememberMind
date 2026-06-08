<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;

class TipoAtencionAdulto extends Model
{
    use GeneraCodigo;
    protected $table = 'tipo_atenciones_adulto';
    protected $primaryKey = 'cod_tipo_aten';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'TAT';
    protected $digitsCode = 3;

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