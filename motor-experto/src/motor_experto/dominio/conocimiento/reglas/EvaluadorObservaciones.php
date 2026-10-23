<?php

declare(strict_types=1);

namespace MotorExperto\dominio\conocimiento\reglas;

use DateTimeImmutable;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Inferencia observacional.
 *
 * Ejecuta las tres implicaciones observacionales de la propuesta funcional v0.1.
 * Evalúa hechos conductuales y de memoria cotidiana documentados en el seguimiento.
 * Conserva condición, valor original, fuente, autor y episodio en cada resultado.
 *
 * Límites epistémicos y clínicos:
 * No interpreta instrumentos estandarizados, no calcula recurrencias ni acredita
 * por sí solo deterioro cognitivo leve (DCL).
 */
final class EvaluadorObservaciones
{
    /** Versión del motor de inferencia observacional. */
    public const VERSION = 'OBS-20261017-v1';

    /**
     * Catálogo canónico de reglas observacionales:
     * - OBS-MEM-01: Documentación de repetición de preguntas.
     * - OBS-MEM-02: Documentación de olvido de indicaciones o consignas.
     * - OBS-COG-01: Señalamiento de cambios cognitivos respecto al nivel previo.
     */
    public const REGLAS = [
        'OBS-MEM-01' => [
            'campo' => 'repite_preguntas',
            'nombre' => 'Repetición de preguntas',
            'hallazgo' => 'Se documentó un episodio de repetición de preguntas.',
        ],
        'OBS-MEM-02' => [
            'campo' => 'olvida_indicaciones',
            'nombre' => 'Olvido de indicaciones',
            'hallazgo' => 'Se documentó un episodio de olvido de indicaciones.',
        ],
        'OBS-COG-01' => [
            'campo' => 'cambio_cognitivo',
            'nombre' => 'Cambio cognitivo señalado',
            'hallazgo' => 'Existe un cambio cognitivo señalado que requiere corroboración.',
        ],
    ];

    /**
     * Evalúa las observaciones documentadas para un residente hasta la fecha de corte.
     *
     * Invariantes de inferencia:
     * - Cada registro origina la evaluación de las tres reglas de manera independiente.
     * - Un valor false refleja no marcación en el episodio; no prueba ausencia de enfermedad.
     * - Registros con misma fuente se deduplican; si presentan datos divergentes se rechazan.
     *
     * @param  string  $residente  Identificador único del residente evaluado.
     * @param  DateTimeImmutable  $corte  Límite temporal estricto (no se evalúan hechos posteriores).
     * @param  list<array>  $registros  Instantánea escalar extraída y autorizada por la capa de aplicación.
     * @param  array|null  $conocimiento  Base de reglas y código de versión dinámico opcional.
     * @return array Inferencia con resultados trazables por registro, resumen consolidado y límites.
     *
     * @throws DomainException Si el contexto es inválido o se detectan fuentes incompatibles.
     */
    public function evaluar(string $residente, DateTimeImmutable $corte, array $registros, ?array $conocimiento = null): array
    {
        // 1. Resolver reglas y versión (prioriza conocimiento dinámico si se suministra)
        $reglas = $conocimiento['reglas'] ?? self::REGLAS;
        $version = $conocimiento['codigo'] ?? self::VERSION;

        // 2. Validación de precondiciones: residente definido y lista indexada de registros
        if ($residente === '' || ! array_is_list($registros)) {
            throw new DomainException('Contexto observacional inválido.');
        }

        // 3. Deduplicación e integridad: agrupar por id y rechazar instantáneas contradictorias
        $unicos = [];
        foreach ($registros as $r) {
            // Verificar pertenencia estricta al residente e identificador válido
            if (($r['residente'] ?? null) !== $residente || ! is_string($r['id'] ?? null) || $r['id'] === '') {
                throw new DomainException('Fuente observacional ausente o de otro residente.');
            }
            // Rechazar conflicto: mismo ID con diferente contenido en la misma evaluación
            if (isset($unicos[$r['id']]) && $unicos[$r['id']] !== $r) {
                throw new DomainException('La misma fuente presenta instantáneas incompatibles.');
            }
            $unicos[$r['id']] = $r;
        }

        // 4. Ordenamiento determinístico por clave para garantizar reproducibilidad
        ksort($unicos, SORT_STRING);

        // 5. Evaluación individual de cada regla sobre cada registro
        $resultados = [];
        foreach ($unicos as $r) {
            $fecha = $r['fecha'] ?? null;

            // Comprobar validez clínica: estado vigente, fecha dentro del corte, autor y coherencia
            $valida = ($r['estado'] ?? null) === 'VIGENTE'
                && $fecha instanceof DateTimeImmutable
                && $fecha <= $corte
                && is_string($r['autor'] ?? null)
                && $r['autor'] !== ''
                && ($r['contexto_coherente'] ?? false) === true;

            foreach ($reglas as $codigo => $regla) {
                $valor = $r['valores'][$regla['campo']] ?? null;
                $evaluable = $valida && is_bool($valor);

                // Determinar estado inferido: CUMPLE, NO_CUMPLE o NO_EVALUABLE
                $estado = ! $evaluable ? 'NO_EVALUABLE' : ($valor ? 'CUMPLE' : 'NO_CUMPLE');

                // Motivo explícito de auditoría para trazabilidad médica
                $motivo = ! $valida
                    ? 'El registro requiere estado vigente, fecha válida, responsable identificable y contexto coherente.'
                    : (! is_bool($valor)
                        ? 'El campo no está registrado con un valor lógico interpretable.'
                        : ($valor
                            ? 'El campo está marcado afirmativamente en el registro.'
                            : 'El campo está registrado como no marcado. Esto no demuestra ausencia de dificultad.'));

                // Registrar evidencia trazable con condición evaluada y procedencia de origen
                $resultados[] = [
                    'regla' => $codigo,
                    'version' => $version,
                    'nombre' => $regla['nombre'],
                    'estado' => $estado,
                    'hallazgo' => $estado === 'CUMPLE' ? $regla['hallazgo'] : null,
                    'condicion' => [
                        'campo' => $regla['campo'],
                        'operador' => 'IGUAL_A',
                        'esperado' => true,
                        'observado' => $valor,
                        'estado' => $estado,
                        'motivo' => $motivo,
                    ],
                    'fuente' => [
                        'tabla' => 'controles_cognitivos',
                        'registro' => $r['id'],
                        'residente' => $residente,
                        'autor' => $r['autor'] ?? null,
                        'fecha' => $fecha instanceof DateTimeImmutable ? $fecha->format('Y-m-d\TH:i:s.uP') : null,
                        'episodio' => $r['episodio'] ?? null,
                    ],
                ];
            }
        }

        // 6. Resumen consolidado por regla (búsqueda de al menos un hecho afirmativo)
        $resumen = [];
        foreach ($reglas as $codigo => $regla) {
            $estados = array_column(array_values(array_filter($resultados, fn ($r) => $r['regla'] === $codigo)), 'estado');

            // CUMPLE si existe al menos una afirmación; NO_EVALUABLE si no hay datos evaluables; NO_CUMPLE en caso contrario.
            $estado = in_array('CUMPLE', $estados, true)
                ? 'CUMPLE'
                : ($estados === [] || in_array('NO_EVALUABLE', $estados, true) ? 'NO_EVALUABLE' : 'NO_CUMPLE');

            $resumen[$codigo] = [
                'estado' => $estado,
                'hallazgo' => $estado === 'CUMPLE' ? $regla['hallazgo'] : null,
            ];

            if ($conocimiento !== null) {
                $resumen[$codigo]['nombre'] = $regla['nombre'];
            }
        }

        // 7. Retornar paquete final de inferencia observacional con advertencia de límite clínico
        return [
            'version' => $version,
            'residente' => $residente,
            'fecha_corte' => $corte->format('Y-m-d\TH:i:s.uP'),
            'resultados' => $resultados,
            'resumen' => $resumen,
            'persistido' => false,
            'limite' => 'Son hechos observacionales del seguimiento. No acreditan alteración objetiva, recurrencia ni clasificación DCL.',
        ];
    }
}

