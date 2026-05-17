<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * HorarioPersonalAdmin — Horarios del personal administrativo.
 *
 * NOTA: Clase renombrada de HorariosPersonalAdmin a HorarioPersonalAdmin
 * para cumplir PSR-4 (nombre de clase debe coincidir con nombre de archivo).
 */
class HorarioPersonalAdmin extends Model
{
    protected $table = 'horarios_personal_admin';
    protected $primaryKey = 'cod_hor_per_admin';

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
        'cod_per_adm',
    ];

    /**
     * Relaciones
     */

    public function personalAdmin()
    {
        return $this->belongsTo(PersonalAdmin::class, 'cod_per_adm', 'cod_per_adm');
    }
}