<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\ValidationException;

class AplicacionInstrumento extends ModeloOperativo
{
    protected $table = 'aplicaciones_instrumento';

    protected $primaryKey = 'cod_aplicacion';

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'registrado_en' => 'immutable_datetime', 'puntaje_total' => 'decimal:2', 'puntaje_maximo' => 'decimal:2'];
    }

    public function setRegistradoEnAttribute(mixed $valor): void
    {
        $this->attributes['registrado_en'] = $valor === null ? null : CarbonImmutable::parse($valor)->format('Y-m-d H:i:s.u');
    }

    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_registro', 'cod_usuario');
    }

    public function documentoOriginal(): BelongsTo
    {
        return $this->belongsTo(DocumentoClinico::class, 'cod_documento_clinico', 'cod_documento_clinico');
    }

    public function aplicacionAnterior(): BelongsTo
    {
        return $this->belongsTo(self::class, 'cod_aplicacion_anterior', 'cod_aplicacion');
    }

    public function sucesora(): HasOne
    {
        return $this->hasOne(self::class, 'cod_aplicacion_anterior', 'cod_aplicacion');
    }

    protected static function booted(): void
    {
        static::updating(function (self $a): void {
            if (array_diff(array_keys($a->getDirty()), ['estado']) !== []) {
                throw ValidationException::withMessages(['aplicacion' => 'El contenido original se conserva; registre una rectificación enlazada.']);
            }
        });
    }

    public function instrumento(): BelongsTo
    {
        return $this->belongsTo(Instrumento::class, 'cod_instrumento', 'cod_instrumento');
    }

    public function adulto(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    public function evaluador(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(RespuestaInstrumento::class, 'cod_aplicacion', 'cod_aplicacion');
    }

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'cod_atencion', 'cod_atencion');
    }

    public function getCodEvalGerAttribute(): string
    {
        return (string) $this->cod_aplicacion;
    }

    public function getFechaEvalAttribute(): mixed
    {
        return $this->fecha_hora;
    }

    public function getNivelAlertaAttribute(): ?string
    {
        return $this->clasificacion;
    }

    public function getCategoriaResultadoAttribute(): ?string
    {
        return $this->interpretacion;
    }

    public function getObservacionesAttribute(): ?string
    {
        return $this->observacion;
    }

    public function getHoraEvalAttribute(): ?string
    {
        return $this->fecha_hora?->format('H:i:s');
    }

    public function getNivelRiesgoAttribute(): ?string
    {
        return $this->clasificacion;
    }

    public function getRegistradorAttribute(): mixed
    {
        return $this->evaluador;
    }

    public function getCreatedAtColumn()
    {
        return 'fecha_hora';
    }
}
