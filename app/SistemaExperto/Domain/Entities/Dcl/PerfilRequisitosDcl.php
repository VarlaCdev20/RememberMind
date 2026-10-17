<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\Entities\Dcl;

use App\SistemaExperto\Domain\Entities\Cognicion\VectorCognitivo;
use App\SistemaExperto\Domain\Entities\Funcionalidad\VectorFuncionalidad;
use App\SistemaExperto\Domain\Entities\Hechos\HechoExperto;
use App\SistemaExperto\Domain\ValueObjects\Dcl\CriterioDclEnum;
use App\SistemaExperto\Domain\ValueObjects\Dcl\EstadoCriterioDclEnum;
use App\SistemaExperto\Domain\ValueObjects\Funcionalidad\EstadoDiferencialEnum;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Clasificación experimental (Requisitos de entrada).
 *
 * Reúne los cinco requisitos DCL y sus soportes junto con los perfiles cognitivo y funcional.
 * Mantiene las condiciones pendientes o contradictorias visibles para una decisión posterior.
 *
 * TÍTULO: Perfil explícito C01–C05 de una simulación.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Clasificación experimental.
 *
 * RESPONSABILIDAD:
 * Preservar requisitos, soportes, revisión diferencial y perfiles sin acreditarlos.
 *
 * ENTRADAS:
 * Vectores cognitivo/funcional, cinco estados, soportes originales y justificaciones.
 *
 * SALIDAS:
 * Perfil inmutable completo, incluido PENDIENTE cuando una condición no se conoce.
 *
 * RESTRICCIONES:
 * No mezcla instantáneas, no usa residentes reales ni acredita requisitos por
 * ausencia. C03 requiere revisión IADL; C04 permanece separado del diagnóstico.
 * No transforma las discrepancias de C02 en normalidad ni las oculta.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R3/R4/R5/R7/R9; contrato DCL-PET-20261017-v0.1.
 */
final readonly class PerfilRequisitosDcl
{
    /** Contrato experimental verificable; no autorización clínica. */
    public const VERSION_CONTRATO = 'DCL-PET-20261017-v0.1';

    /** @var array<string, EstadoCriterioDclEnum> Cinco estados en orden documental. */
    public array $criterios;

    /** @var array<string, list<HechoExperto>> Soportes originales por criterio. */
    public array $soportes;

    /** @var array<string, string> Razones explícitas de cada estado. */
    public array $justificaciones;

    /**
     * Construye requisitos declarados sin inferirlos desde el vector cognitivo.
     *
     * @param VectorCognitivo $cognicion Perfil cognitivo de la simulación.
     * @param VectorFuncionalidad $funcionalidad Perfil funcional de la misma memoria.
     * @param EstadoDiferencialEnum $diferencial Revisión diferencial declarada.
     * @param array<string, EstadoCriterioDclEnum> $criterios Cinco estados explícitos.
     * @param array<string, list<HechoExperto>> $soportes Referencias ficticias independientes.
     * @param array<string, string> $justificaciones Razones para los cinco criterios.
     * @param string $versionContrato Versión experimental, separada de la versión del conocimiento.
     * @throws DomainException Por perfil incompleto, soporte ajeno o acreditación incoherente.
     */
    public function __construct(
        /** Perfil cognitivo original. */ public VectorCognitivo $cognicion,
        /** Perfil funcional original. */ public VectorFuncionalidad $funcionalidad,
        /** Revisión diferencial explícita. */ public EstadoDiferencialEnum $diferencial,
        array $criterios,
        array $soportes,
        array $justificaciones,
        /** Contrato experimental del perfil. */ public string $versionContrato = self::VERSION_CONTRATO,
    ) {
        $memoria = $cognicion->memoria;
        $codigos = array_column(CriterioDclEnum::cases(), 'value');
        if ($funcionalidad->memoria !== $memoria || $versionContrato !== self::VERSION_CONTRATO
            || ! str_starts_with($memoria->evaluacionId, 'SIM-') || ! str_starts_with($memoria->residenteId, 'SINTETICO-')
            || count($criterios) !== 5 || array_diff(array_keys($criterios), $codigos)
            || count($justificaciones) !== 5 || array_diff(array_keys($justificaciones), $codigos)
            || array_diff(array_keys($soportes), $codigos)) {
            throw new DomainException('El perfil DCL requiere un único caso ficticio y cinco requisitos explícitos.');
        }
        $estados = $fuentes = $razones = $usados = [];
        foreach ($codigos as $codigo) {
            $estado = $criterios[$codigo];
            $razon = $justificaciones[$codigo];
            $lista = $soportes[$codigo] ?? [];
            if (! $estado instanceof EstadoCriterioDclEnum || ! is_string($razon) || trim($razon) === ''
                || ! is_array($lista) || ! array_is_list($lista)) {
                throw new DomainException('Estado, razón o lista de soportes de requisito inválidos.');
            }
            $copia = [];
            foreach ($lista as $hecho) {
                if (! $hecho instanceof HechoExperto || ($memoria->hechos[$hecho->id] ?? null) !== $hecho
                    || $hecho->fuenteId !== 'REFERENCIA_CRITERIO_SINTETICA' || $hecho->concepto !== $codigo
                    || $hecho->valor !== $estado->value || isset($usados[$hecho->id])) {
                    throw new DomainException('Soporte de requisito duplicado, ajeno o incompatible.');
                }
                $usados[$hecho->id] = true;
                $copia[] = $hecho;
            }
            if (in_array($estado, [EstadoCriterioDclEnum::ACREDITADO, EstadoCriterioDclEnum::NO_ACREDITADO], true) && $copia === []) {
                throw new DomainException('Un requisito conocido requiere soporte positivo explícito.');
            }
            $estados[$codigo] = $estado;
            $fuentes[$codigo] = $copia;
            $razones[$codigo] = $razon;
        }
        $c03 = $estados[CriterioDclEnum::INDEPENDENCIA_FUNCIONAL_RELATIVA->value];
        if (in_array($c03, [EstadoCriterioDclEnum::ACREDITADO, EstadoCriterioDclEnum::NO_ACREDITADO], true)
            && $c03 !== $funcionalidad->independenciaRelativa) {
            throw new DomainException('C03 no concuerda con la revisión funcional ficticia.');
        }
        if ($estados[CriterioDclEnum::EVALUACION_DIFERENCIAL->value] === EstadoCriterioDclEnum::ACREDITADO
            && $diferencial !== EstadoDiferencialEnum::COMPLETADO_SIN_IMPEDIMENTO) {
            throw new DomainException('C04 no se acredita con revisión diferencial pendiente o impedida.');
        }
        $this->criterios = $estados;
        $this->soportes = $fuentes;
        $this->justificaciones = $razones;
    }

}
