<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Compartido\Infraestructura\Persistencia\CatalogoCodigos;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConservacionObservacional;
use MotorExperto\soporte\conservacion\CodecInstantanea as Codec;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorObservaciones;

/** Escribe exclusivamente tablas expertas existentes dentro de la transacción de la acción. */
final class ConservarObservaciones implements ConservacionObservacional
{
    /** Firma los datos originales tipados, incluido el estado, y todas las decisiones; orden explícito estable. */
    public function huella(array $registros, array $informe, ?array $conocimiento = null): string
    {
        $originales = [];
        foreach ($registros as $r) {
            $valores = [];
            foreach (EvaluadorObservaciones::REGLAS as $regla) {
                $valores[$regla['campo']] = $r['valores'][$regla['campo']];
            }
            $originales[] = ['id' => $r['id'], 'residente' => $r['residente'], 'autor' => $r['autor'],
                'fecha' => Codec::fecha('fecha', $r['fecha']), 'estado' => $r['estado'], 'episodio' => $r['episodio'],
                'contexto_coherente' => $r['contexto_coherente'], 'valores' => $valores];
        }
        usort($originales, fn ($a, $b) => strcmp($a['id'], $b['id']));

        $contenido = ['originales' => $originales, 'informe' => $informe];
        if ($conocimiento !== null) {
            $contenido['conocimiento'] = $conocimiento;
        }

        return Codec::huella($contenido);
    }

    private function insertar(string $tabla, array $datos): string
    {
        $id = app(CodigosEntidad::class)->siguiente($tabla);
        DB::table($tabla)->insert([CatalogoCodigos::clave($tabla) => $id] + $datos);

        return $id;
    }

    public function guardar(string $residente, string $personal, string $solicitud, array $paquete, array $registros, array $informe): string
    {
        if (($paquete['contrato'] ?? null) === 'OBS-SNAP-2') {
            return $this->guardarVersionada($residente, $personal, $solicitud, $paquete, $registros, $informe);
        }
        $fecha = now()->format('Y-m-d H:i:s');
        $id = $this->insertar('evaluaciones_expertas', ['cod_residente' => $residente, 'cod_version_modelo' => $paquete['version'],
            'cod_personal_solicitante' => $personal, 'origen_activacion' => 'OBSERVACIONAL', 'estado_ejecucion' => 'FINALIZADA',
            'fecha_hora_inicio' => $fecha, 'fecha_hora_fin' => $fecha, 'id_evaluacion_dominio' => 'OBS:'.$solicitud,
            'version_conservacion' => 'OBS-SNAP-1', 'huella_memoria_cognicion' => $this->huella($registros, $informe)]
            + Codec::fecha('fecha_corte', new DateTimeImmutable($informe['fecha_corte'])));
        $evidencias = [];
        $orden = 0;
        $decisiones = [];
        foreach ($informe['resultados'] as $decision) {
            $decisiones[$decision['fuente']['registro']][$decision['regla']] = $decision;
        }
        foreach ($registros as $r) {
            $primeraEvidencia = null;
            foreach ($paquete['reglas'] as $codigo => $regla) {
                $valor = $r['valores'][$regla['campo']];
                $decision = $decisiones[$r['id']][$codigo];
                $e = $this->insertar('evidencias_evaluacion', ['cod_evaluacion_experta' => $id,
                    'cod_mapeo_variable_fuente' => $regla['mapeo'], 'cod_registro_fuente' => $r['id'],
                    'estado_representacion' => $valor === null ? 'NO_MAPEADO' : 'MAPEADO',
                    'cod_valor_semantico' => $valor === null ? null : $paquete['valores'][$valor ? 'SI' : 'NO'],
                    'estado_admisibilidad' => $decision['estado'] === 'NO_EVALUABLE' ? 'NO_ADMISIBLE' : 'ADMISIBLE',
                    'motivo_admisibilidad' => $decision['condicion']['motivo'], 'fecha_hora_incorporacion' => $fecha]);
                $evidencias[$codigo][$r['id']] = ['id' => $e, 'estado' => $decision['estado']];
                $primeraEvidencia ??= $e;
                $this->hecho($id, $r, $regla['campo'], $valor, $e, $orden++);
            }
            // Estado original compartido por las tres condiciones, conservado como hecho tipado del control.
            $this->hecho($id, $r, 'estado', $r['estado'], $primeraEvidencia, $orden++);
        }
        foreach ($paquete['reglas'] as $codigo => $regla) {
            $estado = $informe['resumen'][$codigo]['estado'];
            $ec = $this->insertar('evaluacion_criterios', ['cod_evaluacion_experta' => $id, 'cod_nodo_criterio' => $regla['criterio'],
                'estado_evaluabilidad' => $estado === 'NO_EVALUABLE' ? 'OBS_NO_EVALUABLE' : 'OBS_EVALUABLE',
                'fecha_hora_determinacion' => $fecha]);
            $traza = $this->insertar('trazas_inferencia', ['cod_evaluacion_criterio' => $ec, 'estado_inferencia' => 'FINALIZADA',
                'fecha_hora_inicio' => $fecha, 'fecha_hora_fin' => $fecha]);
            $er = $this->insertar('evaluaciones_reglas', ['cod_traza_inferencia' => $traza, 'cod_regla_experta' => $regla['regla'],
                'estado_regla' => $estado, 'fecha_hora_evaluacion' => $fecha]);
            $condicion = $this->insertar('evaluaciones_condiciones_regla', ['cod_evaluacion_regla' => $er, 'cod_condicion_regla' => $regla['condicion'],
                'estado_condicion' => $estado, 'fecha_hora_evaluacion' => $fecha]);
            foreach ($evidencias[$codigo] ?? [] as $e) {
                $this->insertar('evidencias_criterio_evaluacion', ['cod_evaluacion_criterio' => $ec, 'cod_evidencia_evaluacion' => $e['id'],
                    'rol_en_criterio' => 'OBSERVACION', 'estado_participacion' => $e['estado'], 'fecha_hora_vinculacion' => $fecha]);
                // Todas las fuentes quedan vinculadas: también justifican negativos y desconocimiento.
                $this->insertar('evidencias_soporte_condicion', ['cod_evaluacion_condicion' => $condicion, 'cod_evidencia_evaluacion' => $e['id']]);
            }
            if ($estado === 'CUMPLE') {
                $this->insertar('resultados_criterio', ['cod_evaluacion_criterio' => $ec, 'cod_valor_semantico' => $regla['valorResultado'],
                    'fecha_hora_determinacion' => $fecha, 'justificacion_original' => $informe['resumen'][$codigo]['hallazgo']]);
            }
        }

        return $id;
    }

    /** Captura por mapeo/control; las reglas comparten evidencia mediante las relaciones existentes. */
    private function guardarVersionada(string $residente, string $personal, string $solicitud, array $p, array $registros, array $informe): string
    {
        $fecha = now()->format('Y-m-d H:i:s');
        $id = $this->insertar('evaluaciones_expertas', ['cod_residente' => $residente, 'cod_version_modelo' => $p['version'],
            'cod_personal_solicitante' => $personal, 'origen_activacion' => 'OBSERVACIONAL', 'estado_ejecucion' => 'FINALIZADA',
            'fecha_hora_inicio' => $fecha, 'fecha_hora_fin' => $fecha, 'id_evaluacion_dominio' => 'OBS:'.$solicitud,
            'version_conservacion' => 'OBS-SNAP-2', 'huella_memoria_cognicion' => $this->huella($registros, $informe, $p)]
            + Codec::fecha('fecha_corte', new DateTimeImmutable($informe['fecha_corte'])));
        $literal = (new EvaluadorObservaciones)->evaluar($residente, new DateTimeImmutable($informe['fecha_corte']), $registros);
        $decisiones = [];
        foreach ($literal['resultados'] as $decision) {
            $decisiones[$decision['fuente']['registro']][$decision['condicion']['campo']] = $decision;
        }
        $evidencias = [];
        $orden = 0;
        foreach ($registros as $registro) {
            $primera = null;
            foreach ($p['campos'] as $campo => $definicion) {
                $valor = $registro['valores'][$campo];
                $decision = $decisiones[$registro['id']][$campo];
                $ev = $this->insertar('evidencias_evaluacion', ['cod_evaluacion_experta' => $id,
                    'cod_mapeo_variable_fuente' => $definicion['mapeo'], 'cod_registro_fuente' => $registro['id'],
                    'estado_representacion' => $valor === null ? 'NO_MAPEADO' : 'MAPEADO',
                    'cod_valor_semantico' => $valor === null ? null : $p['valores'][$valor ? 'SI' : 'NO'],
                    'estado_admisibilidad' => $decision['estado'] === 'NO_EVALUABLE' ? 'NO_ADMISIBLE' : 'ADMISIBLE',
                    'motivo_admisibilidad' => $decision['condicion']['motivo'], 'fecha_hora_incorporacion' => $fecha]);
                $primera ??= $ev;
                $evidencias[$campo][] = ['id' => $ev, 'estado' => $decision['estado']];
                $this->hecho($id, $registro, $campo, $valor, $ev, $orden++);
            }
            $this->hecho($id, $registro, 'estado', $registro['estado'], $primera, $orden++);
        }
        foreach ($p['criterios'] as $criterio => $definicion) {
            $reglas = array_filter($p['reglas'], fn ($r) => $r['criterio'] === $criterio);
            $estados = array_map(fn ($codigo) => $informe['resumen'][$codigo]['estado'], array_keys($reglas));
            $estado = in_array('CUMPLE', $estados, true) ? 'CUMPLE'
                : ($estados === [] || in_array('NO_EVALUABLE', $estados, true) ? 'NO_EVALUABLE' : 'NO_CUMPLE');
            $ec = $this->insertar('evaluacion_criterios', ['cod_evaluacion_experta' => $id, 'cod_nodo_criterio' => $criterio,
                'estado_evaluabilidad' => $estado === 'NO_EVALUABLE' ? 'OBS_NO_EVALUABLE' : 'OBS_EVALUABLE', 'fecha_hora_determinacion' => $fecha]);
            $traza = $this->insertar('trazas_inferencia', ['cod_evaluacion_criterio' => $ec, 'estado_inferencia' => 'FINALIZADA',
                'fecha_hora_inicio' => $fecha, 'fecha_hora_fin' => $fecha]);
            if ($reglas !== []) {
                foreach ($evidencias[$definicion['campo']] ?? [] as $ev) {
                    $this->insertar('evidencias_criterio_evaluacion', ['cod_evaluacion_criterio' => $ec, 'cod_evidencia_evaluacion' => $ev['id'],
                        'rol_en_criterio' => 'OBSERVACION', 'estado_participacion' => $ev['estado'], 'fecha_hora_vinculacion' => $fecha]);
                }
            }
            foreach ($reglas as $codigo => $regla) {
                $er = $this->insertar('evaluaciones_reglas', ['cod_traza_inferencia' => $traza, 'cod_regla_experta' => $regla['regla'],
                    'estado_regla' => $informe['resumen'][$codigo]['estado'], 'fecha_hora_evaluacion' => $fecha]);
                $condicion = $this->insertar('evaluaciones_condiciones_regla', ['cod_evaluacion_regla' => $er, 'cod_condicion_regla' => $regla['condicion'],
                    'estado_condicion' => $informe['resumen'][$codigo]['estado'], 'fecha_hora_evaluacion' => $fecha]);
                foreach ($evidencias[$regla['campo']] ?? [] as $ev) {
                    $this->insertar('evidencias_soporte_condicion', ['cod_evaluacion_condicion' => $condicion, 'cod_evidencia_evaluacion' => $ev['id']]);
                }
            }
            if ($estado === 'CUMPLE') {
                $this->insertar('resultados_criterio', ['cod_evaluacion_criterio' => $ec, 'cod_valor_semantico' => $definicion['valorResultado'],
                    'fecha_hora_determinacion' => $fecha, 'justificacion_original' => $definicion['hallazgo']]);
            }
        }

        return $id;
    }

    private function hecho(string $id, array $r, string $campo, bool|string|null $valor, string $evidencia, int $orden): void
    {
        $atencion = str_starts_with($r['episodio'], 'atencion:') ? substr($r['episodio'], strlen('atencion:')) : null;
        $this->insertar('hechos_instantanea_experta', ['cod_evaluacion_experta' => $id, 'id_hecho_dominio' => $r['id'].':'.$campo,
            'fuente_id_dominio' => 'controles_cognitivos', 'origen_id_dominio' => $r['id'], 'episodio_id_dominio' => $r['episodio'],
            'concepto_dominio' => $campo, 'orden' => $orden, 'tipo_origen' => 'CONTROL_COGNITIVO',
            'cod_evidencia_evaluacion' => $evidencia, 'cod_control_cognitivo' => $r['id'], 'cod_personal_autor' => $r['autor'],
            'cod_atencion' => $atencion, 'cod_actividad' => null]
            + Codec::escalar('valor', $valor) + Codec::fecha('fecha_hecho', $r['fecha']));
    }
}
