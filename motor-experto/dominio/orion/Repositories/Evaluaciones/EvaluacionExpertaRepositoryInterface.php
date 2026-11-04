<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Repositories\Evaluaciones;

use MotorExperto\dominio\orion\Entities\Cognicion\VectorCognitivo;
use MotorExperto\dominio\orion\Entities\Dcl\ResultadoClasificacionDcl;
use DateTimeImmutable;
use DomainException;
use RuntimeException;

/** Conserva y recupera evaluaciones originales; rechaza sobrescrituras con contenido distinto. */
interface EvaluacionExpertaRepositoryInterface
{
    /**
     * Conserva un perfil y toda su memoria original como una instantánea.
     * @param VectorCognitivo $evaluacion
     * @return void
     * @throws DomainException
     * @throws RuntimeException
     */
    public function guardar(VectorCognitivo $evaluacion): void;

    /**
     * Recupera un perfil de la identidad temporal exacta.
     * @param string $evaluacionId
     * @param string $residenteId
     * @param string $versionConocimiento
     * @param DateTimeImmutable $fechaCorte
     * @return VectorCognitivo|null
     * @throws DomainException
     * @throws RuntimeException
     */
    public function obtener(string $evaluacionId, string $residenteId, string $versionConocimiento, DateTimeImmutable $fechaCorte): ?VectorCognitivo;

    /**
     * Conserva un resultado experimental junto con su perfil y trazabilidad.
     * @param ResultadoClasificacionDcl $resultado
     * @return void
     * @throws DomainException
     * @throws RuntimeException
     */
    public function guardarClasificacion(ResultadoClasificacionDcl $resultado): void;

    /**
     * Recupera una clasificación experimental de identidad y contrato exactos.
     * @param string $evaluacionId
     * @param string $residenteId
     * @param string $versionConocimiento
     * @param DateTimeImmutable $fechaCorte
     * @param string $versionContrato
     * @return ResultadoClasificacionDcl|null
     * @throws DomainException
     * @throws RuntimeException
     */
    public function obtenerClasificacion(string $evaluacionId, string $residenteId, string $versionConocimiento, DateTimeImmutable $fechaCorte, string $versionContrato): ?ResultadoClasificacionDcl;
}
