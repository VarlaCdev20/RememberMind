<?php

namespace App\Models;

use App\Traits\GeneraCodigo;
use Illuminate\Database\Eloquent\Model;

class LesionResidente extends Model
{
    use GeneraCodigo;

    protected $table = 'lesiones_residente';
    protected $primaryKey = 'cod_lesion';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'LSN';
    protected $digitsCode = 6;
    protected $guarded = ['cod_lesion'];
    protected $casts = ['fecha_deteccion' => 'datetime', 'fecha_cierre' => 'datetime'];

    public function adultoMayor() { return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am'); }
    public function incidente() { return $this->belongsTo(IncidenteResidente::class, 'cod_incidente', 'cod_incidente'); }
    public function seguimientos() { return $this->hasMany(SeguimientoLesion::class, 'cod_lesion', 'cod_lesion'); }
}
