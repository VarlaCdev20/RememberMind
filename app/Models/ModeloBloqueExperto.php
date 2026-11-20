<?php

declare(strict_types=1);

namespace App\Models;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos;
use Illuminate\Database\Eloquent\Builder;

/** Entidad lógica singular almacenada en su propietario. Mantiene relaciones y guardas de historia. */
abstract class ModeloBloqueExperto extends ModeloExperto
{
    protected const BLOQUE = '';

    public function entidadLogica(): string
    {
        return static::BLOQUE;
    }

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope('bloque_original', static function (Builder $q): void {
            $modelo = $q->getModel();
            $q->fromSub(BloquesExpertos::consulta(static::BLOQUE, $modelo->getConnectionName()), $modelo->getTable());
        });
    }

    /** Las lecturas sin scopes también deben conservar la entidad lógica y su contexto. */
    public function fresh($with = [])
    {
        if (! $this->exists) return null;
        return $this->setKeysForSelectQuery($this->consultaOriginal())
            ->useWritePdo()->with(is_string($with) ? func_get_args() : $with)->first();
    }

    protected function refreshUsingQuery(Builder $query)
    {
        $query->fromSub(BloquesExpertos::consulta(static::BLOQUE, $this->getConnectionName()), $this->getTable());
        return parent::refreshUsingQuery($query);
    }

    private function consultaOriginal(): Builder
    {
        return $this->newQueryWithoutScopes()->fromSub(BloquesExpertos::consulta(static::BLOQUE, $this->getConnectionName()), $this->getTable());
    }

    protected function performInsert(Builder $query)
    {
        if ($this->fireModelEvent('creating') === false) {
            return false;
        }
        BloquesExpertos::insertar(static::BLOQUE, $this->getAttributes(), $this->getConnectionName());
        $this->exists = true;
        $this->wasRecentlyCreated = true;
        $this->fireModelEvent('created', false);

        return true;
    }
}
