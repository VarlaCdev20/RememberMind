<?php

namespace App\Modulos\Valoraciones\Dominio\Servicios;

/**
 * Lectura técnica y conservadora. NUNCA emite C03=S ni diagnostica DCL/demencia.
 * Recibe historial de filas de la tabla, sin depender de Eloquent ni de HTTP.
 */
final class AnalizadorIadlC03
{
    public function analizar(array $historial): array
    {
        $rectificados = [];
        foreach ($historial as $fila) {
            if (!empty($fila['cod_control_anterior'])) {
                $rectificados[(string) $fila['cod_control_anterior']] = true;
            }
        }
        $vigentes = [];
        foreach ($historial as $fila) {
            if (isset($rectificados[(string) ($fila['cod_control_iadl'] ?? '')])) {
                continue;
            }
            if (!in_array($fila['estado'] ?? null, ['REGISTRADO','RECTIFICACION'], true)) {
                continue;
            }
            $actividad = $fila['actividad'] ?? null;
            $fecha = $fila['fecha_hora'] ?? null;
            if (!is_string($actividad) || !is_string($fecha)) {
                continue;
            }
            $clave = $fecha.'|'.(string) ($fila['registrado_en'] ?? '').'|'.(string) ($fila['cod_control_iadl'] ?? '');
            if (!isset($vigentes[$actividad]) || strcmp($clave, $vigentes[$actividad]['_orden']) > 0) {
                $vigentes[$actividad] = $fila + ['_orden' => $clave];
            }
        }
        ksort($vigentes, SORT_STRING);
        $observadas = 0;
        $independientes = 0;
        $ayudasCognitivas = 0;
        $ayudasOtras = 0;
        $sinOportunidad = 0;
        $pendientes = [];
        $resumen = [];
        foreach ($vigentes as $actividad => $fila) {
            $o = $fila['oportunidad'] ?? null;
            $d = $fila['desempeno'] ?? null;
            $causa = $fila['causa_ayuda'] ?? null;
            if (in_array($o, ['SIN_OPORTUNIDAD','NO_HABITUAL'], true)) {
                $sinOportunidad++;
                $pendientes[] = $actividad.': no se pudo demostrar capacidad ni dependencia';
            } elseif (in_array($o, ['OBSERVADA','REFERIDA'], true)) {
                $observadas++;
                if ($d === 'INDEPENDIENTE') {
                    $independientes++;
                } elseif ($d === 'CON_APOYO' && in_array($causa, ['COGNITIVA','MIXTA'], true)) {
                    $ayudasCognitivas++;
                } elseif ($d === 'CON_APOYO' && in_array($causa, ['FISICA','SENSORIAL','INSTITUCIONAL'], true)) {
                    $ayudasOtras++;
                } else {
                    $pendientes[] = $actividad.': causa de ayuda sin adjudicar';
                }
            }
            $resumen[$actividad] = [
                'oportunidad'=>$o, 'desempeno'=>$d, 'causa_ayuda'=>$causa,
                'fuente'=>$fila['fuente'] ?? null,
                'nivel_previo'=>$fila['nivel_previo'] ?? null,
                'fecha_hora'=>$fila['fecha_hora'],
            ];
        }
        $estado = $ayudasCognitivas > 0
            ? 'DEPENDENCIA_COGNITIVA_REPORTADA_REQUIERE_REVISION'
            : ($observadas === 0 ? 'SIN_COBERTURA_FUNCIONAL'
            : ($pendientes ? 'COBERTURA_PARCIAL_O_CAUSA_INCIERTA'
            : 'ACTIVIDADES_DOCUMENTADAS_SIN_DEPENDENCIA_COGNITIVA_ATRIBUIDA'));

        return [
            'tipo' => 'SINTESIS_FUNCIONAL_DESCRIPTIVA',
            'estado_tecnico' => $estado,
            'actividades_registradas' => count($vigentes),
            'actividades_con_oportunidad' => $observadas,
            'actividades_independientes' => $independientes,
            'ayudas_con_atribucion_cognitiva_o_mixta' => $ayudasCognitivas,
            'ayudas_por_otras_causas' => $ayudasOtras,
            'actividades_sin_oportunidad' => $sinOportunidad,
            'pendientes' => $pendientes,
            'detalle' => $resumen,
            'criterio_dcl_c03' => 'U',
            'requiere_revision_profesional_c03' => true,
            'motivo_c03' => 'C03 exige valorar independencia relativa y cambios respecto al nivel previo; registrar IADL no acredita por sí solo el criterio.',
            'limite' => 'No interpretar dependencia institucional, discapacidad física ni falta de oportunidad como demencia. No emite DCL ni diagnóstico.',
        ];
    }
}
