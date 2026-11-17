<?php

namespace App\Models;

use App\Modulos\Institucion\Infraestructura\Persistencia\HistoriaLaboral;

abstract class RegistroHistoriaLaboral extends ModeloOperativo
{
    protected const CLASE_ACTO = '';

    protected $table = HistoriaLaboral::TABLA;

    protected $hidden = ['cod_historial_laboral', 'clase_acto'];

    protected static function booted(): void
    {
        static::addGlobalScope('clase_acto', fn ($query) => $query
            ->where($query->getModel()->qualifyColumn('clase_acto'), static::CLASE_ACTO));
        static::saving(function (self $registro): void {
            $registro->clase_acto = static::CLASE_ACTO;
        });
    }

    protected function getAttributesForInsert()
    {
        return ['clase_acto' => static::CLASE_ACTO] + parent::getAttributesForInsert();
    }

    public function entidadCodigo(): string
    {
        return array_search([static::CLASE_ACTO, $this->getKeyName()], HistoriaLaboral::TIPOS, true);
    }
}
