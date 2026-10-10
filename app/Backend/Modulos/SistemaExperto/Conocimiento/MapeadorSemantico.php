<?php

namespace App\Backend\Modulos\SistemaExperto\Conocimiento;

use App\Backend\Modulos\SistemaExperto\DTO\ContextoEjecucion;
use App\Backend\Modulos\SistemaExperto\DTO\EvidenciaExperta;
use App\Backend\Modulos\SistemaExperto\DTO\FuenteBrutaCOGMEM;
use App\Backend\Modulos\SistemaExperto\Evaluadores\EvaluadorAdmisibilidad;
use DateTimeImmutable;
use DomainException;

final class MapeadorSemantico
{
    /** Comparación estricta tipada. Serialización técnica explícita, sin normalizar texto. */
    private function literal(mixed $valor): array
    {
        return match (true) {
            is_string($valor) => ['string', $valor],
            is_bool($valor) => ['boolean', $valor ? 'true' : 'false'],
            is_int($valor) => ['integer', (string) $valor],
            default => throw new DomainException('Tipo fuente sin serialización exacta autorizada.'),
        };
    }

    public function mapear(PaqueteConocimiento $p, ContextoEjecucion $c, FuenteBrutaCOGMEM $f, string $mapeoId): EvidenciaExperta
    {
        if ($p->version !== $c->version) {
            throw new DomainException('Paquete de otra ejecución/version.');
        }
        $m = $p->fila('mapeos_variables_fuente', $mapeoId);
        $s = $p->fila('fuentes_datos_expertas', $m['cod_fuente_dato_experta']);
        $v = $p->fila('variables_expertas', $m['cod_variable_experta']);
        if (! isset($p->contratosExtraccion[$m['tipo_extraccion']])) {
            throw new DomainException('Extracción sin contrato técnico explícito.');
        }
        $campoOSelector = $m['campo_valor'] ?? $m['clave_selector'];
        if ($f->fuente !== $s['cod_fuente_dato_experta'] || $f->tabla !== $s['tabla_raiz'] || $f->campo !== $campoOSelector) {
            throw new DomainException('Procedencia incompatible con el mapeo declarado.');
        }
        $admisibilidad = (new EvaluadorAdmisibilidad)->evaluar($f, $c);
        $valor = $mapeoValor = null;
        $representacion = 'SIN_MAPEO_ACTIVO';
        if ($m['estado'] === 'ACTIVO' && $s['estado'] === 'ACTIVO' && $v['estado'] === 'ACTIVO'
            && $p->red->nodo($v['cod_nodo_semantico'])['estado'] === 'ACTIVO') {
            if ($f->valorTipado === null) {
                $representacion = 'NO_DISPONIBLE';
            } else {
                [$tipo, $literal] = $this->literal($f->valorTipado);
                $matches = array_values(array_filter($p->tabla('mapeos_valores_fuente'), fn ($r) => $r['cod_mapeo_variable_fuente'] === $mapeoId
                    && $r['estado'] === 'ACTIVO' && $r['estado_aprobacion'] === 'APROBADO'
                    && $r['tipo_valor_fuente'] === $tipo && $r['valor_fuente_exacto'] === $literal
                    && ($r['fecha_hora_vigencia_desde'] === null || new DateTimeImmutable($r['fecha_hora_vigencia_desde']) <= $c->fechaCorte)
                    && ($r['fecha_hora_vigencia_hasta'] === null || new DateTimeImmutable($r['fecha_hora_vigencia_hasta']) >= $c->fechaCorte)));
                if (count($matches) > 1) {
                    throw new DomainException('Más de un mapeo exacto activo; no se puede elegir arbitrariamente.');
                }
                $representacion = 'VALOR_NO_RECONOCIDO';
                if ($matches) {
                    $semantico = $p->fila('valores_semanticos', $matches[0]['cod_valor_semantico']);
                    $dominio = $p->fila('dominios_valores_expertos', $semantico['cod_dominio_valores']);
                    if ($semantico['estado'] === 'ACTIVO' && $semantico['estado_aprobacion'] === 'APROBADO' && $dominio['estado'] === 'ACTIVO') {
                        $valor = $semantico['cod_valor_semantico'];
                        $mapeoValor = $matches[0]['cod_mapeo_valor_fuente'];
                        $representacion = 'MAPEADO';
                    }
                }
            }
        }
        $id = 'EVE_'.substr(hash('sha256', serialize([$c->evaluacion, $mapeoId, $f->registro])), 0, 16);

        return new EvidenciaExperta($id, $c, $f, $mapeoId, $mapeoValor, $v['cod_variable_experta'], $valor, $representacion, $admisibilidad['estado'], $admisibilidad['motivos']);
    }
}
