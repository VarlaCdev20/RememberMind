<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Entities\Dcl;

use MotorExperto\dominio\orion\Entities\Conocimiento\ReglaExperta;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EstadoDominioEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\EstadoCriterioDclEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\MotivoAbstencionEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\SubtipoDclEnum;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoDiferencialEnum;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Clasificación experimental (Salida y trazabilidad).
 *
 * Conserva un patrón candidato declarado o una abstención con razones y antecedentes.
 * Valida la coherencia de la salida en escenarios sintéticos, sin emitir un diagnóstico clínico.
 *
 * TÍTULO: Resultado candidato o abstención trazable.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Clasificación y explicación experimental.
 *
 * RESPONSABILIDAD:
 * Conservar una salida declarada coherente, sin ejecutar la clasificación.
 *
 * ENTRADAS:
 * Perfil, patrón opcional, motivos separados, justificación y reglas técnicas aplicadas.
 *
 * SALIDAS:
 * Resultado inmutable exclusivamente sintético con acceso a todos sus soportes.
 *
 * RESTRICCIONES:
 * No decide qué patrón emitir: el servicio futuro lo hará. El constructor solo
 * rechaza candidatos incompatibles con sus precondiciones y distribución.
 * No constituye diagnóstico, etiología, pronóstico ni autorización asistencial.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R3/R4/R7/R9/R10; DCL-PET-20261017-v0.1 §§4–6.
 */
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
     *
     * @param PerfilRequisitosDcl $perfil Entradas y soportes originales.
     * @param SubtipoDclEnum|null $patronCandidato Patrón propuesto o null si no concluye.
     * @param list<MotivoAbstencionEnum> $motivos Razones explícitas de no conclusión.
     * @param string $justificacion Explicación de la salida.
     * @param list<ReglaExperta> $reglasAplicadas Reglas técnicas realmente declaradas como aplicadas.
     * @throws DomainException Por salida ambigua, insuficiente, incoherente o de otra versión.
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
