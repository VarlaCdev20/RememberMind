<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AplicacionInstrumento extends ModeloOperativo {
    protected $table='aplicaciones_instrumento'; protected $primaryKey='cod_aplicacion';
    protected function casts(): array{return ['fecha_hora'=>'datetime','puntaje_total'=>'decimal:2','puntaje_maximo'=>'decimal:2'];}
    public function instrumento(): BelongsTo{return $this->belongsTo(Instrumento::class,'cod_instrumento','cod_instrumento');}
    public function adulto(): BelongsTo{return $this->belongsTo(AdultoMayor::class,'cod_residente','cod_residente');}
    public function evaluador(): BelongsTo{return $this->belongsTo(Personal::class,'cod_personal','cod_personal');}
    public function respuestas(): HasMany{return $this->hasMany(RespuestaInstrumento::class,'cod_aplicacion','cod_aplicacion');}
    public function getCodEvalGerAttribute(): string{return (string) $this->cod_aplicacion;}
    public function getCodAmAttribute(): string{return (string) $this->cod_residente;}
    public function getFechaEvalAttribute(): mixed{return $this->fecha_hora;}
    public function getNivelAlertaAttribute(): ?string{return $this->clasificacion;}
    public function getCategoriaResultadoAttribute(): ?string{return $this->interpretacion;}
    public function getObservacionesAttribute(): ?string{return $this->observacion;}
    public function getHoraEvalAttribute(): ?string{return $this->fecha_hora?->format('H:i:s');}
    public function getNivelRiesgoAttribute(): ?string{return $this->clasificacion;}
    public function getRegistradorAttribute(): mixed{return $this->evaluador;}
    public function getCreatedAtColumn() { return 'fecha_hora'; }
}
