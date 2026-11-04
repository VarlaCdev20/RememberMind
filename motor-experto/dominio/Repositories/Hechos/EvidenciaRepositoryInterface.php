<?php

declare(strict_types=1);

namespace MotorExperto\dominio\Repositories\Hechos;

use MotorExperto\dominio\Entities\Hechos\MemoriaTrabajo;
use DateTimeImmutable;
use DomainException;
use RuntimeException;

/** Recupera fuentes del mismo caso, versión y corte; no completa datos ausentes. */
interface EvidenciaRepositoryInterface
{
    /**
     * Recupera una instantánea inclusiva de las fuentes originales.
     * @param string $evaluacionId
     * @param string $residenteId
     * @param string $versionConocimiento
     * @param DateTimeImmutable $fechaCorte
     * @return MemoriaTrabajo
     * @throws DomainException
     * @throws RuntimeException
     */
    public function paraEvaluacion(string $evaluacionId, string $residenteId, string $versionConocimiento, DateTimeImmutable $fechaCorte): MemoriaTrabajo;
}
