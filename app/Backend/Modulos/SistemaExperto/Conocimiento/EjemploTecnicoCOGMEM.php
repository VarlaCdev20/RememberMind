<?php

namespace App\Backend\Modulos\SistemaExperto\Conocimiento;

use App\Backend\Modulos\SistemaExperto\DTO\ContextoEjecucion;
use App\Backend\Modulos\SistemaExperto\DTO\FuenteBrutaCOGMEM;
use DateTimeImmutable;

/** Conocimiento artificial en memoria. No se siembra ni se activa en la institución. */
final class EjemploTecnicoCOGMEM
{
    public static function contexto(): ContextoEjecucion
    {
        return new ContextoEjecucion('EVAL_TEST', 'RES_TEST', 'VER_TEST', new DateTimeImmutable('2026-10-08T12:00:00-04:00'));
    }

    public static function contextoClinico(): array
    {
        return ['componente_validado' => true, 'condiciones_aplicacion_conocidas' => true, 'confusores_revisados' => true];
    }

    public static function tablas(): array
    {
        $t = [];
        $conceptos = ['MEM' => ['COG-MEM', 'CRITERIO'], 'SUB' => ['MEM-S01', 'SUBCOMPONENTE'],
            'OBS' => ['VAR-MEM-OBS-01', 'VARIABLE'], 'INS' => ['VAR-MEM-INST-01', 'VARIABLE'],
            'COR' => ['VAR-MEM-COR-01', 'VARIABLE'], 'MULTI' => ['VAR-COG-MULTI-01', 'VARIABLE'],
            'META' => ['VAR-LON-META-01', 'VARIABLE'], 'LON' => ['BL-LON', 'BLOQUE']];
        foreach ($conceptos as $id => [$codigo, $tipo]) {
            $t['nodos_semanticos'][] = ['cod_nodo_semantico' => $id, 'cod_version_modelo' => 'VER_TEST', 'codigo_semantico' => $codigo,
                'tipo_nodo' => $tipo, 'estado' => 'ACTIVO', 'nombre' => 'Fixture '.$codigo, 'definicion' => 'Contenido técnico de prueba'];
        }
        $t['relaciones_semanticas'] = [['cod_relacion_semantica' => 'REL_SUB', 'cod_version_modelo' => 'VER_TEST', 'cod_nodo_origen' => 'SUB',
            'cod_nodo_destino' => 'MEM', 'tipo_relacion' => 'SUBCOMPONENTE_DE', 'modalidad_relacion' => null, 'estado' => 'ACTIVO',
            'significado' => 'Contrato candidato usado solo en prueba', 'cod_usuario_creacion' => 'USR_TEST', 'fecha_hora_creacion' => '2026-10-08 12:00:00']];
        foreach (['OBS', 'INS', 'COR', 'MULTI'] as $id) {
            $t['relaciones_semanticas'][] = ['cod_relacion_semantica' => 'REL_'.$id, 'cod_version_modelo' => 'VER_TEST', 'cod_nodo_origen' => $id,
                'cod_nodo_destino' => 'MEM', 'tipo_relacion' => 'TEST_APORTA', 'modalidad_relacion' => null, 'estado' => 'ACTIVO',
                'significado' => 'Vínculo explícito de fixture técnico; no catálogo institucional'];
        }
        foreach (['DOM_VAR', 'DOM_RES'] as $id) {
            $t['dominios_valores_expertos'][] = ['cod_dominio_valores' => $id, 'cod_version_modelo' => 'VER_TEST', 'estado' => 'ACTIVO'];
        }
        foreach (['VAL_A' => ['DOM_VAR', 'TEST_A'], 'VAL_B' => ['DOM_VAR', 'TEST_B'],
            'RES_D' => ['DOM_RES', 'DIFICULTAD_EVIDENCIADA'], 'RES_S' => ['DOM_RES', 'SIN_DIFICULTAD_EVIDENCIADA'], 'RES_M' => ['DOM_RES', 'HALLAZGOS_MIXTOS']] as $id => [$dominio, $codigo]) {
            $t['valores_semanticos'][] = ['cod_valor_semantico' => $id, 'cod_dominio_valores' => $dominio, 'codigo_valor' => $codigo, 'estado' => 'ACTIVO', 'estado_aprobacion' => 'APROBADO'];
        }
        foreach (['OBS', 'INS', 'COR', 'MULTI', 'META'] as $id) {
            $t['variables_expertas'][] = ['cod_variable_experta' => 'VAR_'.$id, 'cod_version_modelo' => 'VER_TEST', 'cod_nodo_semantico' => $id,
                'cod_nodo_propietario_primario' => $id === 'META' ? 'LON' : ($id === 'MULTI' ? null : 'MEM'), 'cod_dominio_valores' => 'DOM_VAR', 'estado' => 'ACTIVO'];
        }
        foreach (['FU_CTRL' => 'controles_cognitivos', 'FU_INST' => 'aplicaciones_instrumento'] as $id => $tabla) {
            $t['fuentes_datos_expertas'][] = ['cod_fuente_dato_experta' => $id, 'cod_version_modelo' => 'VER_TEST', 'tabla_raiz' => $tabla, 'estado' => 'ACTIVO',
                'campo_pk_raiz' => $id === 'FU_CTRL' ? 'cod_control_cognitivo' : 'cod_aplicacion', 'campo_residente' => 'cod_residente', 'campo_temporal' => 'fecha_hora', 'campo_personal' => 'cod_personal'];
        }
        foreach (['OBS' => 'memoria_reciente', 'INS' => 'COMP_TEST', 'COR' => 'repite_preguntas', 'MULTI' => 'olvida_indicaciones', 'META' => 'cambio_cognitivo'] as $id => $campo) {
            $t['mapeos_variables_fuente'][] = ['cod_mapeo_variable_fuente' => 'MAP_'.$id, 'cod_version_modelo' => 'VER_TEST', 'cod_variable_experta' => 'VAR_'.$id,
                'cod_fuente_dato_experta' => $id === 'INS' ? 'FU_INST' : 'FU_CTRL', 'tipo_extraccion' => $id === 'INS' ? 'TEST_COMPONENTE' : 'TEST_CAMPO', 'campo_valor' => $id === 'INS' ? null : $campo,
                'clave_selector' => $id === 'INS' ? $campo : null, 'estado' => 'ACTIVO'];
            foreach (['A', 'B'] as $v) {
                $t['mapeos_valores_fuente'][] = ['cod_mapeo_valor_fuente' => 'MV_'.$id.'_'.$v, 'cod_mapeo_variable_fuente' => 'MAP_'.$id, 'cod_valor_semantico' => 'VAL_'.$v,
                    'tipo_valor_fuente' => 'string', 'valor_fuente_exacto' => 'literal_'.$v, 'estado_aprobacion' => 'APROBADO', 'estado' => 'ACTIVO',
                    'fecha_hora_vigencia_desde' => null, 'fecha_hora_vigencia_hasta' => null];
            }
        }
        $t['criterios_dominios_resultado'] = [['cod_criterio_dominio_resultado' => 'CR_DOM', 'cod_nodo_criterio' => 'MEM', 'cod_dominio_valores' => 'DOM_RES', 'estado' => 'ACTIVO']];
        foreach (['A' => 'RES_D', 'B' => 'RES_S'] as $id => $resultado) {
            $t['reglas_expertas'][] = ['cod_regla_experta' => 'RULE_'.$id, 'cod_version_modelo' => 'VER_TEST', 'tipo_regla' => 'RESULTADO', 'estado' => 'ACTIVO'];
            $t['condiciones_regla_experta'][] = ['cod_condicion_regla' => 'COND_'.$id, 'cod_regla_experta' => 'RULE_'.$id, 'cod_variable_experta' => 'VAR_INS', 'cod_valor_semantico' => 'VAL_'.$id];
            $t['consecuencias_regla_experta'][] = ['cod_consecuencia_regla' => 'CONS_'.$id, 'cod_regla_experta' => 'RULE_'.$id, 'cod_criterio_dominio_resultado' => 'CR_DOM', 'cod_valor_semantico' => $resultado];
        }

        return $t;
    }

    public static function paquete(?array $tablas = null, bool $soloPruebas = true): PaqueteConocimiento
    {
        return new PaqueteConocimiento('VER_TEST', $tablas ?? self::tablas(), [
            ['tipo_relacion' => 'SUBCOMPONENTE_DE', 'tipo_origen' => 'SUBCOMPONENTE', 'tipo_destino' => 'CRITERIO', 'modalidades' => [null]],
            ['tipo_relacion' => 'TEST_APORTA', 'tipo_origen' => 'VARIABLE', 'tipo_destino' => 'CRITERIO', 'modalidades' => [null], 'habilita_participacion' => true],
        ], $soloPruebas, tipoReglaResultado: 'RESULTADO', contratosExtraccion: ['TEST_CAMPO' => 'CAMPO_DIRECTO', 'TEST_COMPONENTE' => 'SELECTOR_COMPONENTE']);
    }

    public static function fuente(mixed $valor = 'literal_A', string $campo = 'COMP_TEST', string $registro = 'APP_TEST', array $contexto = []): FuenteBrutaCOGMEM
    {
        $ins = $campo === 'COMP_TEST';

        return new FuenteBrutaCOGMEM($ins ? 'FU_INST' : 'FU_CTRL', $ins ? 'aplicaciones_instrumento' : 'controles_cognitivos', $campo,
            $registro, 'RES_TEST', new DateTimeImmutable('2026-10-08T11:00:00-04:00'), $valor, $valor, 'PER_TEST', true, true,
            contexto: $ins ? ['componente_instrumental_verificado' => true, ...$contexto] : $contexto);
    }
}
