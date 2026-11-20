<?php

namespace App\Compartido\Infraestructura\Persistencia;

use Illuminate\Database\Eloquent\Builder;

/** Conserva las proyecciones explícitas antes de aplicar los JOIN de contexto. */
final class ConstructorConsultaRelacionada extends Builder
{
    public function lock($value = true)
    {
        if (is_bool($value) && $this->getQuery()->getConnection()->getDriverName() === 'pgsql') {
            // El sujeto relacionado es inmutable; el bloqueo corresponde al registro.
            $value = 'for '.($value ? 'update' : 'share').' of '.$this->getQuery()->getGrammar()->wrapTable($this->getModel()->getTable());
        }
        $this->getQuery()->lock($value);

        return $this;
    }

    public function lockForUpdate()
    {
        return $this->lock(true);
    }

    public function sharedLock()
    {
        return $this->lock(false);
    }

    public function get($columns = ['*'])
    {
        if ($columns !== ['*'] && $columns !== '*' && $this->getQuery()->columns === null) {
            return (clone $this)->select($columns)->get($columns);
        }

        return parent::get($columns);
    }

    public function pluck($column, $key = null)
    {
        if ($this->getQuery()->columns === null) {
            return (clone $this)->select($key === null ? [$column] : [$column, $key])->pluck($column, $key);
        }

        return parent::pluck($column, $key);
    }
}
