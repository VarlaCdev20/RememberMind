<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Repositories\Conocimiento;

use MotorExperto\dominio\orion\Entities\Conocimiento\RedSemantica;
use DomainException;
use RuntimeException;

/**
 * TÍTULO: RedSemanticaRepositoryInterface.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Base de conocimiento.
 *
 * RESPONSABILIDAD:
 * Recuperar una instantánea explícita de la red semántica por versión.
 *
 * ENTRADAS:
 * Versión del conocimiento.
 *
 * SALIDAS:
 * Red inmutable o ausencia explícita.
 *
 * RESTRICCIONES:
 * No reconstruye conexiones inexistentes ni autoriza participación inferencial.
 * Una red vacía existente es distinta de una versión inexistente.
 *
 * TRAZABILIDAD:
 * Reglamento maestro, Fase C, R4/R10; grafo versionado D-123.
 */
interface RedSemanticaRepositoryInterface
{
    /**
     * Recupera la red de una versión exacta.
     *
     * @param string $versionConocimiento Versión solicitada.
     * @return RedSemantica|null Instantánea validada o ausencia de la versión.
     * @throws DomainException Por versión inválida o respuesta de otra versión.
     * @throws RuntimeException Por fallo técnico de recuperación.
     */
    public function porVersion(string $versionConocimiento): ?RedSemantica;
}
