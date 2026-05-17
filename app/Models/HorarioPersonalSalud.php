<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * HorarioPersonalSalud — Horarios del personal de salud.
 *
 * NOTA: Clase renombrada de HorariosPersonalSalud a HorarioPersonalSalud
 * para cumplir PSR-4 (nombre de clase debe coincidir con nombre de archivo).
 */
class HorarioPersonalSalud extends Model
{
    protected $table = 'horarios_personal_salud';
    protected $primaryKey = 'cod_hor_per_sal';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'turno',
        'estado',
        'observaciones',
        'cod_per_sal',
    ];

    /**
     * Relaciones
     */

    public function personalSalud()
    {
        return $this->belongsTo(PersonalSalud::class, 'cod_per_sal', 'cod_per_sal');
    }
}