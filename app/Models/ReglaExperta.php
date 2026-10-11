<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// O.R.I.O.N. 4.16.17; restricciones físicas D-137.
class ReglaExperta extends ModeloExperto
{
    protected $table = 'reglas_expertas';

    protected $primaryKey = 'cod_regla_experta';

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

    public function condicionesReglaExperta(): HasMany
    {
        return $this->hasMany(CondicionReglaExperta::class, 'cod_regla_experta', 'cod_regla_experta');
    }

    public function consecuenciasReglaExperta(): HasMany
    {
        return $this->hasMany(ConsecuenciaReglaExperta::class, 'cod_regla_experta', 'cod_regla_experta');
    }

    public function evaluacionesReglas(): HasMany
    {
        return $this->hasMany(EvaluacionRegla::class, 'cod_regla_experta', 'cod_regla_experta');
    }
}
