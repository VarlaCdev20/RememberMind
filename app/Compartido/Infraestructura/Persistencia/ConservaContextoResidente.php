<?php

namespace App\Compartido\Infraestructura\Persistencia;

/** Capacidad común de registros cuyo residente procede de una atención o un vínculo directo. */
trait ConservaContextoResidente
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

    public function fresh($with = [])
    {
        $actual = parent::fresh($with);
        if ($actual !== null) {
            $actual->hidratarResidenteRelacionado();
        }

        return $actual;
    }

    public function refresh()
    {
        parent::refresh();
        $this->hidratarResidenteRelacionado();

        return $this;
    }

    private function hidratarResidenteRelacionado(): void
    {
        if ($this->exists && isset(ContextosResidente::CONTRATOS[$this->getTable()])) {
            $this->setRawAttributes($this->getAttributes() + ['cod_residente' => $this->cod_residente], true);
        }
    }

    protected function getArrayableAttributes()
    {
        return array_diff_key(parent::getArrayableAttributes(), ['cod_contexto_directo' => true]);
    }
}
