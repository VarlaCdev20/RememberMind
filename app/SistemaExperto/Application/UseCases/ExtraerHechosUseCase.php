<?php

declare(strict_types=1);

namespace App\SistemaExperto\Application\UseCases;

use App\SistemaExperto\Application\DTOs\EvidenciasEntradaDTO;
use App\SistemaExperto\Application\DTOs\SolicitudInferenciaDTO;
use App\SistemaExperto\Domain\Repositories\Hechos\EvidenciaRepositoryInterface;
use DomainException;
use RuntimeException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Application (CU-01, adquisición de hechos).
 *
 * TÍTULO:
 * Extracción de hechos para una evaluación.
 *
 * CAPA:
 * Application - Casos de Uso.
 *
 * ¿PARA QUÉ SIRVE?
 * Obtiene la instantánea del caso ficticio mediante el contrato de evidencias
 * de Domain y la entrega sin modificar sus hechos ni su procedencia.
 *
 * ¿QUÉ RECIBE?
 * Una SolicitudInferenciaDTO validada y un repositorio inyectado por constructor.
 *
 * ¿QUÉ HACE?
 * 1. Solicita la evaluación, caso sintético, versión y corte exactos.
 * 2. Entrega la memoria original al DTO, que contrasta el contexto recibido.
 * 3. Informa si la colección de evidencias cognitivas está vacía.
 *
 * ¿QUÉ DEVUELVE?
 * Un EvidenciasEntradaDTO con hechos, evidencias y referencias originales.
 *
 * ¿QUÉ NO DEBE HACER?
 * No consulta tablas, infiere normalidad, interpreta instrumentos ni oculta
 * fallos técnicos devolviendo una colección vacía.
 *
 * RELACIÓN CON O.R.I.O.N.:
 * Implementa CU-01 y utiliza EvidenciaRepositoryInterface::paraEvaluacion.
 * Ese contrato no informa disponibilidad de fuentes: el mapa correspondiente
 * permanece sin declaraciones, sin afirmar que las fuentes estén disponibles.
 */
final readonly class ExtraerHechosUseCase
{
    /**
     * Recibe la capacidad abstracta de recuperar hechos, sin elegir persistencia.
     *
     * @param  EvidenciaRepositoryInterface  $evidencias  Repositorio de Domain.
     *
     * No ejecuta consultas ni se esperan excepciones durante la construcción.
     */
    public function __construct(
        /** Contrato encargado de recuperar la instantánea original. */ private EvidenciaRepositoryInterface $evidencias,
    ) {}

    /**
     * Recupera una única instantánea y conserva íntegra la respuesta del repositorio.
     *
     * @param  SolicitudInferenciaDTO  $solicitud  Petición exclusivamente sintética.
     * @return EvidenciasEntradaDTO Respuesta con contexto y procedencia conservados.
     *
     * @throws DomainException Si la respuesta corresponde a otro contexto o corte.
     * @throws RuntimeException Si el repositorio falla; el error se propaga sin ocultarlo.
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
