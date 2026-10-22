<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\DTOs;

use DomainException;

/** Vista acotada de relaciones declaradas. No evalúa reglas ni acredita aprobación clínica. */
final class ProyectarRedConocimiento
{
    public function fuentesNodo(array $conocimiento, string $nodo): array
    {
        $variables = array_filter($conocimiento['tablas']['variables_expertas'] ?? [],
            fn ($v) => ($v['cod_nodo_semantico'] ?? null) === $nodo || ($v['cod_nodo_propietario_primario'] ?? null) === $nodo);
        $ids = array_fill_keys(array_column($variables, 'cod_variable_experta'), true);
        $mapas = array_filter($conocimiento['tablas']['mapeos_variables_fuente'] ?? [], fn ($m) => isset($ids[$m['cod_variable_experta']]));
        $fuentes = array_column($conocimiento['tablas']['fuentes_datos_expertas'] ?? [], null, 'cod_fuente_dato_experta');

        return array_values(array_map(fn ($m) => ['mapeo' => $m['cod_mapeo_variable_fuente'],
            'fuente' => $fuentes[$m['cod_fuente_dato_experta']]['nombre'] ?? 'Fuente no declarada',
            'codigo' => $m['cod_fuente_dato_experta']], $mapas));
    }

    public function presentar(array $conocimiento, ?string $version, array $filtros = [], int $pagina = 1): array
    {
        $nodos = $conocimiento['nodos'];
        $relaciones = $conocimiento['relaciones'];
        $versionReal = $version ?? (reset($nodos)['cod_version_modelo'] ?? null);
        $codigos = $idsRelaciones = [];
        foreach ($nodos as $id => $n) {
            if ($id !== $n['cod_nodo_semantico'] || $n['cod_version_modelo'] !== $versionReal
                || isset($codigos[$n['codigo_semantico']])) {
                throw new DomainException('Identidad o versión de nodo incompatible.');
            }
            $codigos[$n['codigo_semantico']] = true;
        }
        foreach ($relaciones as $r) {
            if (! isset($nodos[$r['cod_nodo_origen']], $nodos[$r['cod_nodo_destino']])
                || $r['cod_version_modelo'] !== $versionReal || isset($idsRelaciones[$r['cod_relacion_semantica']])) {
                throw new DomainException('Relación huérfana, duplicada o de otra versión.');
            }
            $idsRelaciones[$r['cod_relacion_semantica']] = true;
        }
        $tipos = array_values(array_unique(array_column($nodos, 'tipo_nodo')));
        $predicados = array_values(array_unique(array_column($relaciones, 'tipo_relacion')));
        $estados = array_values(array_unique(array_column($nodos, 'estado')));
        sort($tipos);
        sort($predicados);
        sort($estados);
        $criterios = array_filter($nodos, fn ($n) => $n['tipo_nodo'] === 'CRITERIO');
        $entorno = null;
        if ($filtros['criterio'] ?? '') {
            $raices = array_keys(array_filter($criterios, fn ($n) => $n['codigo_semantico'] === $filtros['criterio']));
            $entorno = array_fill_keys($raices, true);
            // Dos saltos declarados; no se completa la conectividad por semejanza de nombres.
            for ($i = 0; $i < 2; $i++) {
                $siguiente = $entorno;
                foreach ($relaciones as $r) {
                    if (isset($entorno[$r['cod_nodo_origen']])) {
                        $siguiente[$r['cod_nodo_destino']] = true;
                    }
                    if (isset($entorno[$r['cod_nodo_destino']])) {
                        $siguiente[$r['cod_nodo_origen']] = true;
                    }
                }
                $entorno = $siguiente;
            }
        }
        $busqueda = mb_strtolower(mb_substr(trim($filtros['buscar'] ?? ''), 0, 120));
        $relacionesFiltradas = array_filter($relaciones, fn ($r) => ! ($filtros['relacion'] ?? '') || $r['tipo_relacion'] === $filtros['relacion']);
        $conectados = array_fill_keys(array_merge(array_column($relacionesFiltradas, 'cod_nodo_origen'), array_column($relacionesFiltradas, 'cod_nodo_destino')), true);
        $filtrados = array_filter($nodos, fn ($n, $id) => ($entorno === null || isset($entorno[$id]))
            && (! ($filtros['tipo'] ?? '') || $n['tipo_nodo'] === $filtros['tipo'])
            && (! ($filtros['estado'] ?? '') || $n['estado'] === $filtros['estado'])
            && (! ($filtros['relacion'] ?? '') || isset($conectados[$id]))
            && str_contains(mb_strtolower($n['codigo_semantico'].' '.$n['nombre']), $busqueda), ARRAY_FILTER_USE_BOTH);
        uasort($filtrados, fn ($a, $b) => strnatcasecmp($a['codigo_semantico'], $b['codigo_semantico']));
        $total = count($filtrados);
        $paginas = max(1, (int) ceil($total / 36));
        $pagina = min($paginas, max(1, $pagina));
        $visibles = array_slice($filtrados, ($pagina - 1) * 36, 36, true);
        $aristas = [];
        foreach ($relacionesFiltradas as $r) {
            if (isset($visibles[$r['cod_nodo_origen']], $visibles[$r['cod_nodo_destino']])) {
                $aristas[] = ['origen' => $r['cod_nodo_origen'], 'destino' => $r['cod_nodo_destino'], 'relacion' => $r['tipo_relacion']];
            }
        }
        $salida = [];
        foreach ($visibles as $id => $n) {
            $salida[] = ['id' => $id, 'nombre' => $n['nombre'], 'tipo' => $n['tipo_nodo'], 'estado' => $n['estado'],
                'version' => $versionReal, 'columna' => (int) array_search($n['tipo_nodo'], $tipos, true),
                'datos' => ['Código' => $n['codigo_semantico'], 'Descripción' => $n['definicion'] ?? 'No declarada',
                    'Versión' => $versionReal, 'Procedencia' => $conocimiento['experimental'] ? 'Paquete experimental del motor' : 'Versión almacenada seleccionada']];
        }

        return ['nodos' => $salida, 'aristas' => $aristas, 'tipos' => $tipos, 'predicados' => $predicados, 'estados' => $estados,
            'criterios' => array_values($criterios), 'total' => $total, 'pagina' => $pagina, 'paginas' => $paginas, 'version' => $versionReal,
            'aviso' => 'Grafo de conocimiento declarado. Las conexiones no representan una evaluación ni una aprobación clínica.'];
    }
}
