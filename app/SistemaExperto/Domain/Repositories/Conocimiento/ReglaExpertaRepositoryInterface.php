<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\Repositories\Conocimiento;

use App\SistemaExperto\Domain\Entities\Conocimiento\ReglaExperta;
use DomainException;
use RuntimeException;

/**
 * TÍTULO: ReglaExpertaRepositoryInterface.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Base de conocimiento.
 *
 * RESPONSABILIDAD:
 * Proporcionar reglas originales por identidad y versión explícitas.
 *
 * ENTRADAS:
 * Identidad de regla y versión de conocimiento, nunca una versión implícita actual.
 *
 * SALIDAS:
 * Reglas de dominio inmutables o ausencia explícita de una regla.
 *
 * RESTRICCIONES:
 * No ejecuta condiciones, no activa reglas ni decide prioridades clínicas.
 * La implementación futura rechazará identidades inválidas y versiones mezcladas.
 *
 * TRAZABILIDAD:
 * Reglamento maestro, Fase C, R4/R10.
 */
interface ReglaExpertaRepositoryInterface
{
    /**
     * Recupera una regla de la versión exacta sin recurrir a otra versión.
     *
     * @param string $id Identidad de la regla.
     * @param string $versionConocimiento Versión propietaria exacta.
     * @return ReglaExperta|null Regla original o ausencia, nunca una regla fabricada.
     * @throws DomainException Por identidad inválida o regla incoherente con la consulta.
     * @throws RuntimeException Por fallo técnico de recuperación, sin éxito ficticio.
     */
    public function obtener(string $id, string $versionConocimiento): ?ReglaExperta;

    /**
     * Recupera todas las reglas de una versión, incluidas las no habilitadas.
     *
     * @param string $versionConocimiento Versión exacta.
     * @return list<ReglaExperta> Reglas únicas de esa versión, ordenadas por ID.
     * @throws DomainException Por versión inválida o respuesta con duplicados/otra versión.
     * @throws RuntimeException Por fallo técnico de recuperación.
     */
    public function porVersion(string $versionConocimiento): array;
}
