<?php

declare(strict_types=1);

// Inspección reproducible de la referencia. Sólo SQLite en memoria; nunca el esquema institucional.
require dirname(__DIR__, 2).'/vendor/autoload.php';
require __DIR__.'/ContratoNominal.php';
require __DIR__.'/EsquemaNominal.php';

use OrionNominal\EsquemaNominal;

$check = ($argv[1] ?? null) === '--check';
if (count($argv) > 2 || (isset($argv[1]) && ! $check)) {
    throw new RuntimeException('Uso: php generar-referencia-completa.php [--check]');
}
$mapa = require __DIR__.'/mapa-completo.php';
$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec('PRAGMA foreign_keys=ON');
$db->exec('CREATE TABLE residentes(cod_residente VARCHAR(20) PRIMARY KEY); CREATE TABLE usuarios(cod_usuario VARCHAR(20) PRIMARY KEY)');
$ddlSqlite = EsquemaNominal::ddl($mapa, 'sqlite');
$ddlPostgres = EsquemaNominal::ddl($mapa, 'pgsql');
$db->exec($ddlSqlite);
$tablas = array_merge(['releases_nominales', 'evaluaciones_nominales'], array_keys(EsquemaNominal::tablas($mapa)));
$columnas = [['tabla', 'columna_nominal', 'tipo_sql', 'not_null', 'pk']];
$fks = [['tabla', 'fk', 'posicion', 'columna', 'destino', 'columna_destino', 'on_delete']];
$anchuras = [];
foreach ($tablas as $tabla) {
    $cols = $db->query("PRAGMA table_info($tabla)")->fetchAll(PDO::FETCH_ASSOC);
    $anchuras[$tabla] = count($cols);
    foreach ($cols as $c) {
        $columnas[] = [$tabla, $c['name'], $c['type'], $c['notnull'], $c['pk']];
    }
    foreach ($db->query("PRAGMA foreign_key_list($tabla)")->fetchAll(PDO::FETCH_ASSOC) as $fk) {
        $fks[] = [$tabla, $fk['id'], $fk['seq'], $fk['from'], $fk['table'], $fk['to'], $fk['on_delete']];
    }
}
$ubicaciones = [['modelo', 'ruta_estructural', 'tabla', 'propietario', 'fk_propietario']];
$er = "erDiagram\n    residentes ||--o{ evaluaciones_nominales : residente\n    usuarios ||--o{ evaluaciones_nominales : ejecutor\n    releases_nominales ||--o{ evaluaciones_nominales : release\n";
foreach ($mapa['modelos'] as $id => $m) {
    $ubicaciones[] = [$id, $m['ruta'], $m['tabla'], $m['padre'], $m['padre'] === null ? null : $m['fk_propietario']];
    $padre = $m['padre'] === null ? 'evaluaciones_nominales' : $mapa['modelos'][$m['padre']]['tabla'];
    $er .= "    $padre ||--o{ {$m['tabla']} : propiedad\n";
}
$csv = static function (array $filas): string {
    $f = fopen('php://temp', 'w+');
    foreach ($filas as $fila) {
        fputcsv($f, $fila, ',', '"', '');
    }
    rewind($f);
    $texto = stream_get_contents($f);
    fclose($f);

    return $texto;
};
arsort($anchuras);
$resumen = ['contrato' => $mapa['contrato'], 'alcance' => $mapa['alcance'], 'instalable' => false,
    'base_actual' => 100, 'maximo_diseno' => 110, 'relaciones_contenido' => count($mapa['modelos']),
    'relaciones_adicionales' => count($tablas), 'total_si_se_instalara' => 100 + count($tablas),
    'columnas_fisicas' => array_sum($anchuras), 'anchuras' => $anchuras,
    'brechas' => ['EXCEDE_MAXIMO_110', 'REFERENCIA_OBSERVADA_NO_DICCIONARIO_FUNCIONAL_FINAL',
        'FK_SEMANTICAS_ORIGINALES_Y_MEMBRESIA_TERMINAL_PENDIENTES', 'NORMALIZACION_Y_MANTENIBILIDAD_NO_APROBADAS'],
    'no_modifica_base_institucional' => true];
$artefactos = ['diccionario.csv' => $csv($columnas), 'fk.csv' => $csv($fks), 'ubicaciones.csv' => $csv($ubicaciones),
    'referencia-sqlite.sql' => $ddlSqlite, 'referencia-postgres.sql' => $ddlPostgres,
    'reversion-sqlite.sql' => EsquemaNominal::ddlReversion($mapa, 'sqlite'),
    'reversion-postgres.sql' => EsquemaNominal::ddlReversion($mapa, 'pgsql'), 'modelo-er.mmd' => $er,
    'resumen.json' => json_encode($resumen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n"];
$carpeta = __DIR__.'/referencia-completa';
if (! $check && ! is_dir($carpeta) && ! mkdir($carpeta, 0755, true)) {
    throw new RuntimeException('No se pudo crear el directorio de referencia.');
}
foreach ($artefactos as $archivo => $texto) {
    $ruta = $carpeta.'/'.$archivo;
    if ($check) {
        if (! is_file($ruta) || file_get_contents($ruta) !== $texto) {
            throw new RuntimeException('Artefacto de referencia desactualizado: '.$archivo);
        }
    } elseif (file_put_contents($ruta, $texto) !== strlen($texto)) {
        throw new RuntimeException('No se pudo escribir el artefacto de referencia: '.$archivo);
    }
}
echo json_encode(['relaciones' => count($tablas), 'total' => 100 + count($tablas), 'instalable' => false, 'check' => $check], JSON_THROW_ON_ERROR).PHP_EOL;
