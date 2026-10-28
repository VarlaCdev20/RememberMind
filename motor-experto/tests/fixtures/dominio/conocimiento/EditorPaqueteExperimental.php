<?php

declare(strict_types=1);

namespace MotorExperto\dominio\conocimiento;

use DomainException;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorReglas;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;

/** Edición declarativa de otra revisión: nunca modifica el paquete recibido ni ejecuta código del conocimiento. */
final class EditorPaqueteExperimental
{
    public function revisar(PaqueteConocimiento $anterior, string $version, array $reglasNuevas = [], array $retiradas = []): PaqueteConocimiento
    {
        if (! $anterior->soloPruebasTecnicas || $version === $anterior->version || $version === '' || strlen($version) > 20) {
            throw new DomainException('Una revisión experimental exige otra identidad y conocimiento TEST_ONLY.');
        }
        $t = [];
        $ids = [];
        foreach (PaqueteConocimiento::CLAVES as $tabla => $pk) {
            foreach ($anterior->tabla($tabla) as $fila) {
                if (array_key_exists('cod_version_modelo', $fila)) {
                    $fila['cod_version_modelo'] = $version;
                }
                if ($tabla === 'reglas_expertas') {
                    $ids[] = $fila[$pk];
                    if (in_array($fila[$pk], $retiradas, true)) {
                        $fila['estado'] = 'INACTIVO';
                    }
                }
                $t[$tabla][] = $fila;
            }
        }
        if (array_diff($retiradas, $ids)) {
            throw new DomainException('La regla que se pretende retirar no pertenece a la revisión anterior.');
        }
        foreach ($reglasNuevas as $nueva) {
            if (! is_array($nueva) || $anterior->tipoReglaResultado === null) {
                throw new DomainException('La regla requiere definición declarativa y tipo de resultado explícito.');
            }
            foreach (['id', 'codigo', 'nombre', 'descripcion', 'fundamento', 'condiciones', 'criterio_dominio', 'valor_consecuencia'] as $campo) {
                if (! array_key_exists($campo, $nueva) || $nueva[$campo] === '' || $nueva[$campo] === []) {
                    throw new DomainException('Definición de regla incompleta: '.$campo);
                }
            }
            foreach (['id', 'codigo', 'nombre', 'descripcion', 'fundamento', 'criterio_dominio', 'valor_consecuencia'] as $campo) {
                if (! is_string($nueva[$campo]) || trim($nueva[$campo]) === '') {
                    throw new DomainException('Metadato de regla mal tipado: '.$campo);
                }
            }
            if (strlen($nueva['id']) > 20 || strlen($nueva['codigo']) > 50 || strlen($nueva['nombre']) > 160) {
                throw new DomainException('Identidad o nombre de regla fuera del contrato físico.');
            }
            if (! is_array($nueva['condiciones']) || in_array($nueva['id'], $ids, true)) {
                throw new DomainException('Regla nueva duplicada o condiciones incompatibles.');
            }
            $ids[] = $nueva['id'];
            $t['reglas_expertas'][] = ['cod_regla_experta' => $nueva['id'], 'cod_version_modelo' => $version,
                'codigo_regla' => $nueva['codigo'], 'nombre' => $nueva['nombre'], 'descripcion' => $nueva['descripcion'],
                'observacion' => $nueva['fundamento'], 'estado' => 'ACTIVO', 'tipo_regla' => $anterior->tipoReglaResultado];
            foreach ($nueva['condiciones'] as $i => $condicion) {
                if (! is_array($condicion) || count($condicion) !== 2 || array_diff(array_keys($condicion), ['variable', 'valor'])
                    || ! is_string($condicion['variable'] ?? null) || ! is_string($condicion['valor'] ?? null)) {
                    throw new DomainException('Solo se permiten condiciones positivas variable/valor.');
                }
                $t['condiciones_regla_experta'][] = ['cod_condicion_regla' => 'SIM-C-'.substr(hash('sha256', $nueva['id'].':'.$i), 0, 14),
                    'cod_regla_experta' => $nueva['id'], 'cod_variable_experta' => $condicion['variable'], 'cod_valor_semantico' => $condicion['valor']];
            }
            $t['consecuencias_regla_experta'][] = ['cod_consecuencia_regla' => 'SIM-K-'.substr(hash('sha256', $nueva['id']), 0, 14),
                'cod_regla_experta' => $nueva['id'], 'cod_criterio_dominio_resultado' => $nueva['criterio_dominio'],
                'cod_valor_semantico' => $nueva['valor_consecuencia']];
        }

        return new PaqueteConocimiento($version, $t, $anterior->contratosRelaciones, true, $anterior->tipoReglaResultado, $anterior->contratosExtraccion);
    }

    /** Compara contenido semántico de reglas, aunque la persistencia haya regenerado las PK. */
    public function comparar(PaqueteConocimiento $antes, PaqueteConocimiento $despues): array
    {
        $normalizar = static function (PaqueteConocimiento $p): array {
            $salida = [];
            foreach ($p->tabla('reglas_expertas') as $r) {
                $condiciones = $consecuencias = [];
                foreach ($p->tabla('condiciones_regla_experta') as $c) {
                    if ($c['cod_regla_experta'] !== $r['cod_regla_experta']) {
                        continue;
                    }
                    $v = $p->fila('variables_expertas', $c['cod_variable_experta']);
                    $valor = $p->fila('valores_semanticos', $c['cod_valor_semantico']);
                    $dominio = $p->fila('dominios_valores_expertos', $valor['cod_dominio_valores']);
                    $condiciones[] = ['variable' => $p->red->nodo($v['cod_nodo_semantico'])['codigo_semantico'],
                        'dominio' => $dominio['codigo_dominio'] ?? $dominio['cod_dominio_valores'], 'valor' => $valor['codigo_valor']];
                }
                foreach ($p->tabla('consecuencias_regla_experta') as $c) {
                    if ($c['cod_regla_experta'] !== $r['cod_regla_experta']) {
                        continue;
                    }
                    $criterio = $p->fila('criterios_dominios_resultado', $c['cod_criterio_dominio_resultado']);
                    $valor = $p->fila('valores_semanticos', $c['cod_valor_semantico']);
                    $dominio = $p->fila('dominios_valores_expertos', $valor['cod_dominio_valores']);
                    $consecuencias[] = ['criterio' => $p->red->nodo($criterio['cod_nodo_criterio'])['codigo_semantico'],
                        'dominio' => $dominio['codigo_dominio'] ?? $dominio['cod_dominio_valores'], 'valor' => $valor['codigo_valor']];
                }
                usort($condiciones, static fn ($a, $b) => strcmp(serialize($a), serialize($b)));
                usort($consecuencias, static fn ($a, $b) => strcmp(serialize($a), serialize($b)));
                $codigo = $r['codigo_regla'] ?? $r['cod_regla_experta'];
                if (isset($salida[$codigo])) {
                    throw new DomainException('Código de regla ambiguo.');
                }
                $salida[$codigo] = ['estado' => $r['estado'], 'tipo_regla' => $r['tipo_regla'], 'nombre' => $r['nombre'] ?? null, 'descripcion' => $r['descripcion'] ?? null,
                    'fundamento' => $r['observacion'] ?? null, 'condiciones' => $condiciones, 'consecuencias' => $consecuencias];
            }
            ksort($salida);

            return $salida;
        };
        $a = $normalizar($antes);
        $b = $normalizar($despues);
        $cambios = [];
        foreach (array_unique([...array_keys($a), ...array_keys($b)]) as $codigo) {
            if (($a[$codigo] ?? null) !== ($b[$codigo] ?? null)) {
                $cambios[$codigo] = ['antes' => $a[$codigo] ?? null, 'despues' => $b[$codigo] ?? null];
            }
        }

        return $cambios;
    }

    /** Compara la misma batería sintética; cada memoria ya usa las PK de su revisión. No guarda ni habilita conocimiento. */
    public function compararImpacto(PaqueteConocimiento $antes, PaqueteConocimiento $despues, array $casos): array
    {
        if (! $antes->soloPruebasTecnicas || ! $despues->soloPruebasTecnicas || ! array_is_list($casos) || $casos === []) {
            throw new DomainException('Se exige una batería sintética explícita para ambas revisiones.');
        }
        $resultados = [];
        foreach ($casos as $par) {
            if (! is_array($par) || ! ($par['anterior'] ?? null) instanceof MemoriaTrabajo
                || ! ($par['sucesora'] ?? null) instanceof MemoriaTrabajo
                || ! is_string($par['criterio_anterior'] ?? null) || ! is_string($par['criterio_sucesora'] ?? null)) {
                throw new DomainException('Caso de comparación mal formado.');
            }
            $a = $par['anterior'];
            $b = $par['sucesora'];
            $ca = $par['criterio_anterior'];
            $cb = $par['criterio_sucesora'];
            if (! str_starts_with($a->contexto->evaluacion, 'SIM-') || ! str_starts_with($a->contexto->residente, 'SINTETICO-')
                || $a->contexto->evaluacion !== $b->contexto->evaluacion || $a->contexto->residente !== $b->contexto->residente
                || $a->contexto->fechaCorte != $b->contexto->fechaCorte
                || $a->contexto->version !== $antes->version || $b->contexto->version !== $despues->version
                || $antes->red->nodo($ca)['codigo_semantico'] !== $despues->red->nodo($cb)['codigo_semantico']) {
                throw new DomainException('La batería mezcla casos, cortes, criterios o revisiones.');
            }
            if ($this->fuentesComparables($antes, $a, $ca) !== $this->fuentesComparables($despues, $b, $cb)) {
                throw new DomainException('Las revisiones no reciben las mismas fuentes originales.');
            }
            if (isset($resultados[$a->contexto->evaluacion])) {
                throw new DomainException('Caso duplicado en la batería.');
            }
            $ra = (new EvaluadorReglas)->evaluar($antes, $a, $ca);
            $rb = (new EvaluadorReglas)->evaluar($despues, $b, $cb);
            $resultados[$a->contexto->evaluacion] = ['anterior' => $ra, 'sucesora' => $rb];
        }

        return ['uso' => 'TEST_ONLY', 'version_anterior' => $antes->version, 'version_sucesora' => $despues->version,
            'diferencias_conocimiento' => $this->comparar($antes, $despues), 'casos' => $resultados, 'persistido' => false];
    }

    public function verificarMemoria(PaqueteConocimiento $p, MemoriaTrabajo $m, string $criterio): void
    {
        if (! $p->soloPruebasTecnicas || $m->contexto->version !== $p->version
            || ! str_starts_with($m->contexto->evaluacion, 'SIM-') || ! str_starts_with($m->contexto->residente, 'SINTETICO-')) {
            throw new DomainException('La prueba exige memoria sintética y la misma revisión.');
        }
        $this->fuentesComparables($p, $m, $criterio);
    }

    private function fuentesComparables(PaqueteConocimiento $p, MemoriaTrabajo $m, string $criterio): array
    {
        $fuentes = [];
        foreach ($m->paraCriterio($criterio) as $e) {
            $calculada = (new MapeadorSemantico)->mapear($p, $m->contexto, $e->fuente, $e->mapeoVariable);
            if ($p->red->nodo($criterio)['codigo_semantico'] === 'COG-MEM') {
                $calculada = (new AdmisibilidadFuenteCOGMEM)->validar($p, $calculada);
            }
            if (serialize($calculada) !== serialize($e)) {
                throw new DomainException('Evidencia incompatible con su fuente y mapeo de la revisión.');
            }
            $f = get_object_vars($e->fuente);
            $f['fecha'] = $e->fuente->fecha === null ? null : [
                $e->fuente->fecha->format('Y-m-d\TH:i:s.uP'), $e->fuente->fecha->getTimezone()->getName()];
            $origen = $p->fila('fuentes_datos_expertas', $e->fuente->fuente);
            $f['fuente'] = $origen['codigo_fuente'] ?? $origen['cod_fuente_dato_experta'];
            $variable = $p->fila('variables_expertas', $e->variable);
            $fuentes[] = ['fuente' => $f, 'variable' => $p->red->nodo($variable['cod_nodo_semantico'])['codigo_semantico']];
        }
        usort($fuentes, static fn ($a, $b) => strcmp(serialize($a), serialize($b)));

        return $fuentes;
    }
}
