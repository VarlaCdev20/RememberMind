<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ResultadoDclDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ResultadoInferenciaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use DomainException;
use MotorExperto\dominio\Entities\Dcl\PerfilRequisitosDcl;
use MotorExperto\dominio\Entities\Funcionalidad\VectorFuncionalidad;
use MotorExperto\dominio\Services\Dcl\ClasificadorPetersenService;
use MotorExperto\dominio\ValueObjects\Dcl\EstadoCriterioDclEnum;
use MotorExperto\dominio\ValueObjects\Funcionalidad\EstadoDiferencialEnum;

/** Solicita un patrón candidato o abstención a Domain. */
final readonly class ClasificarDclUseCase
{
    /**
     * Recibe un clasificador puro; no activa procesos ni persistencia.
     * @param ClasificadorPetersenService $clasificador
     */
    public function __construct(
        /** Selecciona patrón o abstención en Domain. */ private ClasificadorPetersenService $clasificador = new ClasificadorPetersenService,
    ) {}

    /**
     * Transporta declaraciones externas al contrato de Domain, que valida su coherencia.
     * @param SolicitudInferenciaDTO $solicitud
     * @param ResultadoInferenciaDTO $inferencia
     * @param array $criterios
     * @param array $soportes
     * @param array $justificaciones
     * @param EstadoDiferencialEnum $diferencial
     * @param EstadoCriterioDclEnum $independenciaRelativa
     * @param array $evidenciasIadl
     * @param string|null $revisorSinteticoId
     * @param string $justificacionFuncional
     * @param string $versionContrato
     * @return ResultadoDclDTO
     * @throws DomainException
     */
    public function ejecutar(SolicitudInferenciaDTO $solicitud, ResultadoInferenciaDTO $inferencia,
        array $criterios, array $soportes, array $justificaciones, EstadoDiferencialEnum $diferencial,
        EstadoCriterioDclEnum $independenciaRelativa, array $evidenciasIadl, ?string $revisorSinteticoId,
        string $justificacionFuncional, string $versionContrato = PerfilRequisitosDcl::VERSION_CONTRATO): ResultadoDclDTO
    {
        if ($inferencia->solicitud != $solicitud) {
            throw new DomainException('La inferencia DCL pertenece a otra solicitud.');
        }
        $solicitud->exigirContexto($inferencia->perfilCognitivo->memoria);
        $funcionalidad = new VectorFuncionalidad($inferencia->perfilCognitivo->memoria, $independenciaRelativa,
            $evidenciasIadl, $revisorSinteticoId, $justificacionFuncional);
        $perfil = new PerfilRequisitosDcl($inferencia->perfilCognitivo, $funcionalidad, $diferencial,
            $criterios, $soportes, $justificaciones, $versionContrato);

        return new ResultadoDclDTO($solicitud, $this->clasificador->clasificar($perfil));
    }
}
