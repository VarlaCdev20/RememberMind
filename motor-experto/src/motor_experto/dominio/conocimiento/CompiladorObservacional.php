<?php

declare(strict_types=1);

namespace MotorExperto\dominio\conocimiento;

use DomainException;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorObservaciones as Motor;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Conocimiento observacional versionado.
 * Compila únicamente las implicaciones booleanas descriptivas ya representadas.
 * No ejecuta expresiones de metadatos, interpreta instrumentos ni añade reglas clínicas.
 */
final class CompiladorObservacional
{
    /** $ejecutadas congela la selección histórica; una revisión posterior no cambia su causalidad. */
    public function compilar(array $tablas, string $version, string $codigoVersion, ?array $ejecutadas = null): array
    {
        $seleccionar = static function (string $tabla, callable $filtro) use ($tablas): array {
            $filas = array_values(array_filter($tablas[$tabla] ?? [], $filtro));
            if (count($filas) !== 1) {
                throw new DomainException('Conocimiento observacional incompleto o ambiguo: '.$tabla);
            }

            return $filas[0];
        };
        foreach ($tablas as $filas) {
            foreach ($filas as $fila) {
                if (isset($fila['cod_version_modelo']) && $fila['cod_version_modelo'] !== $version) {
                    throw new DomainException('Conocimiento observacional de otra versión.');
                }
            }
        }
        $activo = static function (array $fila): array {
            if (($fila['estado'] ?? 'ACTIVO') !== 'ACTIVO') {
                throw new DomainException('Una dependencia observacional está inactiva.');
            }

            return $fila;
        };
        $fuente = $activo($seleccionar('fuentes_datos_expertas', fn ($f) => $f['codigo_fuente'] === 'OBS-CONTROL'));
        foreach (['tipo_fuente' => 'OPERATIVA', 'tabla_raiz' => 'controles_cognitivos', 'campo_pk_raiz' => 'cod_control_cognitivo',
            'campo_residente' => 'cod_residente', 'campo_temporal' => 'fecha_hora', 'campo_personal' => 'cod_personal',
            'campo_estado' => 'estado', 'clave_adaptador' => 'OBS_LITERAL'] as $campo => $valor) {
            if (($fuente[$campo] ?? null) !== $valor) {
                throw new DomainException('Fuente fuera del contrato observacional: '.$campo);
            }
        }
        $entrada = $activo($seleccionar('dominios_valores_expertos', fn ($d) => $d['codigo_dominio'] === 'OBS-LOGICO'));
        $salida = $activo($seleccionar('dominios_valores_expertos', fn ($d) => $d['codigo_dominio'] === 'OBS-HALLAZGO'));
        if ($entrada['tipo_dominio'] !== 'CATEGORICO' || $salida['tipo_dominio'] !== 'CATEGORICO') {
            throw new DomainException('Dominios observacionales incompatibles.');
        }
        $valores = [];
        foreach (['SI', 'NO'] as $literal) {
            $v = $activo($seleccionar('valores_semanticos', fn ($v) => $v['cod_dominio_valores'] === $entrada['cod_dominio_valores'] && $v['codigo_valor'] === $literal));
            if ($v['estado_aprobacion'] !== 'APROBADO') {
                throw new DomainException('Valor lógico sin contrato de representación.');
            }
            $valores[$literal] = $v['cod_valor_semantico'];
        }
        $campos = $criterios = [];
        foreach (Motor::REGLAS as $codigo => $literal) {
            $campo = $literal['campo'];
            $nodo = $activo($seleccionar('nodos_semanticos', fn ($n) => $n['codigo_semantico'] === 'OBS:'.$campo && $n['tipo_nodo'] === 'VARIABLE'));
            $criterio = $activo($seleccionar('nodos_semanticos', fn ($n) => $n['codigo_semantico'] === $codigo && $n['tipo_nodo'] === 'CRITERIO'));
            $variable = $activo($seleccionar('variables_expertas', fn ($v) => $v['cod_nodo_semantico'] === $nodo['cod_nodo_semantico']));
            $mapeo = $activo($seleccionar('mapeos_variables_fuente', fn ($m) => $m['cod_variable_experta'] === $variable['cod_variable_experta']));
            if ($variable['cod_nodo_propietario_primario'] !== $criterio['cod_nodo_semantico']
                || $variable['cod_dominio_valores'] !== $entrada['cod_dominio_valores']
                || $variable['tipo_semantico'] !== 'OBSERVACIONAL' || $variable['papel_inferencial'] !== 'DESCRIPTIVO'
                || $mapeo['cod_fuente_dato_experta'] !== $fuente['cod_fuente_dato_experta']
                || $mapeo['tipo_extraccion'] !== 'CAMPO_DIRECTO' || $mapeo['campo_valor'] !== $campo || $mapeo['clave_selector'] !== null) {
                throw new DomainException('Variable o mapeo fuera del contrato observacional.');
            }
            foreach (['true' => 'SI', 'false' => 'NO'] as $bruto => $valor) {
                $mapa = $activo($seleccionar('mapeos_valores_fuente', fn ($m) => $m['cod_mapeo_variable_fuente'] === $mapeo['cod_mapeo_variable_fuente']
                    && $m['tipo_valor_fuente'] === 'bool' && $m['valor_fuente_exacto'] === $bruto));
                if ($mapa['cod_valor_semantico'] !== $valores[$valor] || $mapa['estado_aprobacion'] !== 'APROBADO'
                    || $mapa['fecha_hora_vigencia_desde'] !== null || $mapa['fecha_hora_vigencia_hasta'] !== null) {
                    throw new DomainException('Representación lógica observacional incompatible.');
                }
            }
            $dominio = $activo($seleccionar('criterios_dominios_resultado', fn ($d) => $d['cod_nodo_criterio'] === $criterio['cod_nodo_semantico']));
            $consecuencia = $activo($seleccionar('valores_semanticos', fn ($v) => $v['cod_dominio_valores'] === $salida['cod_dominio_valores'] && $v['codigo_valor'] === $codigo));
            if ($dominio['cod_dominio_valores'] !== $salida['cod_dominio_valores'] || $consecuencia['estado_aprobacion'] !== 'APROBADO'
                || $consecuencia['definicion_semantica'] !== $literal['hallazgo']) {
                throw new DomainException('Consecuencia fuera del contrato descriptivo autorizado.');
            }
            $campos[$campo] = ['campo' => $campo, 'variable' => $variable['cod_variable_experta'], 'mapeo' => $mapeo['cod_mapeo_variable_fuente'],
                'fuente' => $fuente['cod_fuente_dato_experta'], 'criterio' => $criterio['cod_nodo_semantico']];
            $criterios[$criterio['cod_nodo_semantico']] = ['campo' => $campo, 'dominio' => $dominio['cod_criterio_dominio_resultado'],
                'valorResultado' => $consecuencia['cod_valor_semantico'], 'hallazgo' => $literal['hallazgo']];
        }
        $reglas = [];
        $seleccionadas = [];
        foreach ($tablas['reglas_expertas'] ?? [] as $r) {
            $id = $r['cod_regla_experta'];
            if ($ejecutadas === null ? ($r['estado'] !== 'ACTIVO' || ($r['estado_revision'] ?? null) === 'RECHAZADA') : ! in_array($id, $ejecutadas, true)) {
                continue;
            }
            $condiciones = array_values(array_filter($tablas['condiciones_regla_experta'] ?? [], fn ($c) => $c['cod_regla_experta'] === $id));
            $consecuencias = array_values(array_filter($tablas['consecuencias_regla_experta'] ?? [], fn ($c) => $c['cod_regla_experta'] === $id));
            if (count($condiciones) !== 1 || count($consecuencias) !== 1 || $r['tipo_regla'] !== 'OBSERVACIONAL') {
                throw new DomainException('Este ejecutor requiere una condición observacional positiva por regla.');
            }
            $c = $condiciones[0];
            $f = array_values(array_filter($campos, fn ($f) => $f['variable'] === $c['cod_variable_experta']));
            if (count($f) !== 1 || $c['cod_valor_semantico'] !== $valores['SI']) {
                throw new DomainException('La condición no corresponde a un indicador afirmativo autorizado.');
            }
            $f = $f[0];
            $criterio = $criterios[$f['criterio']];
            if ($consecuencias[0]['cod_criterio_dominio_resultado'] !== $criterio['dominio']
                || $consecuencias[0]['cod_valor_semantico'] !== $criterio['valorResultado'] || isset($reglas[$r['codigo_regla']])) {
                throw new DomainException('Consecuencia o identidad de regla observacional incompatible.');
            }
            $reglas[$r['codigo_regla']] = $f + ['regla' => $id, 'condicion' => $c['cod_condicion_regla'],
                'nombre' => $r['nombre'], 'hallazgo' => $criterio['hallazgo'], 'valorResultado' => $criterio['valorResultado'],
                'descripcion' => $r['descripcion'], 'fundamento' => $r['observacion'], 'autor' => $r['cod_usuario_creacion'],
                'fecha_creacion' => $r['fecha_hora_creacion'], 'anterior' => $r['cod_regla_anterior'] ?? null];
            $seleccionadas[] = $id;
        }
        if ($ejecutadas !== null && (count($ejecutadas) !== count(array_unique($ejecutadas)) || array_diff($ejecutadas, $seleccionadas))) {
            throw new DomainException('La selección histórica de reglas es incompatible con su versión.');
        }
        $orden = array_flip(array_keys($campos));
        uasort($reglas, fn ($a, $b) => ($orden[$a['campo']] <=> $orden[$b['campo']]) ?: strcmp($a['regla'], $b['regla']));

        return ['version' => $version, 'codigo' => $codigoVersion, 'contrato' => 'OBS-SNAP-2',
            'campos' => $campos, 'criterios' => $criterios, 'reglas' => $reglas, 'valores' => $valores];
    }
}
