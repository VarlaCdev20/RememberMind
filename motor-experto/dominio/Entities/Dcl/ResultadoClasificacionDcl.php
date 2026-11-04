<?php

declare(strict_types=1);

namespace MotorExperto\dominio\Entities\Dcl;

use MotorExperto\dominio\Entities\Conocimiento\ReglaExperta;
use MotorExperto\dominio\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\ValueObjects\Cognicion\EstadoDominioEnum;
use MotorExperto\dominio\ValueObjects\Dcl\EstadoCriterioDclEnum;
use MotorExperto\dominio\ValueObjects\Dcl\MotivoAbstencionEnum;
use MotorExperto\dominio\ValueObjects\Dcl\SubtipoDclEnum;
use MotorExperto\dominio\ValueObjects\Funcionalidad\EstadoDiferencialEnum;
use DomainException;

/** Conserva un patrón orientativo o una abstención con sus motivos y fuentes. */
final readonly class ResultadoClasificacionDcl
{
    /** Marcador inmutable de alcance, nunca habilitación clínica. */
    public const USO = 'SIMULACION_ACADEMICA_NO_CLINICA';

    /** @var list<MotivoAbstencionEnum> Motivos de no conclusión, separados de PET. */
    public array $motivos;

    /** @var list<ReglaExperta> Reglas técnicas originales y versionadas. */
    public array $reglasAplicadas;

    /**
     * Valida una salida declarada; nunca selecciona automáticamente un patrón.
     * @param PerfilRequisitosDcl $perfil
     * @param SubtipoDclEnum|null $patronCandidato
     * @param list<MotivoAbstencionEnum> $motivos
     * @param string $justificacion
     * @param list<ReglaExperta> $reglasAplicadas
     * @throws DomainException
     */
    public function __construct(
        /** Perfil original de la simulación. */ public PerfilRequisitosDcl $perfil,
        /** Patrón candidato experimental, nunca diagnóstico. */ public ?SubtipoDclEnum $patronCandidato,
        array $motivos,
        /** Fundamento explícito. */ public string $justificacion,
        array $reglasAplicadas = [],
    ) {
        if (! array_is_list($motivos) || ! array_is_list($reglasAplicadas) || trim($justificacion) === ''
            || ($patronCandidato === null ? $motivos === [] : $motivos !== [])) {
            throw new DomainException('La salida requiere patrón o motivos explícitos, de forma excluyente.');
        }
        $razones = $ids = $reglas = [];
        foreach ($motivos as $motivo) {
            if (! $motivo instanceof MotivoAbstencionEnum || isset($razones[$motivo->value])) {
                throw new DomainException('Motivo de abstención mal tipado o duplicado.');
            }
            $razones[$motivo->value] = $motivo;
        }
        foreach ($reglasAplicadas as $regla) {
            if (! $regla instanceof ReglaExperta || ! $regla->habilitada
                || $regla->versionConocimiento !== $perfil->cognicion->memoria->versionConocimiento || isset($ids[$regla->id])) {
                throw new DomainException('Regla aplicada duplicada, deshabilitada o de otra versión.');
            }
            $ids[$regla->id] = true;
            $reglas[] = $regla;
        }
        if ($patronCandidato !== null) {
            foreach ($perfil->criterios as $estado) {
                if ($estado !== EstadoCriterioDclEnum::ACREDITADO) {
                    throw new DomainException('No se emite PET con requisitos incompletos o no acreditados.');
                }
            }
            if ($perfil->diferencial !== EstadoDiferencialEnum::COMPLETADO_SIN_IMPEDIMENTO) {
                throw new DomainException('El diferencial impide emitir un patrón candidato.');
            }
            $memoriaAlterada = false;
            $otrosAlterados = 0;
            foreach ($perfil->cognicion->resultados as $resultado) {
                if (! in_array($resultado->estado, [EstadoDominioEnum::ALTERACION, EstadoDominioEnum::RENDIMIENTO_ESPERADO], true)) {
                    throw new DomainException('U/I/X impiden cualquier patrón PET.');
                }
                if ($resultado->dominio === DominioCognitivoEnum::MEMORIA) {
                    $memoriaAlterada = $resultado->estado === EstadoDominioEnum::ALTERACION;
                } elseif ($resultado->estado === EstadoDominioEnum::ALTERACION) {
                    $otrosAlterados++;
                }
            }
            // Es validación de la distribución declarada, no selección de subtipo.
            $compatible = match ($patronCandidato) {
                SubtipoDclEnum::AMNESICO_DOMINIO_UNICO => $memoriaAlterada && $otrosAlterados === 0,
                SubtipoDclEnum::AMNESICO_MULTIDOMINIO => $memoriaAlterada && $otrosAlterados >= 1,
                SubtipoDclEnum::NO_AMNESICO_DOMINIO_UNICO => ! $memoriaAlterada && $otrosAlterados === 1,
                SubtipoDclEnum::NO_AMNESICO_MULTIDOMINIO => ! $memoriaAlterada && $otrosAlterados >= 2,
            };
            if (! $compatible) {
                throw new DomainException('El patrón declarado no corresponde a la distribución sustentada.');
            }
        }
        $this->motivos = array_values($razones);
        $this->reglasAplicadas = $reglas;
    }

}
