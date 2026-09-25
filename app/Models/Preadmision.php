<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Preadmision extends ModeloOperativo
{
    protected $table = 'preadmisiones';

    protected $primaryKey = 'cod_preadmision';

    /**
     * Conserva el contrato `$preadmision->valoracion_enfermeria` utilizado por
     * Livewire mientras el almacenamiento real queda normalizado en una tabla 1:1.
     */
    protected ?array $valoracionEnfermeriaPendiente = null;

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_solicitud' => 'datetime',
            'fecha_revision' => 'datetime',
        ];
    }

    public function contacto(): BelongsTo
    {
        return $this->belongsTo(Contacto::class, 'cod_contacto', 'cod_contacto');
    }

    public function admision(): HasOne
    {
        return $this->hasOne(Admision::class, 'cod_preadmision', 'cod_preadmision');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'cod_preadmision', 'cod_preadmision');
    }

    public function valoracionEnfermeriaRegistro(): HasOne
    {
        return $this->hasOne(
            ValoracionEnfermeriaPreadmision::class,
            'cod_preadmision',
            'cod_preadmision',
        );
    }

    public function getValoracionEnfermeriaAttribute(): ?array
    {
        $registro = $this->relationLoaded('valoracionEnfermeriaRegistro')
            ? $this->getRelation('valoracionEnfermeriaRegistro')
            : $this->valoracionEnfermeriaRegistro()->first();

        if (! $registro) {
            return null;
        }

        $datos = Arr::except($registro->attributesToArray(), [
            'cod_valoracion_enfermeria',
            'cod_preadmision',
            'cod_usuario_registro',
        ]);
        $datos['registrado_por'] = $registro->cod_usuario_registro;
        $datos['fecha_hora'] = $registro->fecha_hora?->toIso8601String();
        $datos['orientacion'] = sprintf(
            'Persona: %s, Tiempo: %s, Espacio: %s',
            $registro->orientacion_persona,
            $registro->orientacion_tiempo,
            $registro->orientacion_espacio,
        );

        return $datos;
    }

    public function setValoracionEnfermeriaAttribute(mixed $valor): void
    {
        $this->valoracionEnfermeriaPendiente = is_array($valor) ? $valor : null;
    }

    protected static function booted(): void
    {
        static::saved(function (self $preadmision): void {
            if ($preadmision->valoracionEnfermeriaPendiente === null) {
                return;
            }

            $datos = $preadmision->valoracionEnfermeriaPendiente;
            $preadmision->valoracionEnfermeriaPendiente = null;

            $columnas = [
                'fecha_hora', 'estado_general', 'nivel_conciencia',
                'orientacion_persona', 'orientacion_tiempo', 'orientacion_espacio',
                'comunicacion', 'hay_dolor', 'intensidad_dolor', 'ubicacion_dolor',
                'movilidad', 'apoyo_movilidad', 'riesgo_caida', 'piel_estado',
                'hay_heridas', 'ubicacion_heridas', 'higiene_ingreso',
                'continencia_basica', 'alimentacion_aparente', 'pa_sistolica',
                'pa_diastolica', 'frecuencia_cardiaca', 'frecuencia_respiratoria',
                'temperatura', 'saturacion_oxigeno', 'peso', 'talla',
                'antecedentes_relevantes', 'medicacion_referida',
                'alergias_referidas', 'dependencia_funcional', 'riesgo_nutricional',
                'riesgo_cognitivo', 'tipo_evaluacion_cognitiva',
                'necesidad_apoyo_inmediato', 'prioridad_sugerida',
                'confirmacion_documentacion', 'comentarios_adicionales',
                'recomendacion_enfermeria',
            ];

            $valores = Arr::only($datos, $columnas);
            $valores['cod_usuario_registro'] = $datos['registrado_por'] ?? auth()->id();
            $valores['fecha_hora'] = $datos['fecha_hora'] ?? now();

            $registro = $preadmision->valoracionEnfermeriaRegistro()->first();
            if ($registro) {
                $registro->fill($valores)->save();
            } else {
                $preadmision->valoracionEnfermeriaRegistro()->create($valores + [
                    'cod_valoracion_enfermeria' => 'VEN_'.Str::upper(Str::random(12)),
                ]);
            }

            $preadmision->unsetRelation('valoracionEnfermeriaRegistro');
        });
    }

    public function getCodPreAttribute(): string
    {
        return $this->cod_preadmision;
    }

    public function getCiAttribute(): ?string
    {
        return $this->numero_documento;
    }

    public function getExpedicionCiAttribute(): ?string
    {
        return $this->expedicion_documento;
    }

    public function getFechaNacAttribute(): mixed
    {
        return $this->fecha_nacimiento;
    }

    public function getApPaternoAttribute(): ?string
    {
        return $this->apellido_paterno;
    }

    public function getApMaternoAttribute(): ?string
    {
        return $this->apellido_materno;
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    public function getCiudadMunicipioAttribute(): ?string
    {
        return null;
    }

    public function getZonaAttribute(): ?string
    {
        return null;
    }

    public function getCelularAttribute(): ?string
    {
        return $this->telefono;
    }

    public function getDepartamentoResidenciaAttribute(): ?string
    {
        return null;
    }

    public function getCalleAttribute(): ?string
    {
        return $this->direccion;
    }

    public function getProcedenciaIngresoAttribute(): ?string
    {
        return $this->procedencia;
    }

    public function getDocumentosInicialesCompletosAttribute(): bool
    {
        return (int) ($this->documentos_count ?? $this->documentos()->count()) > 0;
    }

    public function getFechaRechazoAttribute(): mixed
    {
        return $this->estado === 'RECHAZADA' ? $this->fecha_revision : null;
    }

    public function getFamiliarCompletoAttribute(): string
    {
        return trim(($this->contacto?->nombres ?? '').' '.($this->contacto?->apellido_paterno ?? '').' '.($this->contacto?->apellido_materno ?? ''));
    }

    public function getFamiliarParentescoAttribute(): string
    {
        return 'CONTACTO';
    }

    public function getFamiliarCelularAttribute(): ?string
    {
        return $this->contacto?->celular ?: $this->contacto?->telefono;
    }

    public function getFamiliarTelefonoAttribute(): ?string
    {
        return $this->contacto?->telefono;
    }

    public function getFamiliarCorreoAttribute(): ?string
    {
        return $this->contacto?->correo;
    }

    public function getFamiliarDireccionAttribute(): ?string
    {
        return $this->contacto?->direccion;
    }
}
