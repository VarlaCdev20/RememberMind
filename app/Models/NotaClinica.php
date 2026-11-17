<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaClinica extends RegistroConsumo
{
    protected const TIPO = 'NOTA';

    protected const ENTIDAD = 'notas_clinicas';

    protected $appends = ['cod_nota'];

    protected $hidden = ['cod_registro_textual', 'tipo_evento'];

    public function getKey()
    {
        return parent::getKey() ?? ($this->attributes['cod_nota'] ?? null);
    }

    public function getCodNotaAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodNotaAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    protected $table = 'registros_textuales_clinicos';

    protected $primaryKey = 'cod_registro_textual';

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'cod_atencion', 'cod_atencion');
    }

    public function anterior(): BelongsTo
    {
        return $this->belongsTo(self::class, 'cod_nota_anterior', 'cod_registro_textual');
    }

    public function getFechaAttribute(): mixed
    {
        return $this->fecha_hora?->toDateString();
    }

    public function getHoraAttribute(): mixed
    {
        return $this->fecha_hora?->format('H:i:s');
    }

    public function getNotaAttribute(): string
    {
        return (string) $this->contenido;
    }

    public function getTipoObsAttribute(): string
    {
        return (string) ($this->tipo_nota ?? 'GENERAL');
    }

    public function getNivelImportanciaAttribute(): string
    {
        return 'NORMAL';
    }

    public function getNivelRiesgoAttribute(): string
    {
        return 'NORMAL';
    }

    public function getCategoriaAttribute(): string
    {
        return (string) ($this->tipo_nota ?? 'GENERAL');
    }

    public function getDescripcionAttribute(): string
    {
        return (string) $this->contenido;
    }

    public function getCodObsAttribute(): string
    {
        return (string) $this->cod_nota;
    }
}
