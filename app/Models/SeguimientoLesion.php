<?php

namespace App\Models;

use App\Traits\GeneraCodigo;
use Illuminate\Database\Eloquent\Model;

class SeguimientoLesion extends Model
{
    use GeneraCodigo;

    protected $table = 'seguimientos_lesion';
    protected $primaryKey = 'cod_seguimiento_lesion';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'SGL';
    protected $digitsCode = 7;
    protected $guarded = ['cod_seguimiento_lesion'];
    protected $casts = ['fecha_hora_evento' => 'datetime', 'es_medible' => 'boolean'];

    public function lesion() { return $this->belongsTo(LesionResidente::class, 'cod_lesion', 'cod_lesion'); }
    public function registrador() { return $this->belongsTo(User::class, 'registrado_por', 'cod_usu'); }
}
