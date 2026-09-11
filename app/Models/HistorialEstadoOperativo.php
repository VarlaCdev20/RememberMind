<?php

namespace App\Models;

use App\Traits\GeneraCodigo;
use Illuminate\Database\Eloquent\Model;

class HistorialEstadoOperativo extends Model
{
    use GeneraCodigo;

    protected $table = 'historial_estado_operativo';
    protected $primaryKey = 'cod_estado_operativo';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'EOP';
    protected $digitsCode = 6;
    protected $fillable = ['cod_am', 'estado_anterior', 'estado_nuevo', 'motivo', 'fecha_hora', 'registrado_por'];
    protected $casts = ['fecha_hora' => 'datetime'];

    public function adultoMayor() { return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am'); }
    public function registrador() { return $this->belongsTo(User::class, 'registrado_por', 'cod_usu'); }
}
