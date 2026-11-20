<?php

namespace App\Models;

use App\Compartido\Infraestructura\Persistencia\ConservaContextoResidente;
use App\Compartido\Infraestructura\Persistencia\ContextosResidente;
use App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo;

abstract class RegistroConsumo extends ModeloOperativo
{
    use ConservaContextoResidente;

    protected const TIPO = '';

    public function save(array $options = [])
    {
        $this->tipo_evento = static::TIPO;

        return ContextosResidente::guardar($this, fn () => parent::save($options));
    }

    protected const ENTIDAD = '';

    protected $table = RegistrosConsumo::TABLA;

    protected $primaryKey = RegistrosConsumo::CODIGO;

    protected static function booted(): void
    {
        static::addGlobalScope('tipo_consumo', fn ($query) => $query
            ->where($query->getModel()->qualifyColumn('tipo_evento'), static::TIPO));

        static::saving(function (self $registro): void {
            $registro->tipo_evento = static::TIPO;
        });
    }

    protected function getAttributesForInsert()
    {
        return ['tipo_evento' => static::TIPO] + parent::getAttributesForInsert();
    }

    public function entidadCodigo(): string
    {
        return static::ENTIDAD;
    }
}
