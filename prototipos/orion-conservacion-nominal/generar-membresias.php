<?php

declare(strict_types=1);

// Emite solamente estructura. SQLite en memoria; no inicia Laravel ni carga configuración o datos institucionales.
require dirname(__DIR__, 2).'/vendor/autoload.php';
require __DIR__.'/MembresiasSQL.php';

use MotorExperto\soporte\conservacion\MembresiasOriginalesPetersen as M;
use OrionNominal\MembresiasSQL;

$check = ($argv[1] ?? null) === '--check';
if (count($argv) > 2 || (isset($argv[1]) && ! $check)) {
    throw new RuntimeException('Uso: php generar-membresias.php [--check]');
}
$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec('PRAGMA foreign_keys=ON');
// Sólo referencias para introspección del DDL. La compatibilidad real se prueba sobre las migraciones Laravel desechables.
$db->exec('CREATE TABLE evaluaciones_expertas(cod_evaluacion_experta VARCHAR(20) PRIMARY KEY,cod_residente VARCHAR(20));
    CREATE TABLE valoraciones_petersen_originales(cod_valoracion_petersen VARCHAR(20) PRIMARY KEY,cod_residente VARCHAR(20));
    CREATE TABLE soportes_valoracion_petersen(cod_soporte_petersen VARCHAR(20) PRIMARY KEY,cod_valoracion_petersen VARCHAR(20))');
$sqlite = MembresiasSQL::ddl('sqlite');
$db->exec($sqlite);
$columnas = [['tabla', 'columna', 'tipo', 'obligatoria', 'pk', 'alcance']];
$fk = [['tabla', 'columna', 'referencia', 'clave_referida', 'borrado']];
$anchuras = [];
foreach (['evaluaciones_expertas', 'juicios_petersen_adquiridos', 'soportes_petersen_adquiridos'] as $tabla) {
    $cols = $db->query("PRAGMA table_info($tabla)")->fetchAll(PDO::FETCH_ASSOC);
    $anchuras[$tabla] = count($cols);
    foreach ($cols as $c) {
        if ($tabla === 'evaluaciones_expertas' && ! str_starts_with($c['name'], 'membresias_petersen_')) {
            continue;
        }
        $columnas[] = [$tabla, $c['name'], $c['type'], $c['notnull'], $c['pk'], $tabla === 'evaluaciones_expertas' ? 'COLUMNA_PROPUESTA' : 'TABLA_PROPUESTA'];
    }
    foreach ($db->query("PRAGMA foreign_key_list($tabla)")->fetchAll(PDO::FETCH_ASSOC) as $f) {
        $fk[] = [$tabla, $f['from'], $f['table'], $f['to'], $f['on_delete']];
    }
}
$mapeo = [['coleccion', 'campo', 'relacion', 'columna', 'recuperacion']];
foreach (['cod_valoracion_petersen', 'cod_residente', 'cod_personal', 'cod_usuario_registro', 'elemento', 'tipo_registro',
    'cod_valoracion_anterior', 'cod_documento_clinico', 'localizador_fuente'] as $campo) {
    $mapeo[] = ['procedencia_valoraciones', $campo, 'valoraciones_petersen_originales', $campo, 'JOIN_ORIGINAL_INMUTABLE'];
}
foreach (['fecha_hora', 'registrado_en'] as $campo) {
    $mapeo[] = ['procedencia_valoraciones', $campo, 'juicios_petersen_adquiridos', $campo.'_iso', 'ISO_EMITIDO_COTEJADO_CON_INSTANTE_ORIGINAL'];
}
$mapeo[] = ['procedencia_valoraciones', 'orden', 'juicios_petersen_adquiridos', 'orden_procedencia', 'ORDEN_EXPLICITO'];
$mapeo[] = ['membresias_soportes', 'valoracion', 'juicios_petersen_adquiridos', 'cod_valoracion_petersen', 'FK_ORIGINAL'];
$mapeo[] = ['membresias_soportes', 'orden', 'juicios_petersen_adquiridos', 'orden_membresia', 'ORDEN_EXPLICITO'];
$mapeo[] = ['membresias_soportes/soportes', 'codigo', 'soportes_petersen_adquiridos', 'cod_soporte_petersen', 'FK_ORIGINAL'];
$mapeo[] = ['membresias_soportes/soportes', 'orden', 'soportes_petersen_adquiridos', 'orden_membresia', 'ORDEN_EXPLICITO'];
foreach (M::CAMPOS as $campo) {
    $mapeo[] = ['soportes_originales', $campo, 'soportes_valoracion_petersen', $campo, 'JOIN_SOLO_MIEMBROS_ADQUIRIDOS_INMUTABLES'];
}
$mapeo[] = ['soportes_originales', 'orden_global', 'juicios_petersen_adquiridos + soportes_petersen_adquiridos',
    'orden_membresia + orden_membresia', 'CONCATENACION_EXIGIDA_POR_CONTRATO_V2'];
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
$er = "erDiagram\n    evaluaciones_expertas ||--o{ juicios_petersen_adquiridos : adquisicion\n"
    ."    valoraciones_petersen_originales ||--o{ juicios_petersen_adquiridos : original_inmutable\n"
    ."    juicios_petersen_adquiridos ||--o{ soportes_petersen_adquiridos : miembros\n"
    ."    soportes_valoracion_petersen ||--o{ soportes_petersen_adquiridos : soporte_inmutable\n";
$artefactos = ['diccionario.csv' => $csv($columnas), 'fk.csv' => $csv($fk), 'mapeo.csv' => $csv($mapeo), 'modelo-er.mmd' => $er,
    'candidato-sqlite.sql' => $sqlite, 'candidato-postgres.sql' => MembresiasSQL::ddl('pgsql')];
$resumen = ['alcance' => 'SUBMODELO_MEMBRESIAS_ORIGINALES_PETERSEN_V2', 'instalable_institucionalmente' => false,
    'conserva_dto_completo' => false, 'base_actual' => 100, 'tablas_nuevas_submodelo' => 2, 'total_hipotetico_submodelo' => 102,
    'maximo_global' => 110, 'columnas_cabecera_propuestas' => 4, 'columnas_juicio_adquirido' => $anchuras['juicios_petersen_adquiridos'],
    'columnas_soporte_adquirido' => $anchuras['soportes_petersen_adquiridos'], 'conectores_fk' => count($fk) - 1,
    'huella_ddl_sqlite' => hash('sha256', $sqlite), 'huella_ddl_postgres' => hash('sha256', $artefactos['candidato-postgres.sql']),
    'prueba_compatibilidad_baseline' => 'CapturaPetersenOriginalTest::test_sql_membresias_*',
    'no_modifica_base_institucional' => true];
$artefactos['resumen.json'] = json_encode($resumen, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
$carpeta = __DIR__.'/membresias-v2';
if (! $check && ! is_dir($carpeta) && ! mkdir($carpeta, 0755, true)) {
    throw new RuntimeException('No se pudo crear el directorio de estructura.');
}
foreach ($artefactos as $nombre => $texto) {
    $ruta = $carpeta.'/'.$nombre;
    if ($check) {
        if (! is_file($ruta) || file_get_contents($ruta) !== $texto) {
            throw new RuntimeException('Artefacto de membresías desactualizado: '.$nombre);
        }
    } elseif (file_put_contents($ruta, $texto) !== strlen($texto)) {
        throw new RuntimeException('No se pudo guardar el diccionario de membresías: '.$nombre);
    }
}
echo json_encode(['tablas_submodelo' => 2, 'total_hipotetico' => 102, 'dto_completo' => false, 'check' => $check], JSON_THROW_ON_ERROR).PHP_EOL;
