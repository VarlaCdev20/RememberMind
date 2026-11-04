<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Services\Dcl;

use MotorExperto\dominio\orion\Entities\Dcl\PerfilRequisitosDcl;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EstadoDominioEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\CriterioDclEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\EstadoCriterioDclEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\MotivoAbstencionEnum;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoDiferencialEnum;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Clasificación experimental (Compuertas DCL).
 * TÍTULO: Comprobación de requisitos C01–C05 y coherencia contractual.
 * CAPA: Domain.
 * SUBSISTEMA: DCL experimental.
 * RESPONSABILIDAD: Identificar impedimentos sin acreditar requisitos por inferencia.
 * ENTRADAS: Perfil sintético con declaraciones y soportes originales.
 * SALIDAS: Admisibilidad, motivos y razones de cada impedimento.
 * RESTRICCIONES: No sustituye revisión IADL/diferencial ni emite diagnóstico.
 * TRAZABILIDAD: R1/R5/R7/R9; DCL-PET-20261017-v0.1 §§4–6.
 */
final readonly class EvaluadorRequisitosDclService
{
    /**
     * Conserva todos los motivos en orden técnico determinista, sin prioridad clínica.
     *
     * @param  PerfilRequisitosDcl  $perfil  Entradas ya validadas constructivamente.
     * @return array{habilitado: bool, motivos: list<MotivoAbstencionEnum>, impedimentos: list<MotivoAbstencionEnum>, razones: array<string, string>}
     *                                                                                                                                                No produce efectos ni modifica los requisitos declarados.
     */
    public function evaluar(PerfilRequisitosDcl $perfil): array
    {
        $motivos = $razones = [];
        $agregar = static function (MotivoAbstencionEnum $motivo, string $id, string $razon) use (&$motivos, &$razones): void {
            $motivos[$motivo->value] = $motivo;
            $razones[$id] = $razon;
        };
        foreach ($perfil->criterios as $codigo => $estado) {
            $motivo = match ($estado) {
                EstadoCriterioDclEnum::CONTRADICTORIO => MotivoAbstencionEnum::ABSTENCION_CONTRADICCION,
                EstadoCriterioDclEnum::PENDIENTE => MotivoAbstencionEnum::ABSTENCION_INFORMACION_INSUFICIENTE,
                EstadoCriterioDclEnum::NO_ACREDITADO => MotivoAbstencionEnum::SIN_PATRON_CLASIFICABLE_POR_REQUISITO,
                EstadoCriterioDclEnum::ACREDITADO => null,
            };
            if ($motivo !== null) {
                $agregar($motivo, $codigo, $perfil->justificaciones[$codigo]);
            }
        }
        if ($perfil->funcionalidad->independenciaRelativa === EstadoCriterioDclEnum::CONTRADICTORIO) {
            $agregar(MotivoAbstencionEnum::ABSTENCION_CONTRADICCION, 'FUNCIONALIDAD', $perfil->funcionalidad->justificacion);
        }
        if ($perfil->diferencial === EstadoDiferencialEnum::CONTRADICTORIO) {
            $agregar(MotivoAbstencionEnum::ABSTENCION_CONTRADICCION, 'DIFERENCIAL', $perfil->diferencial->value);
        } elseif ($perfil->diferencial === EstadoDiferencialEnum::IMPEDIMENTO_NO_RESUELTO
            || $perfil->criterios[CriterioDclEnum::EVALUACION_DIFERENCIAL->value] === EstadoCriterioDclEnum::NO_ACREDITADO) {
            $agregar(MotivoAbstencionEnum::ABSTENCION_EVALUACION_DIFERENCIAL, 'DIFERENCIAL', $perfil->diferencial->value);
        } elseif ($perfil->diferencial === EstadoDiferencialEnum::PENDIENTE) {
            $agregar(MotivoAbstencionEnum::ABSTENCION_INFORMACION_INSUFICIENTE, 'DIFERENCIAL', $perfil->diferencial->value);
        }
        $alterados = 0;
        $completo = true;
        foreach ($perfil->cognicion->resultados as $codigo => $resultado) {
            $alterados += $resultado->estado === EstadoDominioEnum::ALTERACION ? 1 : 0;
            if ($resultado->estado === EstadoDominioEnum::CONTRADICTORIO) {
                $agregar(MotivoAbstencionEnum::ABSTENCION_CONTRADICCION, $codigo, $resultado->justificacion);
            } elseif (in_array($resultado->estado, [EstadoDominioEnum::DESCONOCIDO, EstadoDominioEnum::NO_INTERPRETABLE], true)) {
                $agregar(MotivoAbstencionEnum::ABSTENCION_INFORMACION_INSUFICIENTE, $codigo, $resultado->justificacion);
            }
            $completo = $completo && in_array($resultado->estado, [EstadoDominioEnum::ALTERACION, EstadoDominioEnum::RENDIMIENTO_ESPERADO], true);
        }
        $c02 = $perfil->criterios[CriterioDclEnum::ALTERACION_COGNITIVA_OBJETIVA->value];
        if (($c02 === EstadoCriterioDclEnum::ACREDITADO && $completo && $alterados === 0)
            || ($c02 === EstadoCriterioDclEnum::NO_ACREDITADO && $alterados > 0)) {
            $agregar(MotivoAbstencionEnum::REQUIERE_ADJUDICACION_CONTRATO, 'COHERENCIA_C02',
                'La acreditación de C02 no concuerda con los estados sustentados del perfil.');
        }
        // Estado principal contractual; CAS-11 precisa adjudicación C02 antes del veto general N.
        // Se conservan los demás impedimentos en detalle, sin inventar otra salida principal.
        $ordenados = [];
        foreach ([MotivoAbstencionEnum::ABSTENCION_CONTRADICCION,
            MotivoAbstencionEnum::ABSTENCION_EVALUACION_DIFERENCIAL,
            MotivoAbstencionEnum::ABSTENCION_INFORMACION_INSUFICIENTE,
            MotivoAbstencionEnum::REQUIERE_ADJUDICACION_CONTRATO,
            MotivoAbstencionEnum::SIN_PATRON_CLASIFICABLE_POR_REQUISITO] as $motivo) {
            if (isset($motivos[$motivo->value])) {
                $ordenados[] = $motivo;
            }
        }

        return ['habilitado' => $ordenados === [], 'motivos' => array_slice($ordenados, 0, 1),
            'impedimentos' => $ordenados, 'razones' => $razones];
    }
}
