<?php

declare(strict_types=1);

namespace App\SistemaExperto\Application\DTOs;

use App\SistemaExperto\Domain\Entities\Dcl\ResultadoClasificacionDcl;
use App\SistemaExperto\Domain\Entities\Hechos\HechoExperto;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\EstadoDominioEnum;
use App\SistemaExperto\Domain\ValueObjects\Dcl\EstadoCriterioDclEnum;
use App\SistemaExperto\Domain\ValueObjects\Dcl\MotivoAbstencionEnum;
use App\SistemaExperto\Domain\ValueObjects\Dcl\SubtipoDclEnum;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Application (Salida experimental de Petersen).
 *
 * TÍTULO:
 * Resultado experimental de clasificación Petersen.
 *
 * CAPA:
 * Application - Objetos de Transferencia de Datos (DTOs).
 *
 * ¿PARA QUÉ SIRVE?
 * Transporta el resultado original de Domain, sea un patrón candidato
 * sintético o una abstención, con sus requisitos y soportes.
 *
 * ¿QUÉ RECIBE?
 * La solicitud original y un ResultadoClasificacionDcl ya validado por Domain.
 *
 * ¿QUÉ HACE?
 * Contrasta el contexto y proyecta literalmente los estados, motivos, patrón
 * y versión contractual sin ofrecer parámetros para reemplazarlos.
 *
 * ¿QUÉ DEVUELVE?
 * Una salida inmutable vinculada a la evaluación y al perfil original.
 *
 * ¿QUÉ NO DEBE HACER?
 * No selecciona PET, acredita C01–C05, resuelve C03/C04 ni modifica abstenciones.
 * No expresa un diagnóstico o una conclusión etiológica.
 *
 * RELACIÓN CON O.R.I.O.N.:
 * Es el contrato de salida de CU-04; conserva PET-01 a PET-04 y los motivos
 * del contrato experimental verificado por PerfilRequisitosDcl.
 */
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
     *
     * @param  SolicitudInferenciaDTO  $solicitud  Contexto del análisis experimental.
     * @param  ResultadoClasificacionDcl  $resultado  Salida original con perfil, explicación y reglas.
     *
     * @throws DomainException Si la salida pertenece a otro caso, evaluación, versión o corte.
     *
     * La exclusión entre patrón y abstención ya está garantizada por Domain;
     * conservar su objeto mantiene también diferencial y condiciones verificadas.
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
