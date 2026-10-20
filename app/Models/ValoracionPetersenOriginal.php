<?php

declare(strict_types=1);

namespace App\Models;

use App\Modulos\ApoyoDecision\Presentacion\Policies\ValoracionPetersenPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LogicException;
use MotorExperto\dominio\petersen\ValoracionOriginalPetersen;

/** Juicio clínico original inmutable; corregir crea otra fila y nunca modifica el resultado del motor. */
#[UsePolicy(ValoracionPetersenPolicy::class)]
final class ValoracionPetersenOriginal extends ModeloOperativo
{
    protected $table = 'valoraciones_petersen_originales';

    protected $primaryKey = 'cod_valoracion_petersen';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected function casts(): array
    {
        return ['fecha_hora' => 'immutable_datetime', 'registrado_en' => 'immutable_datetime']
            + array_fill_keys(array_merge(...array_values(ValoracionOriginalPetersen::COMPROBACIONES)), 'boolean');
    }

    public function soportes(): HasMany
    {
        return $this->hasMany(SoporteValoracionPetersen::class, 'cod_valoracion_petersen', 'cod_valoracion_petersen')->orderBy('orden');
    }

    public function sucesora(): HasOne
    {
        return $this->hasOne(self::class, 'cod_valoracion_anterior', 'cod_valoracion_petersen');
    }

    protected static function booted(): void
    {
        self::updating(static function (): never {
            throw new LogicException('La valoración original es inmutable: registrar una rectificación.');
        });
    }
}
