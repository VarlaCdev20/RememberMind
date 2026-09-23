<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Adaptador del nombre usado por la interfaz histórica.
 * Persiste exclusivamente en la tabla V2 `residentes`.
 */
class AdultoMayor extends Residente
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    
        public function getCodAmAttribute(): string
    {
        return (string) ($this->attributes['cod_residente'] ?? $this->getKey());
    }

    public function setCodAmAttribute($value): void
    {
        $this->attributes['cod_residente'] = $value;
    }

public function estado(): HasOne
    {
        return $this->hasOne(self::class, 'cod_residente', 'cod_residente');
    }

    public function familiares(): BelongsToMany
    {
        return $this->belongsToMany(Contacto::class, 'residentes_contactos', 'cod_residente', 'cod_contacto')
            ->withPivot(['parentesco', 'responsable_principal', 'contacto_emergencia', 'autoriza_informacion', 'autoriza_salida', 'estado']);
    }

    public function observaciones(): HasMany
    {
        return $this->hasMany(NotaClinica::class, 'cod_residente', 'cod_residente');
    }

    public function actividades(): BelongsToMany
    {
        return $this->belongsToMany(Actividad::class, 'participantes_actividad', 'cod_residente', 'cod_actividad');
    }

    public function evaluacionesGeriatricas(): HasMany
    {
        return $this->hasMany(AplicacionInstrumento::class, 'cod_residente', 'cod_residente');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'cod_residente', 'cod_residente');
    }

    public function getEstadoAdultoAttribute(): string
    {
        return (string) $this->estado;
    }

    public function getEdadAttribute(): ?int
    {
        return $this->fecha_nacimiento?->age;
    }

    public function getPermanenciaAttribute(): ?string
    {
        return null;
    }

    public function getCiudadMunicipioAttribute(): ?string
    {
        return $this->direccion;
    }
}
