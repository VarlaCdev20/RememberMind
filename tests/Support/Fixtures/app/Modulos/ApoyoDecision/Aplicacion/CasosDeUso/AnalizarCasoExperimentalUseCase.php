<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\InformeExpertoDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use DomainException;
use MotorExperto\dominio\ValueObjects\Dcl\EstadoCriterioDclEnum;
use MotorExperto\dominio\ValueObjects\Funcionalidad\EstadoDiferencialEnum;

/** Ejecuta los cinco casos individuales sobre la misma petición. */
final readonly class AnalizarCasoExperimentalUseCase
{
    /**
     * Conecta exactamente los cinco casos; no ejecuta trabajo durante la construcción.
     * @param ExtraerHechosUseCase $extraer
     * @param PrepararMemoriaTrabajoUseCase $preparar
     * @param EjecutarInferenciaUseCase $inferir
     * @param ClasificarDclUseCase $clasificar
     * @param GenerarInformeUseCase $informar
     */
    public function __construct(
        /** CU-01: recuperación por puerto de Domain. */ private ExtraerHechosUseCase $extraer,
        /** CU-02: memoria original preparada. */ private PrepararMemoriaTrabajoUseCase $preparar,
        /** CU-03: ejecución pura con conocimiento versionado. */ private EjecutarInferenciaUseCase $inferir,
        /** CU-04: patrón candidato o abstención. */ private ClasificarDclUseCase $clasificar,
        /** CU-05: explicación producida por Domain. */ private GenerarInformeUseCase $informar,
    ) {}

    /**
     * Propaga cualquier fallo y no ejecuta etapas posteriores después de un error.
     * @param SolicitudInferenciaDTO $solicitud
     * @param array{criterios: array, soportes: array, justificaciones: array, diferencial: EstadoDiferencialEnum, independenciaRelativa: EstadoCriterioDclEnum, evidenciasIadl: array, revisorSinteticoId: ?string, justificacionFuncional: string, versionContrato?: string} $entradaDcl
     * @param array $dominiosReglas
     * @param array $contratosRelaciones
     * @param array $habilitaciones
     * @param array $procedencias
     * @param array $contexto
     * @return InformeExpertoDTO
     * @throws DomainException
     * @throws \RuntimeException
     */
    public function ejecutar(SolicitudInferenciaDTO $solicitud, array $entradaDcl, array $dominiosReglas,
        array $contratosRelaciones = [], array $habilitaciones = [], array $procedencias = [], array $contexto = []): InformeExpertoDTO
    {
        $obligatorias = ['criterios', 'soportes', 'justificaciones', 'diferencial', 'independenciaRelativa',
            'evidenciasIadl', 'revisorSinteticoId', 'justificacionFuncional'];
        if (array_diff($obligatorias, array_keys($entradaDcl))
            || array_diff(array_keys($entradaDcl), [...$obligatorias, 'versionContrato'])) {
            throw new DomainException('El coordinador requiere declaraciones DCL explícitas con forma cerrada.');
        }
        $entrada = $this->extraer->ejecutar($solicitud);
        $memoria = $this->preparar->ejecutar($solicitud, $entrada);
        $inferencia = $this->inferir->ejecutar($solicitud, $memoria, $dominiosReglas,
            $contratosRelaciones, $habilitaciones, $procedencias, $contexto);
        $dcl = $this->clasificar->ejecutar($solicitud, $inferencia, ...$entradaDcl);

        return $this->informar->desdeResultados($solicitud, $inferencia, $dcl);
    }
}
