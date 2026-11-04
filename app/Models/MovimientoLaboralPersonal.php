<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoLaboralPersonal extends ModeloOperativo
{
    protected $table = 'movimientos_laborales_personal';

    protected $primaryKey = 'cod_movimiento_laboral';

    protected function casts(): array
    {
        return ['fecha_hora_registro' => 'datetime', 'fecha_hora_efecto' => 'datetime'];
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_registro', 'cod_usuario');
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Un movimiento laboral conserva la transición original.'));
    }
}
