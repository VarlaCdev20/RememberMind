<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Documento extends ModeloOperativo {
    protected $table='documentos'; protected $primaryKey='cod_documento';
    protected function casts(): array { return ['fecha_vencimiento'=>'date','fecha_validacion'=>'datetime']; }
    public function residente(): BelongsTo { return $this->belongsTo(Residente::class,'cod_residente','cod_residente'); }
    public function anterior(): BelongsTo { return $this->belongsTo(self::class,'cod_documento_anterior','cod_documento'); }
    public function getCreatedAtColumn(): string { return 'cod_documento'; }
    public function getCodDocAttribute(): string { return (string) $this->cod_documento; }
    public function getCodAmAttribute(): string { return (string) $this->cod_residente; }
    public function getObservacionesAttribute(): ?string { return $this->observacion; }
}
