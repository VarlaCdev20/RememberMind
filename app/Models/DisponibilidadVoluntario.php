<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;

class DisponibilidadVoluntario extends Model
{
    use GeneraCodigo;
    protected $table = 'disponibilidad_voluntarios';
    protected $primaryKey = 'cod_hor_vol';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'DVO';
    protected $digitsCode = 3;

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
        static::creating(function ($DisponibilidadVoluntario) {
            if (!$DisponibilidadVoluntario->cod_hor_vol) {
                $ultimo = self::where('cod_hor_vol', 'like', 'HDV_%')
                    ->orderByDesc('cod_hor_vol')
                    ->value('cod_hor_vol');

                $numero = $ultimo
                    ? ((int) substr($ultimo, 3)) + 1
                    : 1;

                $DisponibilidadVoluntario->cod_hor_vol = 'HDV_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
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