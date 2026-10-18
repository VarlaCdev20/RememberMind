<?php

declare(strict_types=1);

namespace MotorExperto\dominio\conocimiento\reglas;

use DateTimeImmutable;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Inferencia observacional.
 * Ejecuta las tres implicaciones literales de la propuesta funcional v0.1.
 * Conserva condición, valor original, fuente, autor y episodio en cada resultado.
 * No interpreta instrumentos, calcula recurrencias ni acredita deterioro cognitivo.
 */
final class EvaluadorObservaciones
{
    public const VERSION = 'OBS-20261017-v1';

    public const REGLAS = [
        'OBS-MEM-01' => ['campo' => 'repite_preguntas', 'nombre' => 'Repetición de preguntas',
            'hallazgo' => 'Se documentó un episodio de repetición de preguntas.'],
        'OBS-MEM-02' => ['campo' => 'olvida_indicaciones', 'nombre' => 'Olvido de indicaciones',
            'hallazgo' => 'Se documentó un episodio de olvido de indicaciones.'],
        'OBS-COG-01' => ['campo' => 'cambio_cognitivo', 'nombre' => 'Cambio cognitivo señalado',
            'hallazgo' => 'Existe un cambio cognitivo señalado que requiere corroboración.'],
    ];

    /**
     * Un registro origina tres decisiones independientes. false no equivale a ausencia de enfermedad.
     * La misma fuente repetida no origina decisiones duplicadas; valores divergentes se rechazan.
     *
     * @param  list<array>  $registros  Instantánea escalar extraída y autorizada por el llamador.
     */
    public function evaluar(string $residente, DateTimeImmutable $corte, array $registros, ?array $conocimiento = null): array
    {
        $reglas = $conocimiento['reglas'] ?? self::REGLAS;
        $version = $conocimiento['codigo'] ?? self::VERSION;
        if ($residente === '' || ! array_is_list($registros)) {
            throw new DomainException('Contexto observacional inválido.');
        }
        $unicos = [];
        foreach ($registros as $r) {
            if (($r['residente'] ?? null) !== $residente || ! is_string($r['id'] ?? null) || $r['id'] === '') {
                throw new DomainException('Fuente observacional ausente o de otro residente.');
            }
            if (isset($unicos[$r['id']]) && $unicos[$r['id']] !== $r) {
                throw new DomainException('La misma fuente presenta instantáneas incompatibles.');
            }
            $unicos[$r['id']] = $r;
        }
        ksort($unicos, SORT_STRING);
        $resultados = [];
        foreach ($unicos as $r) {
            $fecha = $r['fecha'] ?? null;
            $valida = ($r['estado'] ?? null) === 'VIGENTE' && $fecha instanceof DateTimeImmutable && $fecha <= $corte
                && is_string($r['autor'] ?? null) && $r['autor'] !== '' && ($r['contexto_coherente'] ?? false) === true;
            foreach ($reglas as $codigo => $regla) {
                $valor = $r['valores'][$regla['campo']] ?? null;
                $evaluable = $valida && is_bool($valor);
                $estado = ! $evaluable ? 'NO_EVALUABLE' : ($valor ? 'CUMPLE' : 'NO_CUMPLE');
                $motivo = ! $valida ? 'El registro requiere estado vigente, fecha válida, responsable identificable y contexto coherente.'
                    : (! is_bool($valor) ? 'El campo no está registrado con un valor lógico interpretable.'
                        : ($valor ? 'El campo está marcado afirmativamente en el registro.' : 'El campo está registrado como no marcado. Esto no demuestra ausencia de dificultad.'));
                $resultados[] = ['regla' => $codigo, 'version' => $version, 'nombre' => $regla['nombre'],
                    'estado' => $estado, 'hallazgo' => $estado === 'CUMPLE' ? $regla['hallazgo'] : null,
                    'condicion' => ['campo' => $regla['campo'], 'operador' => 'IGUAL_A', 'esperado' => true,
                        'observado' => $valor, 'estado' => $estado, 'motivo' => $motivo],
                    'fuente' => ['tabla' => 'controles_cognitivos', 'registro' => $r['id'],
                        'residente' => $residente, 'autor' => $r['autor'] ?? null, 'fecha' => $fecha instanceof DateTimeImmutable ? $fecha->format('Y-m-d\TH:i:s.uP') : null,
                        'episodio' => $r['episodio'] ?? null]];
            }
        }

        $resumen = [];
        foreach ($reglas as $codigo => $regla) {
            $estados = array_column(array_values(array_filter($resultados, fn ($r) => $r['regla'] === $codigo)), 'estado');
            // Existencia de una observación afirmativa; no es un cálculo de recurrencia ni de normalidad global.
            $estado = in_array('CUMPLE', $estados, true) ? 'CUMPLE'
                : ($estados === [] || in_array('NO_EVALUABLE', $estados, true) ? 'NO_EVALUABLE' : 'NO_CUMPLE');
            $resumen[$codigo] = ['estado' => $estado, 'hallazgo' => $estado === 'CUMPLE' ? $regla['hallazgo'] : null];
            if ($conocimiento !== null) {
                $resumen[$codigo]['nombre'] = $regla['nombre'];
            }
        }

        return ['version' => $version, 'residente' => $residente, 'fecha_corte' => $corte->format('Y-m-d\TH:i:s.uP'),
            'resultados' => $resultados, 'resumen' => $resumen, 'persistido' => false,
            'limite' => 'Son hechos observacionales del seguimiento. No acreditan alteración objetiva, recurrencia ni clasificación DCL.'];
    }
}
