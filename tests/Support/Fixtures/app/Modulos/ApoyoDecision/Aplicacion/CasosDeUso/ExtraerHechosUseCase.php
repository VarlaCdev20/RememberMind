<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\EvidenciasEntradaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use DomainException;
use MotorExperto\dominio\Repositories\Hechos\EvidenciaRepositoryInterface;
use RuntimeException;

/** Obtiene la instantánea del caso ficticio mediante el contrato de evidencias de Domain y la entrega sin modificar sus hechos ni su procedencia. */
final readonly class ExtraerHechosUseCase
{
    /**
     * Recibe la capacidad abstracta de recuperar hechos, sin elegir persistencia.
     * @param EvidenciaRepositoryInterface $evidencias
     */
    public function __construct(
        /** Contrato encargado de recuperar la instantánea original. */ private EvidenciaRepositoryInterface $evidencias,
    ) {}

    /**
     * Recupera una única instantánea y conserva íntegra la respuesta del repositorio.
     * @param SolicitudInferenciaDTO $solicitud
     * @return EvidenciasEntradaDTO
     * @throws DomainException
     * @throws RuntimeException
     */
    public function ejecutar(SolicitudInferenciaDTO $solicitud): EvidenciasEntradaDTO
    {
        $memoria = $this->evidencias->paraEvaluacion(
            $solicitud->evaluacionId,
            $solicitud->casoSinteticoId,
            $solicitud->versionConocimiento,
            $solicitud->fechaCorte,
        );

        // Describe una ausencia observable del transporte, sin asignar A/P/U,
        // evaluabilidad ni disponibilidad a una fuente que no fue informada.
        $datosFaltantes = $memoria->evidencias === []
            ? ['EVIDENCIAS_COGNITIVAS' => 'El repositorio no entregó evidencias cognitivas para la evaluación y fecha de corte solicitadas.']
            : [];

        return new EvidenciasEntradaDTO($solicitud, $memoria, $datosFaltantes);
    }
}
