<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\Repositories\Hechos;

use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use DateTimeImmutable;
use DomainException;
use RuntimeException;

/**
 * TÍTULO: EvidenciaRepositoryInterface.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Base de hechos.
 *
 * RESPONSABILIDAD:
 * Entregar una memoria original de evidencias y hechos hasta un corte explícito.
 *
 * ENTRADAS:
 * Evaluación, residente, versión de conocimiento y fecha de corte.
 *
 * SALIDAS:
 * Memoria inmutable que preserva hechos, evidencias y procedencia.
 *
 * RESTRICCIONES:
 * No interpreta resultados, no rellena ausencias ni selecciona otro sujeto.
 * La memoria vacía conserva el contexto solicitado y no prueba normalidad.
 *
 * TRAZABILIDAD:
 * Reglamento maestro, Fase C, R1/R3/R4.
 */
interface EvidenciaRepositoryInterface
{
    /**
     * Recupera una instantánea inclusiva de las fuentes originales.
     *
     * La respuesta conserva exactamente evaluación, sujeto, versión y corte.
     * Las declaraciones de procedencia/contexto se entregan como hechos
     * originales, sin acreditarlas automáticamente durante su recuperación.
     *
     * @param string $evaluacionId Evaluación propietaria.
     * @param string $residenteId Sujeto propietario.
     * @param string $versionConocimiento Versión exacta.
     * @param DateTimeImmutable $fechaCorte Última fecha admitida, inclusiva.
     * @return MemoriaTrabajo Instantánea validada, posiblemente vacía.
     * @throws DomainException Por identidad, contexto, duplicación o fecha incoherentes.
     * @throws RuntimeException Por fallo técnico; nunca se devuelve memoria vacía para ocultarlo.
     */
    public function paraEvaluacion(string $evaluacionId, string $residenteId, string $versionConocimiento, DateTimeImmutable $fechaCorte): MemoriaTrabajo;
}
