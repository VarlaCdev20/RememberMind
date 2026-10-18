<?php

declare(strict_types=1);

namespace App\SistemaExperto\Application\UseCases;

use App\SistemaExperto\Application\DTOs\ResultadoInferenciaDTO;
use App\SistemaExperto\Application\DTOs\SolicitudInferenciaDTO;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use App\SistemaExperto\Domain\Repositories\Conocimiento\RedSemanticaRepositoryInterface;
use App\SistemaExperto\Domain\Repositories\Conocimiento\ReglaExpertaRepositoryInterface;
use App\SistemaExperto\Domain\Services\Inferencia\MotorInferenciaService;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Application (CU-03).
 * TÍTULO: Ejecución del motor de inferencia.
 * CAPA: Application - Casos de Uso.
 * ¿PARA QUÉ SIRVE? Coordina conocimiento versionado y el motor puro.
 * ¿QUÉ RECIBE? Solicitud, memoria y contratos explícitos de participación/revisión.
 * ¿QUÉ HACE? Recupera reglas y red exactas, invoca Domain y transporta su respuesta.
 * ¿QUÉ DEVUELVE? ResultadoInferenciaDTO con perfil, trazas y exclusiones originales.
 * ¿QUÉ NO DEBE HACER? No interpreta pruebas, modifica compuertas ni inventa A/P.
 * RELACIÓN CON O.R.I.O.N.: CU-03 y R1–R10; ejecución exclusivamente experimental.
 */
final readonly class EjecutarInferenciaUseCase
{
    /**
     * Recibe puertos de Domain y el motor puro; no ejecuta consultas al construir.
     *
     * @param  ReglaExpertaRepositoryInterface  $reglas  Puerto de conocimiento versionado.
     * @param  RedSemanticaRepositoryInterface  $red  Puerto de la red original.
     * @param  MotorInferenciaService  $motor  Colaborador puro, sin dependencias Laravel.
     *                                         No produce resultados ni excepciones durante la construcción.
     */
    public function __construct(
        /** Reglas de la versión solicitada. */ private ReglaExpertaRepositoryInterface $reglas,
        /** Red de la versión solicitada. */ private RedSemanticaRepositoryInterface $red,
        /** Productor real de inferencia y trazas. */ private MotorInferenciaService $motor = new MotorInferenciaService,
    ) {}

    /**
     * Orquesta la recuperación y ejecución sin condiciones clínicas.
     *
     * @param  SolicitudInferenciaDTO  $solicitud  Petición original.
     * @param  MemoriaTrabajo  $memoria  Instantánea preparada por CU-02.
     * @param  array  $dominiosReglas  Adscripciones explícitas por regla.
     * @param  array  $contratosRelaciones  Contratos semánticos de Domain.
     * @param  array  $habilitaciones  Habilitaciones de aristas.
     * @param  array  $procedencias  Hechos originales de revisión de procedencia.
     * @param  array  $contexto  Hechos originales de revisión contextual.
     * @return ResultadoInferenciaDTO Salida original del motor.
     *
     * @throws DomainException Por contexto o contratos incompatibles.
     * @throws \RuntimeException Por fallos de recuperación; nunca se sustituyen por listas vacías.
     */
    public function ejecutar(SolicitudInferenciaDTO $solicitud, MemoriaTrabajo $memoria, array $dominiosReglas,
        array $contratosRelaciones = [], array $habilitaciones = [], array $procedencias = [], array $contexto = []): ResultadoInferenciaDTO
    {
        $solicitud->exigirContexto($memoria);
        $reglas = $this->reglas->porVersion($solicitud->versionConocimiento);
        $red = $this->red->porVersion($solicitud->versionConocimiento);
        if ($red === null) {
            throw new DomainException('No existe una red semántica para la versión solicitada.');
        }
        $salida = $this->motor->inferir($memoria, $reglas, $red, $dominiosReglas,
            $contratosRelaciones, $habilitaciones, $procedencias, $contexto);

        return new ResultadoInferenciaDTO($solicitud, $salida['perfil'], $salida['versionMotor'],
            $salida['reglasPorDominio'], $salida['trazas'], $salida['advertencias'],
            $salida['relacionesUtilizadas'], $salida['exclusiones'], $salida['evaluacionesReglas']);
    }
}
