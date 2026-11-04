<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\DTOs;

use Carbon\CarbonImmutable;

/** Disponibilidad de metadatos; no acredita mediciones, permisos de ejecución ni aplicabilidad clínica. */
final class DisponibilidadReglaCatalogo
{
    public function describir(array $regla, array $version, array $tablas, CarbonImmutable $corte): array
    {
        $motivos = [];
        if (($regla['estado'] ?? null) !== 'ACTIVO') {
            $motivos[] = 'La regla está archivada o inactiva.';
        }
        if (($regla['estado_revision'] ?? null) === 'RECHAZADA') {
            $motivos[] = 'La regla tiene una decisión de rechazo.';
        }
        if (($version['estado'] ?? null) !== 'ACTIVO' || empty($version['fecha_hora_vigencia'])
            || CarbonImmutable::parse($version['fecha_hora_vigencia'])->greaterThan($corte)
            || (! empty($version['fecha_hora_retiro']) && CarbonImmutable::parse($version['fecha_hora_retiro'])->lessThanOrEqualTo($corte))) {
            $motivos[] = 'La versión no está vigente para nuevas ejecuciones.';
        }
        if (preg_match('/SIM|TEST|EXPERIMENTAL/i', $version['codigo_version'] ?? '')) {
            $motivos[] = 'El paquete está excluido del circuito operativo.';
        }
        $indexar = static fn (string $tabla, string $pk): array => array_column($tablas[$tabla] ?? [], null, $pk);
        $variables = $indexar('variables_expertas', 'cod_variable_experta');
        $valores = $indexar('valores_semanticos', 'cod_valor_semantico');
        $nodos = $indexar('nodos_semanticos', 'cod_nodo_semantico');
        $dominios = $indexar('dominios_valores_expertos', 'cod_dominio_valores');
        $criterios = $indexar('criterios_dominios_resultado', 'cod_criterio_dominio_resultado');
        $condiciones = array_values(array_filter($tablas['condiciones_regla_experta'] ?? [],
            static fn ($c) => $c['cod_regla_experta'] === $regla['cod_regla_experta']));
        $consecuencias = array_values(array_filter($tablas['consecuencias_regla_experta'] ?? [],
            static fn ($c) => $c['cod_regla_experta'] === $regla['cod_regla_experta']));
        if ($condiciones === [] || count($consecuencias) !== 1) {
            $motivos[] = 'La regla no tiene condiciones y una consecuencia inequívocas.';
        }
        foreach ($condiciones as $condicion) {
            $variable = $variables[$condicion['cod_variable_experta']] ?? [];
            $valor = $valores[$condicion['cod_valor_semantico']] ?? [];
            if (($variable['estado'] ?? null) !== 'ACTIVO'
                || ($nodos[$variable['cod_nodo_semantico'] ?? '']['estado'] ?? null) !== 'ACTIVO'
                || ! $this->valorHabilitado($valor, $dominios)
                || ($variable['cod_dominio_valores'] ?? null) !== ($valor['cod_dominio_valores'] ?? null)) {
                $motivos[] = 'Una condición tiene dependencias inactivas, incompatibles o no aprobadas.';
            }
        }
        foreach ($consecuencias as $consecuencia) {
            $criterio = $criterios[$consecuencia['cod_criterio_dominio_resultado']] ?? [];
            $valor = $valores[$consecuencia['cod_valor_semantico']] ?? [];
            if (($criterio['estado'] ?? null) !== 'ACTIVO'
                || ($nodos[$criterio['cod_nodo_criterio'] ?? '']['estado'] ?? null) !== 'ACTIVO'
                || ! $this->valorHabilitado($valor, $dominios)
                || ($criterio['cod_dominio_valores'] ?? null) !== ($valor['cod_dominio_valores'] ?? null)) {
                $motivos[] = 'La consecuencia tiene dependencias inactivas, incompatibles o no aprobadas.';
            }
        }

        return ['habilitada_en_catalogo' => $motivos === [], 'motivos' => array_values(array_unique($motivos)),
            'alcance' => 'ESTADOS_VIGENCIA_Y_DEPENDENCIAS_DIRECTAS',
            'acredita_ejecucion_clinica' => false];
    }

    private function valorHabilitado(array $valor, array $dominios): bool
    {
        return ($valor['estado'] ?? null) === 'ACTIVO' && ($valor['estado_aprobacion'] ?? null) === 'APROBADO'
            && ($dominios[$valor['cod_dominio_valores'] ?? '']['estado'] ?? null) === 'ACTIVO';
    }
}
