<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaVoluntarios extends Model
{
    protected $table = 'asistencia_voluntarios';
    protected $primaryKey = 'cod_asis_vol';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'hora_entrada',
        'hora_salida',
        'estado',
        'actividad_realizada',
        'observaciones',
        'cod_vol'
    ];

    protected static function boot(): void
    {
        static::creating(function ($AsistenciaVoluntarios) {
            if (!$AsistenciaVoluntarios->cod_asig_vol) {
                $ultimo = self::where('cod_asig_vol', 'like', 'ASV_%')
                    ->orderByDesc('cod_asig_vol')
                    ->value('cod_asig_vol');

                $numero = $ultimo
                    ? ((int) substr($ultimo, 3)) + 1
                    : 1;

                $AsistenciaVoluntarios->cod_asig_vol = 'ASV_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Relaciones
     */

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class, 'cod_vol', 'cod_vol');
    }
}