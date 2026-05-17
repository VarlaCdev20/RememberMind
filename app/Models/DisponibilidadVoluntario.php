<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisponibilidadVoluntarios extends Model
{
    protected $table = 'disponibilidad_voluntarios';
    protected $primaryKey = 'cod_hor_vol';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'observaciones',
        'cod_vol',
    ];

    protected static function boot(): void
    {
        static::creating(function ($DisponibilidadVoluntarios) {
            if (!$DisponibilidadVoluntarios->cod_hor_vol) {
                $ultimo = self::where('cod_hor_vol', 'like', 'HDV_%')
                    ->orderByDesc('cod_hor_vol')
                    ->value('cod_hor_vol');

                $numero = $ultimo
                    ? ((int) substr($ultimo, 3)) + 1
                    : 1;

                $DisponibilidadVoluntarios->cod_hor_vol = 'HDV_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
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