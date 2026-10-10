<?php

namespace App\Backend\Modulos\SistemaExperto\Conocimiento;

use DateTimeImmutable;
use DomainException;

final class ValidadorPaqueteConocimiento
{
    public function validar(PaqueteConocimiento $p): void
    {
        foreach (PaqueteConocimiento::CLAVES as $tabla => $pk) {
            foreach ($p->tabla($tabla) as $r) {
                if (isset($r['cod_version_modelo']) && $r['cod_version_modelo'] !== $p->version) {
                    throw new DomainException('Conocimiento de otra versión: '.$tabla);
                }
            }
        }
        foreach ($p->tabla('variables_expertas') as $v) {
            if ($p->red->nodo($v['cod_nodo_semantico'])['tipo_nodo'] !== 'VARIABLE') {
                throw new DomainException('Una variable experta requiere un nodo de tipo VARIABLE.');
            }
            if ($v['cod_nodo_propietario_primario'] !== null) {
                $p->red->nodo($v['cod_nodo_propietario_primario']);
            }
            if ($v['cod_dominio_valores'] !== null) {
                $this->dominio($p, $v['cod_dominio_valores']);
            }
        }
        foreach ($p->tabla('valores_semanticos') as $v) {
            $this->dominio($p, $v['cod_dominio_valores']);
        }
        $equivalentes = [];
        foreach ($p->tabla('mapeos_variables_fuente') as $m) {
            $p->fila('variables_expertas', $m['cod_variable_experta']);
            $f = $p->fila('fuentes_datos_expertas', $m['cod_fuente_dato_experta']);
            if ($f['cod_version_modelo'] !== $p->version) {
                throw new DomainException('Fuente de otra versión.');
            }
            $modo = $p->contratosExtraccion[$m['tipo_extraccion']] ?? null;
            if ($m['estado'] === 'ACTIVO') {
                $correcto = ($modo === 'CAMPO_DIRECTO' && $m['campo_valor'] !== null && $m['campo_valor'] !== '' && $m['clave_selector'] === null)
                    || ($modo === 'SELECTOR_COMPONENTE' && $m['campo_valor'] === null && $m['clave_selector'] !== null && $m['clave_selector'] !== '');
                if (! $correcto) {
                    throw new DomainException('Modo/campo/selector de extracción sin contrato único compatible.');
                }
            }
            $key = serialize([$m['cod_variable_experta'], $m['cod_fuente_dato_experta'], $modo, $m['campo_valor'] ?? $m['clave_selector']]);
            if ($m['estado'] === 'ACTIVO') {
                if (isset($equivalentes[$key])) {
                    throw new DomainException('Mapeos fuente-variable activos equivalentes.');
                }
                $equivalentes[$key] = true;
            }
        }
        foreach ($p->tabla('mapeos_valores_fuente') as $m) {
            $v = $p->fila('variables_expertas', $p->fila('mapeos_variables_fuente', $m['cod_mapeo_variable_fuente'])['cod_variable_experta']);
            $valor = $p->fila('valores_semanticos', $m['cod_valor_semantico']);
            if ($v['cod_dominio_valores'] === null || $v['cod_dominio_valores'] !== $valor['cod_dominio_valores']) {
                throw new DomainException('Mapeo de valor incompatible con dominio de variable.');
            }
            if ($m['fecha_hora_vigencia_desde'] !== null && $m['fecha_hora_vigencia_hasta'] !== null
                && new DateTimeImmutable($m['fecha_hora_vigencia_desde']) > new DateTimeImmutable($m['fecha_hora_vigencia_hasta'])) {
                throw new DomainException('Intervalo de vigencia invertido.');
            }
            foreach ($p->tabla('mapeos_valores_fuente') as $otro) {
                if ($m['cod_mapeo_valor_fuente'] >= $otro['cod_mapeo_valor_fuente'] || $m['estado'] !== 'ACTIVO' || $otro['estado'] !== 'ACTIVO') {
                    continue;
                }
                if ($m['cod_mapeo_variable_fuente'] === $otro['cod_mapeo_variable_fuente'] && $m['tipo_valor_fuente'] === $otro['tipo_valor_fuente']
                    && $m['valor_fuente_exacto'] === $otro['valor_fuente_exacto'] && $m['cod_valor_semantico'] !== $otro['cod_valor_semantico']
                    && $this->superpuestos($m, $otro)) {
                    throw new DomainException('Vigencias de mapeos exactos con destinos incompatibles.');
                }
            }
        }
        foreach ($p->tabla('criterios_dominios_resultado') as $c) {
            if ($p->red->nodo($c['cod_nodo_criterio'])['tipo_nodo'] !== 'CRITERIO') {
                throw new DomainException('El dominio de resultado requiere un nodo CRITERIO.');
            }
            $this->dominio($p, $c['cod_dominio_valores']);
            $p->dominioCriterio($c['cod_nodo_criterio']);
        }
        $atomic = [];
        foreach ($p->tabla('condiciones_regla_experta') as $c) {
            $p->fila('reglas_expertas', $c['cod_regla_experta']);
            $v = $p->fila('variables_expertas', $c['cod_variable_experta']);
            $valor = $p->fila('valores_semanticos', $c['cod_valor_semantico']);
            if ($v['cod_dominio_valores'] === null || $v['cod_dominio_valores'] !== $valor['cod_dominio_valores']) {
                throw new DomainException('Condición incompatible con dominio de variable.');
            }
            $key = serialize([$c['cod_regla_experta'], $c['cod_variable_experta'], $c['cod_valor_semantico']]);
            if (isset($atomic[$key])) {
                throw new DomainException('Condición atómica duplicada D-130.');
            }
            $atomic[$key] = true;
        }
        $destinos = [];
        foreach ($p->tabla('consecuencias_regla_experta') as $c) {
            $p->fila('reglas_expertas', $c['cod_regla_experta']);
            $d = $p->fila('criterios_dominios_resultado', $c['cod_criterio_dominio_resultado']);
            $v = $p->fila('valores_semanticos', $c['cod_valor_semantico']);
            if ($v['cod_dominio_valores'] !== $d['cod_dominio_valores'] || $v['codigo_valor'] === 'HALLAZGOS_MIXTOS') {
                throw new DomainException('Consecuencia incompatible o reservada al resolvedor.');
            }
            $key = serialize([$c['cod_regla_experta'], $c['cod_criterio_dominio_resultado']]);
            if (isset($destinos[$key])) {
                throw new DomainException('Consecuencia duplicada por regla y criterio.');
            }
            $destinos[$key] = true;
        }
        foreach ($p->tabla('reglas_expertas') as $r) {
            if ($r['estado'] !== 'ACTIVO' || $r['tipo_regla'] !== $p->tipoReglaResultado) {
                continue;
            }
            $condiciones = array_filter($p->tabla('condiciones_regla_experta'), fn ($c) => $c['cod_regla_experta'] === $r['cod_regla_experta']);
            $consecuencias = array_filter($p->tabla('consecuencias_regla_experta'), fn ($c) => $c['cod_regla_experta'] === $r['cod_regla_experta']);
            if (! $condiciones || ! $consecuencias) {
                throw new DomainException('Regla activa de resultado incompleta.');
            }
        }
    }

    private function dominio(PaqueteConocimiento $p, string $id): void
    {
        if ($p->fila('dominios_valores_expertos', $id)['cod_version_modelo'] !== $p->version) {
            throw new DomainException('Dominio de otra versión.');
        }
    }

    private function superpuestos(array $a, array $b): bool
    {
        return ($a['fecha_hora_vigencia_hasta'] === null || $b['fecha_hora_vigencia_desde'] === null || new DateTimeImmutable($a['fecha_hora_vigencia_hasta']) >= new DateTimeImmutable($b['fecha_hora_vigencia_desde']))
            && ($b['fecha_hora_vigencia_hasta'] === null || $a['fecha_hora_vigencia_desde'] === null || new DateTimeImmutable($b['fecha_hora_vigencia_hasta']) >= new DateTimeImmutable($a['fecha_hora_vigencia_desde']));
    }
}
