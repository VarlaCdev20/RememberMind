<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// O.R.I.O.N. 4.16.7; restricciones físicas D-137.
class DominioValoresExperto extends ModeloExperto
{
    protected $table = 'dominios_valores_expertos';

    protected $primaryKey = 'cod_dominio_valores';

    protected function casts(): array
    {
        return [
            'fecha_hora_creacion' => 'immutable_datetime',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(VersionModeloExperto::class, 'cod_version_modelo', 'cod_version_modelo');
    }

    public function usuarioCreacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_creacion', 'cod_usuario');
    }

    public function valoresSemanticos(): HasMany
    {
        return $this->hasMany(ValorSemantico::class, 'cod_dominio_valores', 'cod_dominio_valores');
    }

    public function variablesExpertas(): HasMany
    {
        return $this->hasMany(VariableExperta::class, 'cod_dominio_valores', 'cod_dominio_valores');
    }

    public function criteriosDominiosResultado(): HasMany
    {
        return $this->hasMany(CriterioDominioResultado::class, 'cod_dominio_valores', 'cod_dominio_valores');
    }
}
