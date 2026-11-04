<?php

declare(strict_types=1);

namespace MotorExperto\dominio\Entities\Dcl;

use MotorExperto\dominio\Entities\Cognicion\VectorCognitivo;
use MotorExperto\dominio\Entities\Funcionalidad\VectorFuncionalidad;
use MotorExperto\dominio\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\ValueObjects\Dcl\CriterioDclEnum;
use MotorExperto\dominio\ValueObjects\Dcl\EstadoCriterioDclEnum;
use MotorExperto\dominio\ValueObjects\Funcionalidad\EstadoDiferencialEnum;
use DomainException;

/** Reúne requisitos y soportes del caso; conserva pendientes y contradicciones. */
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
     * @param VectorCognitivo $cognicion
     * @param VectorFuncionalidad $funcionalidad
     * @param EstadoDiferencialEnum $diferencial
     * @param array<string, EstadoCriterioDclEnum> $criterios
     * @param array<string, list<HechoExperto>> $soportes
     * @param array<string, string> $justificaciones
     * @param string $versionContrato
     * @throws DomainException
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
