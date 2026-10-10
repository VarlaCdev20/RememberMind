<?php

namespace App\Backend\Modulos\SistemaExperto\DTO;

use DateTimeImmutable;
use DomainException;

final readonly class ContextoEjecucion
{
    public function __construct(public string $evaluacion, public string $residente, public string $version, public DateTimeImmutable $fechaCorte)
    {
        foreach ([$evaluacion, $residente, $version] as $id) {
            if ($id === '' || strlen($id) > 20) {
                throw new DomainException('Identificador de ejecución experto inválido.');
            }
        }
    }
}
