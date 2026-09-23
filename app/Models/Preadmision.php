<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Preadmision extends ModeloOperativo {
    protected $table='preadmisiones'; protected $primaryKey='cod_preadmision';
    protected function casts(): array { return ['fecha_nacimiento'=>'date','fecha_solicitud'=>'datetime','fecha_revision'=>'datetime']; }
    public function contacto(): BelongsTo { return $this->belongsTo(Contacto::class,'cod_contacto','cod_contacto'); }
    public function admision(): HasOne { return $this->hasOne(Admision::class,'cod_preadmision','cod_preadmision'); }
    public function documentos(): HasMany { return $this->hasMany(Documento::class,'cod_preadmision','cod_preadmision'); }

    public function getCodPreAttribute(): string { return $this->cod_preadmision; }
    public function getCiAttribute(): ?string { return $this->numero_documento; }
    public function getExpedicionCiAttribute(): ?string { return $this->expedicion_documento; }
    public function getFechaNacAttribute() { return $this->fecha_nacimiento; }
    public function getApPaternoAttribute(): ?string { return $this->apellido_paterno; }
    public function getApMaternoAttribute(): ?string { return $this->apellido_materno; }
    public function getNombreCompletoAttribute(): string { return trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}"); }
    public function getCiudadMunicipioAttribute(): ?string { return null; }
    public function getZonaAttribute(): ?string { return null; }
    public function getCelularAttribute(): ?string { return $this->telefono; }
    public function getDepartamentoResidenciaAttribute(): ?string { return null; }
    public function getCalleAttribute(): ?string { return $this->direccion; }
    public function getProcedenciaIngresoAttribute(): ?string { return $this->procedencia; }
    public function getCodAmGeneradoAttribute(): ?string { return $this->admision?->cod_residente; }
    public function getDocumentosInicialesCompletosAttribute(): bool { return (int) ($this->documentos_count ?? $this->documentos()->count()) > 0; }
    public function getFechaRechazoAttribute() { return $this->estado === 'RECHAZADA' ? $this->fecha_revision : null; }
    public function getFamiliarCompletoAttribute(): string { return trim(($this->contacto?->nombres ?? '').' '.($this->contacto?->apellido_paterno ?? '').' '.($this->contacto?->apellido_materno ?? '')); }
    public function getFamiliarParentescoAttribute(): string { return 'CONTACTO'; }
    public function getFamiliarCelularAttribute(): ?string { return $this->contacto?->celular ?: $this->contacto?->telefono; }
    public function getFamiliarCorreoAttribute(): ?string { return $this->contacto?->correo; }
    public function getFamiliarDireccionAttribute(): ?string { return $this->contacto?->direccion; }
}
