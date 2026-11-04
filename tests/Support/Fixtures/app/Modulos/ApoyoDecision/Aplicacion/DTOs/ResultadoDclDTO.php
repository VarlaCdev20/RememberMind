<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\DTOs;

use MotorExperto\dominio\Entities\Dcl\ResultadoClasificacionDcl;
use MotorExperto\dominio\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\ValueObjects\Cognicion\EstadoDominioEnum;
use MotorExperto\dominio\ValueObjects\Dcl\EstadoCriterioDclEnum;
use MotorExperto\dominio\ValueObjects\Dcl\MotivoAbstencionEnum;
use MotorExperto\dominio\ValueObjects\Dcl\SubtipoDclEnum;
use DomainException;

/** Transporta el resultado original de Domain, sea un patrón candidato sintético o una abstención, con sus requisitos y soportes. */
final readonly class ResultadoDclDTO
{
    /** Identidad de la evaluación a la que pertenece esta salida. */
    public string $analisisId;

    /** Versión contractual original, separada de la versión del conocimiento. */
    public string $versionContrato;

    /** @var array<string, EstadoCriterioDclEnum> Los cinco requisitos originales C01–C05. */
    public array $criterios;

    /** @var array<string, EstadoDominioEnum> Estados originales de los cinco dominios. */
    public array $estadosDominios;

    /** Patrón original o null cuando Domain no concluyó. */
    public ?SubtipoDclEnum $patronCandidato;

    /** Indica exclusivamente que el resultado original no contiene un patrón. */
    public bool $abstencion;

    /** @var list<MotivoAbstencionEnum> Motivos originales de no conclusión. */
    public array $motivos;

    /** @var array<string, list<HechoExperto>> Referencias originales por requisito. */
    public array $soportes;

    /**
     * Proyecta una salida ya validada sin reimplementar el clasificador.
     * @param SolicitudInferenciaDTO $solicitud
     * @param ResultadoClasificacionDcl $resultado
     * @throws DomainException
     */
    public function __construct(
        /** Solicitud original. */ public SolicitudInferenciaDTO $solicitud,
        /** Resultado íntegro, incluidas justificación y reglas aplicadas. */ public ResultadoClasificacionDcl $resultado,
    ) {
        $solicitud->exigirContexto($resultado->perfil->cognicion->memoria);
        $estados = [];
        foreach ($resultado->perfil->cognicion->resultados as $codigo => $dominio) {
            $estados[$codigo] = $dominio->estado;
        }
        $this->analisisId = $solicitud->evaluacionId;
        $this->versionContrato = $resultado->perfil->versionContrato;
        $this->criterios = $resultado->perfil->criterios;
        $this->estadosDominios = $estados;
        $this->patronCandidato = $resultado->patronCandidato;
        $this->abstencion = $resultado->patronCandidato === null;
        $this->motivos = $resultado->motivos;
        $this->soportes = $resultado->perfil->soportes;
    }
}
