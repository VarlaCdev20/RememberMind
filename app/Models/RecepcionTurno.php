<?php

namespace App\Models;

use App\Traits\GeneraCodigo;
use Illuminate\Database\Eloquent\Model;

class RecepcionTurno extends Model
{
    use GeneraCodigo;

    protected $table = 'recepciones_turno';
    protected $primaryKey = 'cod_recepcion';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'RCT';
    protected $digitsCode = 6;
    protected $fillable = ['cod_turno', 'cod_usuario', 'fecha_hora_recepcion', 'observacion'];
    protected $casts = ['fecha_hora_recepcion' => 'datetime'];

    public function turno() { return $this->belongsTo(TurnoEnfermeria::class, 'cod_turno', 'cod_turno'); }
    public function usuario() { return $this->belongsTo(User::class, 'cod_usuario', 'cod_usu'); }
}
