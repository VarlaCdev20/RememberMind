<?php

namespace App\Models;

use App\Traits\GeneraCodigo;
use Illuminate\Database\Eloquent\Model;

class DispositivoResidente extends Model
{
    use GeneraCodigo;

    protected $table = 'dispositivos_residente';
    protected $primaryKey = 'cod_dispositivo';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'DSP';
    protected $digitsCode = 6;
    protected $fillable = ['cod_am', 'tipo', 'ubicacion', 'fecha_colocacion', 'fecha_retiro', 'estado', 'indicacion', 'observacion', 'registrado_por'];
    protected $casts = ['fecha_colocacion' => 'datetime', 'fecha_retiro' => 'datetime'];

    public function adultoMayor() { return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am'); }
    public function registrador() { return $this->belongsTo(User::class, 'registrado_por', 'cod_usu'); }
}
