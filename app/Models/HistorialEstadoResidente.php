<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class HistorialEstadoResidente extends ModeloOperativo {
    protected $table='historial_estados_residente'; protected $primaryKey='cod_historial_estado';
    protected function casts(): array { return ['fecha_hora'=>'datetime']; }
    public function residente(): BelongsTo { return $this->belongsTo(Residente::class,'cod_residente','cod_residente'); }
}
