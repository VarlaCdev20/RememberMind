<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Dominio\Reglas;

/**
 * Piloto de reglas descriptivas: examina hechos ya registrados, sin clasificar
 * DCL ni estimar riesgo. No representa conocimiento clínico activado.
 *
 * Las reglas se trasladarán al conocimiento versionado después de aprobar
 * sus contratos fuente -> variable -> valor y el flujo editorial.
 */
final class EvaluadorHechosObservacionales
{
    public const VERSION = 'OBS-20261017-v1';

    private const REGLAS = [
        'OBS-COG-001' => [
            'campo' => 'repite_preguntas',
            'nombre' => 'Repetición de preguntas',
            'hallazgo' => 'Se documentaron observaciones de repetición de preguntas.',
        ],
        'OBS-COG-002' => [
            'campo' => 'olvida_indicaciones',
            'nombre' => 'Olvido de indicaciones',
            'hallazgo' => 'Se documentaron observaciones de olvido de indicaciones.',
        ],
        'OBS-COG-003' => [
            'campo' => 'cambio_cognitivo',
            'nombre' => 'Cambio cognitivo señalado',
            'hallazgo' => 'Se señaló un cambio cognitivo en los controles consultados.',
        ],
    ];

    /**
     * @param array<int, array<string, mixed>> $controles Registros ya autorizados y filtrados.
     */
    public function evaluar(array $controles): array
    {
        $resultados = [];
        foreach (self::REGLAS as $codigo => $definicion) {
            $evaluables = 0;
            $soportes = [];
            foreach ($controles as $control) {
                $valor = $control[$definicion['campo']] ?? null;
                if ($valor === null) {
                    continue;
                }
                ++$evaluables;
                if ($valor === true) {
                    $soportes[] = [
                        'registro' => $control['registro'],
                        'fecha' => $control['fecha'],
                        'autor' => $control['autor'],
                    ];
                }
            }

            $positivos = count($soportes);
            $estado = $positivos > 0 ? 'CUMPLE' : ($evaluables > 0 ? 'NO_CUMPLE' : 'NO_EVALUABLE');
            $resultados[] = [
                'codigo' => $codigo,
                'nombre' => $definicion['nombre'],
                'condicion' => 'SI existe un control válido con '.$definicion['campo'].' marcado, ENTONCES registrar la observación.',
                'estado' => $estado,
                'registros_evaluables' => $evaluables,
                'registros_positivos' => $positivos,
                'soportes' => $soportes,
                'explicacion' => match ($estado) {
                    'CUMPLE' => $definicion['hallazgo'].' Se encontraron '.$positivos.' registros marcados dentro de la muestra consultada.',
                    'NO_CUMPLE' => 'No se encontró el indicador marcado en los registros evaluables de la muestra. Esto no demuestra ausencia de dificultades.',
                    default => 'Los controles consultados no contienen un valor interpretable para este indicador.',
                },
            ];
        }

        return [
            'version' => self::VERSION,
            'registros_consultados' => count($controles),
            'limite_registros' => 30,
            'muestra_limitada' => count($controles) === 30,
            'reglas' => $resultados,
            'advertencia' => 'Hallazgos descriptivos derivados de registros de seguimiento. No constituyen una evaluación objetiva de dominios, una clasificación DCL ni un diagnóstico.',
        ];
    }
}
