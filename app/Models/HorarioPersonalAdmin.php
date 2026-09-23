<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;

/**
 * HorarioPersonalAdmin — Horarios del personal administrativo.
 *
 * NOTA: Clase renombrada de HorariosPersonalAdmin a HorarioPersonalAdmin
 * para cumplir PSR-4 (nombre de clase debe coincidir con nombre de archivo).
 */
class HorarioPersonalAdmin extends Model
{
    use GeneraCodigo;
    protected $table = 'horarios_personal_admin';
    protected $primaryKey = 'cod_hor_per_admin';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'HAD';
    protected $digitsCode = 3;

    public $timestamps = true;

    protected $fillable = [
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'turno',
        'estado',
        'observaciones',
        'cod_per_adm',
        'cod_usu',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

    protected static function booted(): void
    {
        static::saving(function (self $horario) {
            $horario->cod_usu ??= $horario->cod_per_adm;
            $horario->cod_per_adm ??= $horario->cod_usu;
            $horario->estado ??= 'ACTIVO';
        });
    }

}
