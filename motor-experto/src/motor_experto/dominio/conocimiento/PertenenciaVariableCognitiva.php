<?php

declare(strict_types=1);

namespace MotorExperto\dominio\conocimiento;

use DomainException;

/** Pertenencia estructural explícita; nunca habilita participación inferencial por transitividad. */
final class PertenenciaVariableCognitiva
{
    public function verificar(array $tablas, string $variable, string $criterio): array
    {
        $variables = array_column($tablas['variables_expertas'], null, 'cod_variable_experta');
        $nodos = array_column($tablas['nodos_semanticos'], null, 'cod_nodo_semantico');
        $v = $variables[$variable] ?? throw new DomainException('Variable ausente de la versión.');
        $propietario = $nodos[$v['cod_nodo_propietario_primario'] ?? ''] ?? throw new DomainException('La condición requiere propietario primario explícito.');
        if (($nodos[$criterio]['tipo_nodo'] ?? null) !== 'CRITERIO' || $propietario['estado'] !== 'ACTIVO') {
            throw new DomainException('Propietario o criterio incompatible.');
        }
        if ($propietario['cod_nodo_semantico'] === $criterio) {
            return ['propietario' => $criterio, 'pertenencia' => null];
        }
        $pertenencias = array_values(array_filter($tablas['relaciones_semanticas'], static fn ($r) => $r['cod_nodo_origen'] === $propietario['cod_nodo_semantico'] && $r['tipo_relacion'] === 'SUBCOMPONENTE_DE' && $r['estado'] === 'ACTIVO'));
        if ($propietario['tipo_nodo'] !== 'SUBCOMPONENTE' || count($pertenencias) !== 1 || $pertenencias[0]['cod_nodo_destino'] !== $criterio) {
            throw new DomainException('El subcomponente debe pertenecer explícitamente a un único dominio primario.');
        }
        // Exige además una arista directa variable→criterio. Su contrato inferencial lo verifica PaqueteConocimiento.
        $directas = array_values(array_filter($tablas['relaciones_semanticas'], static fn ($r) => $r['cod_nodo_origen'] === $v['cod_nodo_semantico'] && $r['cod_nodo_destino'] === $criterio && $r['estado'] === 'ACTIVO'));
        if ($directas === []) {
            throw new DomainException('La pertenencia del componente no sustituye el vínculo explícito de la variable al criterio.');
        }

        return ['propietario' => $propietario['cod_nodo_semantico'], 'pertenencia' => $pertenencias[0]['cod_relacion_semantica']];
    }
}
