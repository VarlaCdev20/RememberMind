<?php

declare(strict_types=1);

namespace MotorExperto\dominio\Repositories\Conocimiento;

use MotorExperto\dominio\Entities\Conocimiento\ReglaExperta;
use DomainException;
use RuntimeException;

/** Recupera reglas por identidad y versión explícitas. */
interface ReglaExpertaRepositoryInterface
{
    /**
     * Recupera una regla de la versión exacta sin recurrir a otra versión.
     * @param string $id
     * @param string $versionConocimiento
     * @return ReglaExperta|null
     * @throws DomainException
     * @throws RuntimeException
     */
    public function obtener(string $id, string $versionConocimiento): ?ReglaExperta;

    /**
     * Recupera todas las reglas de una versión, incluidas las no habilitadas.
     * @param string $versionConocimiento
     * @return list<ReglaExperta>
     * @throws DomainException
     * @throws RuntimeException
     */
    public function porVersion(string $versionConocimiento): array;
}
