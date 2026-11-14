<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValoracionFuncional extends RegistroConsumo
{
    protected const TIPO = 'FUNCIONAL';

    protected const ENTIDAD = 'valoraciones_funcionales';

    protected $table = 'valoraciones_profesionales';

    protected $primaryKey = 'cod_valoracion_profesional';

    protected $appends = ['cod_valoracion_funcional'];

    protected $hidden = ['cod_valoracion_profesional', 'tipo_evento', 'estado_animo', 'afecto', 'ansiedad', 'apatia', 'percepcion', 'conducta', 'comunicacion', 'interaccion_social', 'impresion_cognitiva', 'cod_medicion', 'estado_nutricional', 'apetito', 'deglucion', 'riesgo_desnutricion', 'necesidad_asistencia', 'requerimiento_hidrico', 'restricciones_alimentarias', 'cod_valoracion_psicologica_ref'];

    protected $fillable = ['cod_valoracion_funcional', 'cod_residente', 'cod_personal', 'cod_atencion', 'fecha_hora', 'marcha', 'equilibrio', 'traslado', 'fuerza_funcional', 'resistencia', 'alimentacion_autonoma', 'bano_autonomo', 'vestido_autonomo', 'higiene_autonoma', 'continencia', 'movilidad_autonoma', 'necesita_supervision', 'nivel_dependencia', 'conclusion', 'recomendacion', 'estado'];

    public function getCodValoracionFuncionalAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodValoracionFuncionalAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'necesita_supervision' => 'boolean'];
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function scopeVigente(Builder $query): Builder
    {
        return $query->whereIn('estado', ['ACTIVA', 'VIGENTE']);
    }

    public function scopeHistorica(Builder $query): Builder
    {
        return $query->whereNotIn('estado', ['ACTIVA', 'VIGENTE']);
    }

    public function getAdultoMayorAttribute()
    {
        return $this->residente;
    }

    public function getPreadmisionAttribute()
    {
        return null;
    }

    public function getFechaValoracionAttribute(): mixed
    {
        return $this->fecha_hora;
    }

    public function getHoraValoracionAttribute(): ?string
    {
        return $this->fecha_hora?->format('H:i:s');
    }

    public function getCodValEnfAttribute(): string
    {
        return (string) $this->cod_valoracion_funcional;
    }

    public function getCodValoracionAttribute(): string
    {
        return (string) $this->cod_valoracion_funcional;
    }

    public function getObservacionAttribute(): ?string
    {
        return $this->conclusion;
    }

    public function getCodValFuncAttribute(): string
    {
        return (string) $this->cod_valoracion_funcional;
    }

    public function getRiesgoCaidaAttribute(): string
    {
        if (preg_match('/Riesgo de caída:\s*(BAJO|MEDIO|MODERADO|ALTO)/iu', (string) $this->conclusion, $matches)) {
            return strtoupper($matches[1]) === 'MODERADO' ? 'MEDIO' : strtoupper($matches[1]);
        }

        return 'MEDIO';
    }

    public function getIndiceBarthelAttribute(): ?int
    {
        return preg_match('/Barthel\s+(\d+)\/100/iu', (string) $this->conclusion, $matches)
            ? (int) $matches[1]
            : null;
    }

    public function getUsaBastonAttribute(): bool { return str_contains((string) $this->equilibrio, 'BASTON'); }
    public function getUsaAndadorAttribute(): bool { return str_contains((string) $this->equilibrio, 'ANDADOR'); }
    public function getUsaSillaRuedasAttribute(): bool { return $this->traslado === 'SILLA_RUEDAS'; }
    public function getComeSoloAttribute(): bool { return $this->alimentacion_autonoma === 'INDEPENDIENTE'; }
    public function getSeBanaSoloAttribute(): bool { return $this->bano_autonomo === 'INDEPENDIENTE'; }
    public function getSeVisteSoloAttribute(): bool { return $this->vestido_autonomo === 'INDEPENDIENTE'; }
    public function getVaBanoSoloAttribute(): bool { return in_array($this->continencia, ['INDEPENDIENTE', 'CONTINENTE'], true); }
    public function getCaminaSoloAttribute(): bool { return $this->marcha === 'INDEPENDIENTE'; }
    public function getBajaVisionAttribute(): bool { return false; }
    public function getBajaAudicionAttribute(): bool { return false; }
    public function getDificultadHablarAttribute(): bool { return false; }
    public function getMolestiaLuzAttribute(): bool { return false; }
    public function getMolestiaRuidoAttribute(): bool { return false; }
    public function getSeAsustaFacilAttribute(): bool { return false; }
    public function getMotivoAnulacionAttribute(): ?string { return $this->estado === 'ANULADA' ? $this->conclusion : null; }
    public function getFechaAnulacionAttribute(): mixed { return $this->estado === 'ANULADA' ? $this->fecha_hora : null; }

    public function getEstadoGeneralAttribute(): string
    {
        return $this->nivel_dependencia ?? 'REGULAR';
    }

    public function getNivelConcienciaAttribute(): string
    {
        return $this->necesita_supervision ? 'SUPERVISIÓN REQUERIDA' : 'CONSCIENTE / AUTÓNOMO';
    }

    public function getOrientacionAttribute(): ?string
    {
        return $this->conclusion ?? 'ORIENTADO EN TIEMPO Y ESPACIO';
    }

    public function getComunicacionAttribute(): ?string
    {
        return 'ADECUADA';
    }

    public function getSignosVitalesInicialesAttribute(): ?string
    {
        return null;
    }
}
