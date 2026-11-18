<?php

namespace App\Models;

use App\Compartido\Infraestructura\Persistencia\LeeColumnasRelacionadas;

abstract class ValoracionProfesional extends RegistroConsumo
{
    use LeeColumnasRelacionadas;

    protected $table = 'valoraciones_profesionales';

    protected $primaryKey = 'cod_valoracion_profesional';

    public function getCodResidenteAttribute(?string $valor): ?string
    {
        return $this->valorRelacionado($valor);
    }

    public function save(array $options = [])
    {
        if ($this->exists && ! $this->isDirty(['cod_atencion', 'cod_residente'])) {
            return parent::save($options);
        }

        return $this->getConnection()->transaction(function () use ($options): bool {
            $atenciones = array_filter([$this->getRawOriginal('cod_atencion'), $this->cod_atencion]);
            $this->getConnection()->table('atenciones')->whereIn('cod_atencion', $atenciones)
                ->orderBy('cod_atencion')->sharedLock()->get(['cod_atencion']);

            return parent::save($options);
        });
    }
}
