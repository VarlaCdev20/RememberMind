<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ResultadoInferenciaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use DomainException;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\Repositories\Conocimiento\RedSemanticaRepositoryInterface;
use MotorExperto\dominio\orion\Repositories\Conocimiento\ReglaExpertaRepositoryInterface;
use MotorExperto\dominio\orion\Services\Inferencia\MotorInferenciaService;

/** Coordina conocimiento versionado y el motor puro. */
final readonly class EjecutarInferenciaUseCase
{
    /**
     * Recibe puertos de Domain y el motor puro; no ejecuta consultas al construir.
     * @param ReglaExpertaRepositoryInterface $reglas
     * @param RedSemanticaRepositoryInterface $red
     * @param MotorInferenciaService $motor
     */
    public function __construct(
        /** Reglas de la versión solicitada. */ private ReglaExpertaRepositoryInterface $reglas,
        /** Red de la versión solicitada. */ private RedSemanticaRepositoryInterface $red,
        /** Productor real de inferencia y trazas. */ private MotorInferenciaService $motor = new MotorInferenciaService,
    ) {}

    /**
     * Orquesta la recuperación y ejecución sin condiciones clínicas.
     * @param SolicitudInferenciaDTO $solicitud
     * @param MemoriaTrabajo $memoria
     * @param array $dominiosReglas
     * @param array $contratosRelaciones
     * @param array $habilitaciones
     * @param array $procedencias
     * @param array $contexto
     * @return ResultadoInferenciaDTO
     * @throws DomainException
     * @throws \RuntimeException
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
