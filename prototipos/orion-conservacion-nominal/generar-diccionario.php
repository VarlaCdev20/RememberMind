<?php

declare(strict_types=1);

// Genera artefactos de diseño en este directorio. Sólo compila DDL en SQLite :memory:.
require __DIR__.'/ContratoNominal.php';
require __DIR__.'/EsquemaNominal.php';

use OrionNominal\ContratoNominal;
use OrionNominal\EsquemaNominal;

$verificar = ($argv[1] ?? '') === '--check';
if (count($argv) > 2 || (isset($argv[1]) && ! $verificar)) {
    throw new RuntimeException('Uso: php generar-diccionario.php [--check]');
}
$mapa = ContratoNominal::mapa();
$tablas = EsquemaNominal::tablas($mapa);
$archivos = [];
foreach (['sqlite', 'pgsql'] as $motor) {
    $archivos["esquema-$motor.sql"] = '-- ENSAYO NO INSTALABLE: supera el máximo institucional de 110 tablas.'."\n".EsquemaNominal::ddl($mapa, $motor);
    $archivos["reversion-$motor.sql"] = '-- Sólo revierte las tablas sintéticas del ensayo desechable; no es migración institucional.'."\n".EsquemaNominal::ddlReversion($mapa, $motor);
}
$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec('PRAGMA foreign_keys=ON');
$db->exec('CREATE TABLE residentes(cod_residente VARCHAR(20) PRIMARY KEY); CREATE TABLE usuarios(cod_usuario VARCHAR(20) PRIMARY KEY)');
$db->exec($archivos['esquema-sqlite.sql']);
$csv = static function (array $filas): string {
    $f = fopen('php://temp', 'w+');
    foreach ($filas as $fila) {
        fputcsv($f, $fila, ',', '"', '');
    }
    rewind($f);
    $salida = stream_get_contents($f);
    fclose($f);

    return $salida;
};
$campos = [['modelo', 'ruta_original_modelo', 'ruta_campo', 'tabla', 'columna_nominal', 'clase', 'tipos_php_permitidos', 'modelo_hijo', 'rol_hijo']];
$modelos = [['modelo', 'ruta_original', 'tabla', 'modelo_propietario', 'fk_propietario', 'columnas_fisicas']];
foreach ($mapa['modelos'] as $id => $modelo) {
    $modelos[] = [$id, $modelo['ruta'] ?? '/', $modelo['tabla'], $modelo['padre'], $modelo['padre'] === null ? null : $modelo['fk_propietario'],
        count($db->query('PRAGMA table_info('.$modelo['tabla'].')')->fetchAll())];
    $recorrer = static function (array $nodo, string $ruta = '') use (&$recorrer, &$campos, $id, $modelo): void {
        $campos[] = [$id, $modelo['ruta'] ?? '/', $ruta, $modelo['tabla'], ContratoNominal::columna($ruta, $id), $nodo['clase'],
            $nodo['clase'] === 'escalar' ? implode('|', ContratoNominal::tipos($id, $ruta)) : null, $nodo['modelo'] ?? null, $nodo['identidad'] ?? null];
        if ($nodo['clase'] === 'objeto') {
            foreach ($nodo['campos'] as $nombre => $hijo) {
                $recorrer($hijo, $ruta.'/'.$nombre);
            }
        }
    };
    $recorrer($modelo['estructura']);
}
$fisico = [['tabla', 'columna', 'tipo_sqlite', 'not_null', 'pk', 'valor_predeterminado']];
$fks = [['tabla', 'fk', 'orden', 'columna', 'tabla_referida', 'columna_referida', 'on_delete', 'on_update']];
$indices = [['tabla', 'indice', 'unico', 'columnas', 'parcial']];
$anchuras = [];
$nombres = array_merge(['evaluaciones_nominales', 'releases_nominales'], array_keys($tablas));
foreach ($nombres as $tabla) {
    $cols = $db->query("PRAGMA table_info($tabla)")->fetchAll(PDO::FETCH_ASSOC);
    $anchuras[$tabla] = count($cols);
    foreach ($cols as $c) {
        $fisico[] = [$tabla, $c['name'], $c['type'], $c['notnull'], $c['pk'], $c['dflt_value']];
    }
    foreach ($db->query("PRAGMA foreign_key_list($tabla)")->fetchAll(PDO::FETCH_ASSOC) as $fk) {
        $fks[] = [$tabla, $fk['id'], $fk['seq'], $fk['from'], $fk['table'], $fk['to'], $fk['on_delete'], $fk['on_update']];
    }
    foreach ($db->query("PRAGMA index_list($tabla)")->fetchAll(PDO::FETCH_ASSOC) as $i) {
        $n = $i['name'];
        $indices[] = [$tabla, $n, $i['unique'], implode('|', array_column($db->query("PRAGMA index_info($n)")->fetchAll(PDO::FETCH_ASSOC), 'name')), $i['partial']];
    }
}
$archivos['matriz-campos.csv'] = $csv($campos);
$archivos['modelos.csv'] = $csv($modelos);
$ubicaciones = [];
$expandir = static function (array $nodo, string $modelo, string $absoluta, string $local) use (&$expandir, &$ubicaciones, $mapa): void {
    $ubicaciones[$absoluta === '' ? '/' : $absoluta][] = ['modelo' => $modelo, 'tabla' => $mapa['modelos'][$modelo]['tabla'],
        'columna' => ContratoNominal::columna($local, $modelo), 'clase' => $nodo['clase']];
    if ($nodo['clase'] === 'objeto') {
        foreach ($nodo['campos'] as $nombre => $hijo) {
            $expandir($hijo, $modelo, $absoluta.'/'.$nombre, $local.'/'.$nombre);
        }
    } elseif (in_array($nodo['clase'], ['coleccion', 'registro'], true)) {
        $expandir($mapa['modelos'][$nodo['modelo']]['estructura'], $nodo['modelo'],
            $absoluta.($nodo['clase'] === 'coleccion' ? '/*' : ''), '');
    }
};
$expandir($mapa['modelos'][$mapa['raiz']]['estructura'], $mapa['raiz'], '', '');
$cobertura = [['ruta_dto_original', 'modelo_anterior', 'modelo_nominal', 'clase', 'tabla_nominal', 'columna_nominal']];
$f = fopen(dirname(__DIR__).'/orion-conservacion-100/mapa-campos-candidato.csv', 'rb');
$cabecera = fgetcsv($f, escape: '');
while (($fila = fgetcsv($f, escape: '')) !== false) {
    $origen = array_combine($cabecera, $fila);
    $candidatas = array_values(array_filter($ubicaciones[$origen['ruta_dto']] ?? [],
        static fn ($u) => $u['clase'] === $origen['forma']));
    if (count($candidatas) !== 1) {
        throw new RuntimeException('Ruta sin destino nominal unívoco: '.$origen['ruta_dto']);
    }
    $destino = $candidatas[0];
    $cobertura[] = [$origen['ruta_dto'], $origen['modelo_origen'], $destino['modelo'],
        $destino['clase'], $destino['tabla'], $destino['columna']];
}
fclose($f);
if (count($cobertura) !== 784) {
    throw new RuntimeException('Cambió el inventario original de 783 rutas.');
}
$archivos['cobertura-783.csv'] = $csv($cobertura);
$granos = [['modelo_anterior', 'ruta_coleccion_original', 'modelo_nominal', 'tabla_nominal', 'modelo_propietario', 'fk_propietario']];
$f = fopen(dirname(__DIR__).'/orion-conservacion-100/mapa-granos-candidato.csv', 'rb');
$cabecera = fgetcsv($f, escape: '');
while (($fila = fgetcsv($f, escape: '')) !== false) {
    $origen = array_combine($cabecera, $fila);
    $candidatas = array_values(array_filter($ubicaciones[$origen['ruta_coleccion']] ?? [],
        static fn ($u) => $u['modelo'] === $origen['modelo_origen']));
    if (count($candidatas) !== 1) {
        throw new RuntimeException('Grano sin destino nominal unívoco: '.$origen['ruta_coleccion']);
    }
    $destino = $candidatas[0];
    $def = $mapa['modelos'][$destino['modelo']];
    $granos[] = [$origen['modelo_origen'], $origen['ruta_coleccion'], $destino['modelo'], $destino['tabla'],
        $def['padre'], $def['padre'] === null ? null : $def['fk_propietario']];
}
fclose($f);
if (count($granos) !== 52) {
    throw new RuntimeException('Cambió el inventario original de 51 rutas.');
}
$archivos['cobertura-51-rutas.csv'] = $csv($granos);
$inventario = json_decode(file_get_contents(dirname(__DIR__).'/orion-conservacion-110/inventario-esquema-sqlite.json'), true, flags: JSON_THROW_ON_ERROR);
$referencias = [['modelo', 'tabla_definicion', 'columnas_definicion', 'tabla_copia', 'destino_definicion', 'columnas_destino', 'estado_copia']];
foreach ($mapa['modelos'] as $id => $modelo) {
    if (! str_starts_with($id, 'k_')) {
        continue;
    }
    $definicion = substr($id, 2);
    foreach ($inventario['tablas'][$definicion]['fks'] as $fk) {
        $referencias[] = [$id, $definicion, implode('|', $fk['columns']), $modelo['tabla'], $fk['foreign_table'],
            implode('|', $fk['foreign_columns']), 'IDENTIFICADOR_CONSERVADO_SIN_FK_SEMANTICA_EN_COPIA'];
    }
}
$archivos['referencias-conocimiento-pendientes.csv'] = $csv($referencias);
$archivos['diccionario-fisico.csv'] = $csv($fisico);
$archivos['fk.csv'] = $csv($fks);
$archivos['indices.csv'] = $csv($indices);
$mermaid = "erDiagram\n  residentes ||--o{ evaluaciones_nominales : residente\n  usuarios ||--o{ evaluaciones_nominales : ejecuta\n  releases_nominales ||--o{ evaluaciones_nominales : retiene\n";
foreach ($tablas as $tabla => $def) {
    $mermaid .= "  evaluaciones_nominales ||--o{ $tabla : conserva\n";
    foreach (array_unique(array_values($def['padres'] ?? [])) as $padre) {
        $mermaid .= "  $padre ||--o{ $tabla : propietario\n";
    }
}
$archivos['modelo-er.mmd'] = $mermaid;
$resumen = ['contrato' => ContratoNominal::VERSION, 'modelos_logicos' => count($mapa['modelos']),
    'tablas_entradas' => count($tablas), 'tablas_totales_candidato' => count($nombres), 'total_si_se_anadiera_a_104' => 104 + count($nombres),
    'exceso_sobre_maximo_110' => 104 + count($nombres) - 110, 'rutas_locales' => count($campos) - 1,
    'rutas_originales_mapeadas' => count($cobertura) - 1,
    'rutas_coleccion_originales_mapeadas' => count($granos) - 1,
    'referencias_semanticas_conocimiento_no_reconectadas' => count($referencias) - 1,
    'columnas_fisicas' => count($fisico) - 1, 'anchura_por_tabla' => $anchuras,
    'recupera_entradas_sql' => true, 'requiere_replay_para_resultado_completo' => true,
    'instalable' => false, 'migracion_institucional' => false];
$archivos['resumen.json'] = json_encode($resumen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
foreach ($archivos as $nombre => $contenido) {
    if ($verificar) {
        if (! is_file(__DIR__.'/'.$nombre) || file_get_contents(__DIR__.'/'.$nombre) !== $contenido) {
            throw new RuntimeException('Artefacto desactualizado: '.$nombre);
        }
    } else {
        file_put_contents(__DIR__.'/'.$nombre, $contenido);
    }
}
echo json_encode(['artefactos' => count($archivos), 'tablas' => count($nombres), 'total' => 104 + count($nombres),
    'rutas' => count($campos) - 1, 'columnas' => count($fisico) - 1, 'check' => $verificar], JSON_THROW_ON_ERROR)."\n";
