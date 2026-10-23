<?php

declare(strict_types=1);

/** Correspondencia reproducible entre los modelos F02 y el inventario físico, sin aprobar DDL. */
$mapa = require __DIR__.'/mapa-cerrado.php';
$entrada = fopen(__DIR__.'/../orion-conservacion-110/matriz-resolucion-51-rutas.csv', 'rb');
if ($entrada === false) {
    throw new RuntimeException('Falta la matriz de 51 rutas.');
}
$cabecera = fgetcsv($entrada);
$rutas = [];
while (($fila = fgetcsv($entrada)) !== false) {
    $dato = array_combine($cabecera, $fila);
    if ($dato === false || ! isset($mapa['modelos'][$dato['modelo']])) {
        throw new RuntimeException('Ruta sin modelo F02 reconocido.');
    }
    $rutas[$dato['modelo']][] = $dato;
}
fclose($entrada);
if (count($rutas) !== 39 || array_sum(array_map('count', $rutas)) !== 51) {
    throw new RuntimeException('La matriz ya no coincide con 39 modelos y 51 rutas.');
}
$salida = fopen(__DIR__.'/matriz-integracion-38-relaciones.csv', 'wb');
if ($salida === false) {
    throw new RuntimeException('No se pudo escribir la matriz de integración.');
}
fputcsv($salida, ['modelo', 'tabla_f02_desechable', 'rutas', 'destinos_institucionales_evaluados',
    'destino_en_ensayo_integrado', 'prueba_ejecutada', 'estado_institucional'], ',', '"', '');
$tablas = [];
foreach ($mapa['modelos'] as $id => $modelo) {
    $tabla = match ($id) {
        'm015' => $mapa['modelos']['m008']['tabla'],
        'm016' => $mapa['modelos']['m009']['tabla'],
        default => $modelo['tabla'],
    };
    $tablas[$tabla] = true;
    $filas = $rutas[$id] ?? [];
    fputcsv($salida, [$id, $tabla, implode(' | ', array_column($filas, 'ruta')),
        implode(' | ', array_unique(array_column($filas, 'destino_candidato'))),
        $id === 'm001' ? 'evaluaciones_expertas (cabecera) + '.$tabla.' (contenido)' : $tabla,
        'SQLite memoria: seis DTO completos, replay, huella, FK, cierre y rollback',
        'REFERENCIA_F02; no se ha probado reutilización semántica institucional de esta ruta'], ',', '"', '');
}
fclose($salida);
if (count($tablas) !== 37) {
    throw new RuntimeException('La integración cambió el inventario de 37 relaciones F02 más cabecera existente.');
}
echo '39 modelos y 51 rutas: 37 relaciones F02 nuevas + evaluaciones_expertas = 38 relaciones; 141 tablas de negocio.'.PHP_EOL;
