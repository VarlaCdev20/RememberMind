<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\g09;

use MotorExperto\dominio\conocimiento\PaqueteConocimiento;

/**
 * Materializa G09-EXP como grafo/paquete D-123 exclusivamente para
 * validación cruzada del EvaluadorReglas real de RememberMind.
 *
 * Todos los nodos/valores son artificiales y NO SE PERSISTEN.
 * Los indicadores APROBADO exigidos por EvaluadorReglas corresponden
 * ÚNICAMENTE a literales SIMULADOS dentro de este paquete TEST_ONLY:
 * no representan autorización ni aprobación clínica G08/G09.
 */
final class PaqueteSemanticoG09Experimental
{
    /** Metadatos declarados de ingeniería para las tablas existentes; actor/fecha los deriva el gestor. */
    public static function paraConservacion(): PaqueteConocimiento
    {
        $p = self::crear();
        $t = $p->declaracion()['tablas'];
        foreach ($t['nodos_semanticos'] as &$fila) {
            $fila += ['nombre' => 'Prueba '.$fila['codigo_semantico'], 'definicion' => 'Nodo artificial del contrato G09 TEST_ONLY.'];
        }
        unset($fila);
        foreach ($t['relaciones_semanticas'] as &$fila) {
            $fila += ['significado' => 'Relación declarada exclusivamente para comprobación técnica G09.'];
        }
        unset($fila);
        foreach ($t['dominios_valores_expertos'] as &$fila) {
            $fila += ['codigo_dominio' => $fila['cod_dominio_valores'], 'nombre' => 'Prueba '.$fila['cod_dominio_valores'],
                'descripcion' => 'Vocabulario artificial de ingeniería; sin interpretación clínica.', 'tipo_dominio' => 'ENUMERADO'];
        }
        unset($fila);
        foreach ($t['valores_semanticos'] as &$fila) {
            $fila += ['nombre' => 'Prueba '.$fila['codigo_valor'], 'definicion_semantica' => 'Literal artificial del contrato G09 TEST_ONLY.',
                'observacion' => 'APROBADO identifica un literal sintético del evaluador; no aprobación clínica.'];
        }
        unset($fila);
        foreach ($t['variables_expertas'] as &$fila) {
            $fila += ['tipo_semantico' => 'CUALITATIVO', 'papel_inferencial' => 'INSTRUMENTAL_TEST_ONLY'];
        }
        unset($fila);
        foreach ($t['reglas_expertas'] as &$fila) {
            $codigo = $fila['cod_regla_experta'];
            $meta = CatalogoReglasG09Experimental::todas()[$codigo];
            $fila += ['codigo_regla' => $codigo, 'nombre' => $meta['descripcion'],
                'descripcion' => 'Conjunción de tres predicados de tarea artificial G09.',
                'observacion' => 'Regla candidata sintética de ingeniería; sin aprobación instrumental o clínica.'];
        }
        unset($fila);

        return new PaqueteConocimiento($p->version, $t, $p->contratosRelaciones, true, $p->tipoReglaResultado, $p->contratosExtraccion);
    }

    public static function crear(): PaqueteConocimiento
    {
        $version = CatalogoReglasG09Experimental::VERSION;
        $t = [];
        $subcomponentes = [];
        foreach (['ATE', 'EJE', 'LEN', 'VIS'] as $dominio) {
            $criterio = 'CRIT-'.$dominio;
            $domResultado = 'DOM-RESULT-'.$dominio;
            $t['nodos_semanticos'][] = [
                'cod_nodo_semantico' => $criterio,
                'codigo_semantico' => 'COG-'.$dominio,
                'tipo_nodo' => 'CRITERIO', 'estado' => 'ACTIVO',
                'cod_version_modelo' => $version,
            ];
            $t['dominios_valores_expertos'][] = [
                'cod_dominio_valores' => $domResultado,
                'cod_version_modelo' => $version, 'estado' => 'ACTIVO',
            ];
            $t['criterios_dominios_resultado'][] = [
                'cod_criterio_dominio_resultado' => 'CR-'.$dominio,
                'cod_nodo_criterio' => $criterio,
                'cod_dominio_valores' => $domResultado,
                'estado' => 'ACTIVO',
            ];
        }
        $t['dominios_valores_expertos'][] = [
            'cod_dominio_valores' => 'DOM-SIM-COND',
            'cod_version_modelo' => $version, 'estado' => 'ACTIVO',
        ];
        $t['valores_semanticos'][] = [
            'cod_valor_semantico' => 'VAL-SIM-TRUE',
            'cod_dominio_valores' => 'DOM-SIM-COND',
            'codigo_valor' => 'SIMULADO_TRUE',
            'estado' => 'ACTIVO', 'estado_aprobacion' => 'APROBADO',
        ];
        foreach (CatalogoReglasG09Experimental::todas() as $regla => $meta) {
            $dominio = substr($meta['criterio'], 4);
            $criterio = 'CRIT-'.$dominio;
            $sub = 'SUB-'.$meta['componente'];
            if (! isset($subcomponentes[$sub])) {
                $subcomponentes[$sub] = true;
                $t['nodos_semanticos'][] = [
                    'cod_nodo_semantico' => $sub,
                    'codigo_semantico' => $meta['componente'],
                    'tipo_nodo' => 'SUBCOMPONENTE',
                    'cod_version_modelo' => $version, 'estado' => 'ACTIVO',
                ];
                $t['relaciones_semanticas'][] = [
                    'cod_relacion_semantica' => 'REL-SUB-'.$meta['componente'],
                    'cod_nodo_origen' => $sub,
                    'cod_nodo_destino' => $criterio,
                    'tipo_relacion' => 'SUBCOMPONENTE_DE',
                    'modalidad_relacion' => null,
                    'cod_version_modelo' => $version, 'estado' => 'ACTIVO',
                ];
            }
            $t['reglas_expertas'][] = [
                'cod_regla_experta' => $regla,
                'cod_version_modelo' => $version,
                'tipo_regla' => 'RESULTADO', 'estado' => 'ACTIVO',
            ];
            $t['valores_semanticos'][] = [
                'cod_valor_semantico' => 'VAL-RES-'.$regla,
                'cod_dominio_valores' => 'DOM-RESULT-'.$dominio,
                'codigo_valor' => $meta['consecuencia'],
                'estado' => 'ACTIVO', 'estado_aprobacion' => 'APROBADO',
            ];
            $t['consecuencias_regla_experta'][] = [
                'cod_consecuencia_regla' => 'CONS-'.$regla,
                'cod_regla_experta' => $regla,
                'cod_criterio_dominio_resultado' => 'CR-'.$dominio,
                'cod_valor_semantico' => 'VAL-RES-'.$regla,
            ];
            foreach (['C1', 'C2', 'C3'] as $cond) {
                $variable = 'VAR-'.$regla.'-'.$cond;
                $nodo = 'NODO-'.$regla.'-'.$cond;
                $t['nodos_semanticos'][] = [
                    'cod_nodo_semantico' => $nodo,
                    'codigo_semantico' => $variable,
                    'tipo_nodo' => 'VARIABLE',
                    'cod_version_modelo' => $version,
                    'estado' => 'ACTIVO',
                ];
                $t['variables_expertas'][] = [
                    'cod_variable_experta' => $variable,
                    'cod_version_modelo' => $version,
                    'cod_nodo_semantico' => $nodo,
                    'cod_nodo_propietario_primario' => $sub,
                    'cod_dominio_valores' => 'DOM-SIM-COND',
                    'estado' => 'ACTIVO',
                ];
                $t['relaciones_semanticas'][] = [
                    'cod_relacion_semantica' => 'REL-VAR-'.$regla.'-'.$cond,
                    'cod_version_modelo' => $version,
                    'cod_nodo_origen' => $nodo,
                    'cod_nodo_destino' => $criterio,
                    'tipo_relacion' => 'SIM_APORTA',
                    'modalidad_relacion' => null,
                    'estado' => 'ACTIVO',
                ];
                $t['condiciones_regla_experta'][] = [
                    'cod_condicion_regla' => 'COND-'.$regla.'-'.$cond,
                    'cod_regla_experta' => $regla,
                    'cod_variable_experta' => $variable,
                    'cod_valor_semantico' => 'VAL-SIM-TRUE',
                ];
            }
        }

        return new PaqueteConocimiento($version, $t, [
            [
                'tipo_relacion' => 'SUBCOMPONENTE_DE',
                'tipo_origen' => 'SUBCOMPONENTE',
                'tipo_destino' => 'CRITERIO',
                'modalidades' => [null],
            ],
            [
                'tipo_relacion' => 'SIM_APORTA',
                'tipo_origen' => 'VARIABLE',
                'tipo_destino' => 'CRITERIO',
                'modalidades' => [null],
                'habilita_participacion' => true,
            ],
        ], true, 'RESULTADO');
    }
}
