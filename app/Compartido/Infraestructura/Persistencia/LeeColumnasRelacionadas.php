<?php

namespace App\Compartido\Infraestructura\Persistencia;

/** Expone en la lectura el residente o versión que determina su vínculo obligatorio. */
trait LeeColumnasRelacionadas
{
    public function qualifyColumn($column)
    {
        $campo = ColumnasRelacionadas::CONTRATOS[$this->getTable()][0] ?? null;
        if ($campo !== null && ($column === $campo || $column === $this->getTable().'.'.$campo)) {
            return 'relacion_'.$this->getTable().'.'.$campo;
        }

        return parent::qualifyColumn($column);
    }

    public function initializeLeeColumnasRelacionadas(): void
    {
        if (isset(ColumnasRelacionadas::CONTRATOS[$this->getTable()])) {
            $this->appends[] = ColumnasRelacionadas::CONTRATOS[$this->getTable()][0];
        }
    }

    public static function bootLeeColumnasRelacionadas(): void
    {
        static::addGlobalScope('columnas_relacionadas', static function ($query): void {
            if (isset(ColumnasRelacionadas::CONTRATOS[$query->getModel()->getTable()])) {
                ColumnasRelacionadas::unir($query->getQuery(), $query->getModel()->getTable());
            }
        });
        static::saving(static function ($registro): void {
            if (! isset(ColumnasRelacionadas::CONTRATOS[$registro->getTable()])) {
                return;
            }
            ColumnasRelacionadas::preparar($registro->getConnection(), $registro->getTable(), $registro->getAttributes());
            $campo = ColumnasRelacionadas::CONTRATOS[$registro->getTable()][0];
            unset($registro[$campo]);
        });
    }

    protected function valorRelacionado(?string $valor): ?string
    {
        return $valor ?? (isset(ColumnasRelacionadas::CONTRATOS[$this->getTable()])
            ? ColumnasRelacionadas::valor($this->getConnection(), $this->getTable(), $this->getAttributes()) : null);
    }

    protected function guardarConVinculoProtegido(array $options): bool
    {
        return $this->getConnection()->transaction(function () use ($options): bool {
            ColumnasRelacionadas::bloquearEntrada($this->getConnection(), $this->getTable(), [$this->getAttributes(), $this->getRawOriginal()]);

            return parent::save($options);
        });
    }
}
