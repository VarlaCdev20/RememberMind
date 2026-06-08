<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;

class AsignacionAdultoMayor extends Model
{
    use GeneraCodigo;
    protected $table = 'asignacion_adulto_mayor';
    protected $primaryKey = 'cod_asig_adulto';
    protected $keyType = 'string';
    protected $prefixCode = 'AAM';
    protected $digitsCode = 5;
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        'cod_asig_adulto',
        'cod_am',
        'cod_habitacion',
        'cod_cama',
        'fecha_asignacion',
        'hora_asignacion',
        'estado',
        'observaciones',
        'registrado_por',
    ];

    protected static function booted(): void
    {
        static::creating(function ($asignacion) {
            if (!$asignacion->cod_asig_adulto) {
                $ultimo = self::where('cod_asig_adulto', 'like', 'AAM_%')
                    ->orderByDesc('cod_asig_adulto')
                    ->value('cod_asig_adulto');

                $numero = $ultimo
                    ? ((int) substr($ultimo, 4)) + 1
                    : 1;

                $asignacion->cod_asig_adulto = 'AAM_' . str_pad($numero, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}
