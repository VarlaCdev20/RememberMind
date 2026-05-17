<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionVoluntario extends Model
{
    protected $table = 'asignacion_voluntarios';
    protected $primaryKey = 'cod_asig_vol';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'fecha_asig',
        'fecha_fin',
        'estado',
        'obser',
        'cod_am',
        'cod_vol'
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($AsignacionVoluntario) {
            if (!$AsignacionVoluntario->cod_asig_vol) {
                $ultimo = self::where('cod_asig_vol', 'like', 'ASV_%')
                    ->orderByDesc('cod_asig_vol')
                    ->value('cod_asig_vol');

                $numero = $ultimo
                    ? ((int) substr($ultimo, 3)) + 1
                    : 1;

                $AsignacionVoluntario->cod_asig_vol = 'ASV_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }


    /**
     * Relaciones
     */

    public function adultoMayor()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class, 'cod_vol', 'cod_vol');
    }
}