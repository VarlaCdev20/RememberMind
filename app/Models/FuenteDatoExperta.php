<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// O.R.I.O.N. 4.16.5; restricciones físicas D-137.
class FuenteDatoExperta extends ModeloExperto
{
    protected $table = 'fuentes_datos_expertas';

    protected $primaryKey = 'cod_fuente_dato_experta';

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

    public function mapeosVariablesFuente(): HasMany
    {
        return $this->hasMany(MapeoVariableFuente::class, 'cod_fuente_dato_experta', 'cod_fuente_dato_experta');
    }
}
