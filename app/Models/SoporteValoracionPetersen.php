<?php

declare(strict_types=1);

namespace App\Models;

use LogicException;

/** Vínculo tipado y ordenado al soporte original; no contiene una respuesta DCL esperada. */
final class SoporteValoracionPetersen extends ModeloOperativo
{
    protected $table = 'soportes_valoracion_petersen';

    protected $primaryKey = 'cod_soporte_petersen';

    protected function casts(): array
    {
        return ['orden' => 'integer'];
    }

    protected static function booted(): void
    {
        self::updating(static function (): never {
            throw new LogicException('Los soportes originales se conservan con su valoración inmutable.');
        });
    }
}
