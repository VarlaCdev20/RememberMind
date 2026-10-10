<?php

namespace App\Backend\Modulos\SistemaExperto\Conocimiento;

use Illuminate\Database\Eloquent\Builder;
use LogicException;

/** Impide que las mutaciones masivas omitan las guardas de instancia. */
class BuilderExperto extends Builder
{
    private function rechazar(): never
    {
        throw new LogicException('La historia experta no admite borrado ni sobrescritura masiva.');
    }

    public function update(array $values)
    {
        $this->rechazar();
    }

    public function upsert(array $values, $uniqueBy, $update = null)
    {
        $this->rechazar();
    }

    public function delete()
    {
        $this->rechazar();
    }

    public function forceDelete()
    {
        $this->rechazar();
    }

    public function touch($column = null)
    {
        $this->rechazar();
    }

    public function increment($column, $amount = 1, array $extra = [])
    {
        $this->rechazar();
    }

    public function decrement($column, $amount = 1, array $extra = [])
    {
        $this->rechazar();
    }

    public function incrementEach(array $columns, array $extra = [])
    {
        $this->rechazar();
    }

    public function decrementEach(array $columns, array $extra = [])
    {
        $this->rechazar();
    }
}
