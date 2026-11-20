<?php

namespace App\Models;

use App\Compartido\Infraestructura\Persistencia\ConstructorConsultaRelacionada;
use App\Compartido\Infraestructura\Persistencia\ContextosResidente;
use App\Compartido\Infraestructura\Persistencia\LeeColumnasRelacionadas;
use App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo;

abstract class RegistroConsumo extends ModeloOperativo
{
    use LeeColumnasRelacionadas;

    public function newEloquentBuilder($query)
    {
        return isset(ContextosResidente::CONTRATOS[$this->getTable()])
            ? new ConstructorConsultaRelacionada($query)
            : parent::newEloquentBuilder($query);
    }

    public function getCodResidenteAttribute(?string $valor): ?string
    {
        return $this->valorRelacionado($valor);
    }

    public function save(array $options = [])
    {
        return ContextosResidente::guardar($this, fn () => parent::save($options));
    }

    protected const TIPO = '';

    public function getArrayableAttributes()
    {
        return array_diff_key(parent::getArrayableAttributes(), ['cod_contexto_directo' => true]);
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
