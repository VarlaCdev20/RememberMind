<?php

declare(strict_types=1);

// Auditoría estructural del corpus sintético existente. No inicia Laravel ni abre conexiones SQL.
require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/prototipos/orion-conservacion-0_2/CasoCanonico.php';
require dirname(__DIR__).'/prototipos/orion-conservacion-0_2/VariantesCanonicas.php';

use MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional;
use OrionFase02\VariantesCanonicas;

$comprobar = ($argv[1] ?? null) === '--check';
if (count($argv) > 2 || (isset($argv[1]) && ! $comprobar)) {
    throw new RuntimeException('Uso: php scripts/inventariar-salidas-institucionales.php [--check]');
}
$rutas = $casos = [];
$visitar = function (mixed $dato, string $seccion, string $ruta, string $caso, array &$conteo) use (&$visitar, &$rutas): void {
    $tipo = match (true) {
        is_array($dato) => array_is_list($dato) ? 'LISTA' : 'MAPA',
        $dato === null => 'NULL', is_bool($dato) => 'BOOL', is_int($dato) => 'INT', is_float($dato) => 'FLOAT',
        is_string($dato) => 'STRING', default => throw new RuntimeException('Tipo no plano en el corpus sintético.'),
    };
    $clave = $seccion.'|'.$ruta;
    $rutas[$clave] ??= ['seccion' => $seccion, 'ruta' => $ruta, 'tipos' => [], 'casos' => [], 'apariciones' => 0];
    $rutas[$clave]['tipos'][$tipo] = true;
    $rutas[$clave]['casos'][$caso] = true;
    $rutas[$clave]['apariciones']++;
    if (is_array($dato)) {
        $conteo['arreglos']++;
        $lista = array_is_list($dato);
        foreach ($dato as $k => $v) {
            $segmento = $lista ? '*' : str_replace(['~', '/'], ['~0', '~1'], (string) $k);
            $visitar($v, $seccion, $ruta.'/'.$segmento, $caso, $conteo);
        }
    } else {
        $conteo['hojas_escalares']++;
        $conteo['valores_null'] += (int) ($dato === null);
    }
};
foreach (VariantesCanonicas::casos() as $nombre => $dto) {
    $d = InstantaneaEvaluacionInstitucional::verificarIntegridad($dto->contenido, $dto->huella)->contenido;
    $secciones = ['instrumental_salida' => $d['instrumental']['contenido']['salida'],
        'petersen_salida' => $d['petersen']['contenido']['salida'],
        'extracciones_instrumentales' => array_column($d['instrumental']['contenido']['capturas']['capturas'], 'extraccion'),
        'fuente_conceptual' => $d['instrumental']['contenido']['fuente_conceptual']];
    foreach ($secciones as $seccion => $datos) {
        $conteo = ['hojas_escalares' => 0, 'arreglos' => 0, 'valores_null' => 0];
        $visitar($datos, $seccion, '', $nombre, $conteo);
        $casos[$nombre][$seccion] = $conteo;
    }
}
ksort($rutas);
$csv = fopen('php://temp', 'w+');
fputcsv($csv, ['seccion', 'ruta_estructural', 'tipos_observados', 'casos_sinteticos', 'apariciones'], ',', '"', '');
foreach ($rutas as $r) {
    $tipos = array_keys($r['tipos']);
    sort($tipos);
    $nombres = array_keys($r['casos']);
    sort($nombres);
    fputcsv($csv, [$r['seccion'], $r['ruta'], implode('|', $tipos), implode('|', $nombres), $r['apariciones']], ',', '"', '');
}
rewind($csv);
$contenidoCsv = stream_get_contents($csv);
fclose($csv);
$resumen = ['contrato' => InstantaneaEvaluacionInstitucional::CONTRATO, 'origen' => 'CORPUS_SINTETICO_F02_EXISTENTE',
    'cantidad_casos' => count($casos), 'rutas_estructurales_observadas' => count($rutas),
    'contiene_datos_clinicos_reales' => false, 'es_mapa_fisico_sql' => false, 'acredita_cobertura_de_todas_las_reglas' => false,
    'tablas_base_instaladas' => 100, 'maximo_autorizado_diseno' => 110,
    'incluido_en_mapa_historico_783' => false, 'casos' => $casos];
$contenidoJson = json_encode($resumen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
$carpeta = dirname(__DIR__).'/docs/auditorias/conservacion-completa-2026-10-22';
if (! $comprobar && ! is_dir($carpeta) && ! mkdir($carpeta, 0755, true)) {
    throw new RuntimeException('No se pudo crear la carpeta de auditoría.');
}
foreach (['rutas-salidas-observadas.csv' => $contenidoCsv, 'resumen-salidas-observadas.json' => $contenidoJson] as $archivo => $contenido) {
    $ruta = $carpeta.'/'.$archivo;
    if ($comprobar) {
        if (! is_file($ruta) || file_get_contents($ruta) !== $contenido) {
            throw new RuntimeException('El inventario estructural necesita actualización: '.$archivo);
        }
    } elseif (file_put_contents($ruta, $contenido) !== strlen($contenido)) {
        throw new RuntimeException('No se pudo guardar el inventario estructural.');
    }
}
echo json_encode(['casos_sinteticos' => count($casos), 'rutas_estructurales' => count($rutas), 'check' => $comprobar], JSON_THROW_ON_ERROR).PHP_EOL;
