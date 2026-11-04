<?php

declare(strict_types=1);

namespace MotorExperto\dominio\Repositories\Conocimiento;

use MotorExperto\dominio\Entities\Conocimiento\RedSemantica;
use DomainException;
use RuntimeException;

/** Recupera la red de una versión exacta; distingue una red vacía de una inexistente. */
interface RedSemanticaRepositoryInterface
{
    /**
     * Recupera la red de una versión exacta.
     * @param string $versionConocimiento
     * @return RedSemantica|null
     * @throws DomainException
     * @throws RuntimeException
     */
    public function porVersion(string $versionConocimiento): ?RedSemantica;
}
