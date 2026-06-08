<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;

/**
 * HorarioPersonalSalud — Horarios del personal de salud.
 *
 * NOTA: Clase renombrada de HorariosPersonalSalud a HorarioPersonalSalud
 * para cumplir PSR-4 (nombre de clase debe coincidir con nombre de archivo).
 */
class HorarioPersonalSalud extends Model
{
    use GeneraCodigo;
    protected $table = 'horarios_personal_salud';
    protected $primaryKey = 'cod_hor_per_sal';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'HSA';
    protected $digitsCode = 3;

    public $timestamps = false;

    protected $fillable = [
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'turno',
        'estado',
        'observaciones',
        'cod_per_sal',
        'cod_usu',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

}