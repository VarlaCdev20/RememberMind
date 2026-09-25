<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValoracionEnfermeriaPreadmision extends ModeloOperativo
{
    protected $table = 'valoraciones_enfermeria_preadmision';

    protected $primaryKey = 'cod_valoracion_enfermeria';

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'hay_dolor' => 'boolean',
            'intensidad_dolor' => 'integer',
            'hay_heridas' => 'boolean',
            'confirmacion_documentacion' => 'boolean',
            'pa_sistolica' => 'decimal:2',
            'pa_diastolica' => 'decimal:2',
            'frecuencia_cardiaca' => 'decimal:2',
            'frecuencia_respiratoria' => 'decimal:2',
            'temperatura' => 'decimal:2',
            'saturacion_oxigeno' => 'decimal:2',
            'peso' => 'decimal:2',
            'talla' => 'decimal:2',
        ];
    }

    public function preadmision(): BelongsTo
    {
        return $this->belongsTo(Preadmision::class, 'cod_preadmision', 'cod_preadmision');
    }

    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_registro', 'cod_usuario');
    }
}
