<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Repositories\Evaluaciones;

use MotorExperto\dominio\orion\Entities\Cognicion\VectorCognitivo;
use MotorExperto\dominio\orion\Entities\Dcl\ResultadoClasificacionDcl;
use DateTimeImmutable;
use DomainException;
use RuntimeException;

/**
 * TÍTULO: EvaluacionExpertaRepositoryInterface.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Resultados y trazabilidad.
 *
 * RESPONSABILIDAD:
 * Conservar evaluaciones como perfiles originales y clasificaciones experimentales.
 *
 * ENTRADAS:
 * Vectores o resultados de dominio y una identidad temporal explícita al consultar.
 *
 * SALIDAS:
 * Perfil cognitivo o clasificación original, con memoria, soportes y versiones.
 *
 * RESTRICCIONES:
 * El vector existente representa la evaluación sin crear un agregado paralelo.
 * Guardar preserva instantáneas: reintentos idénticos son idempotentes;
 * contenido distinto bajo la misma identidad se rechaza, nunca se sobrescribe.
 * No diagnostica, no evalúa reglas ni decide qué resultado corresponde emitir.
 *
 * TRAZABILIDAD:
 * Reglamento maestro, Fase C, R1/R4/R6/R9; entidades de la Fase B.
 */
interface EvaluacionExpertaRepositoryInterface
{
    /**
     * Conserva un perfil y toda su memoria original como una instantánea.
     *
     * @param VectorCognitivo $evaluacion Perfil completo, con evaluación, sujeto, versión y corte.
     * @return void
     * @throws DomainException Por intento de sustituir una instantánea existente.
     * @throws RuntimeException Por fallo técnico, sin confirmación ficticia.
     */
    public function guardar(VectorCognitivo $evaluacion): void;

    /**
     * Recupera un perfil de la identidad temporal exacta.
     *
     * @param string $evaluacionId Evaluación propietaria.
     * @param string $residenteId Sujeto propietario.
     * @param string $versionConocimiento Versión del conocimiento.
     * @param DateTimeImmutable $fechaCorte Corte original de la instantánea.
     * @return VectorCognitivo|null Perfil original o ausencia explícita.
     * @throws DomainException Por identidad inválida o respuesta de contexto incompatible.
     * @throws RuntimeException Por fallo técnico de recuperación.
     */
    public function obtener(string $evaluacionId, string $residenteId, string $versionConocimiento, DateTimeImmutable $fechaCorte): ?VectorCognitivo;

    /**
     * Conserva un resultado experimental junto con su perfil y trazabilidad.
     *
     * @param ResultadoClasificacionDcl $resultado Candidato o no conclusión original.
     * @return void
     * @throws DomainException Por sustitución de identidad o perfil incompatible con la evaluación conservada.
     * @throws RuntimeException Por fallo técnico de conservación.
     */
    public function guardarClasificacion(ResultadoClasificacionDcl $resultado): void;

    /**
     * Recupera una clasificación experimental de identidad y contrato exactos.
     *
     * @param string $evaluacionId Evaluación propietaria.
     * @param string $residenteId Sujeto propietario.
     * @param string $versionConocimiento Versión del conocimiento.
     * @param DateTimeImmutable $fechaCorte Corte original.
     * @param string $versionContrato Versión experimental DCL original.
     * @return ResultadoClasificacionDcl|null Resultado original o ausencia, nunca normalidad.
     * @throws DomainException Por identidad inválida o resultado de otro contexto/contrato.
     * @throws RuntimeException Por fallo técnico de recuperación.
     */
    public function obtenerClasificacion(string $evaluacionId, string $residenteId, string $versionConocimiento, DateTimeImmutable $fechaCorte, string $versionContrato): ?ResultadoClasificacionDcl;
}
