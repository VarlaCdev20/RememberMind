<?php

declare(strict_types=1);

/** Índice léxico de consumidores; complementa las FK físicas, sin afirmar que cubra SQL dinámico. */
$raiz = dirname(__DIR__, 2);
$esquema = json_decode(file_get_contents(__DIR__.'/inventario-esquema-sqlite.json'), true, flags: JSON_THROW_ON_ERROR);
$tablas = array_keys($esquema['tablas']);
$carpetas = [
    'app' => 'APLICACION',
    'motor-experto/src' => 'MOTOR',
    'routes' => 'RUTAS',
    'config' => 'CONFIGURACION',
    'database/seeders' => 'SEEDER',
    'resources/views' => 'VISTA',
    'tests' => 'PRUEBA',
];
$out = fopen(__DIR__.'/consumidores-codigo.csv', 'wb') ?: throw new RuntimeException('No se pudo crear índice.');
fputcsv($out, ['tabla', 'ambito', 'archivo']);
$total = 0;
foreach ($carpetas as $carpeta => $ambito) {
    $origen = $raiz.'/'.$carpeta;
    if (! is_dir($origen)) {
        continue;
    }
    $iterador = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($origen, FilesystemIterator::SKIP_DOTS));
    foreach ($iterador as $archivo) {
        if (! $archivo->isFile() || ! preg_match('/\.(php|blade\.php)$/D', $archivo->getFilename())) {
            continue;
        }
        $texto = file_get_contents($archivo->getPathname());
        $ruta = str_replace('\\', '/', substr($archivo->getPathname(), strlen($raiz) + 1));
        foreach ($tablas as $tabla) {
            if (preg_match('/(?<![a-z0-9_])'.preg_quote($tabla, '/').'(?![a-z0-9_])/i', $texto)) {
                fputcsv($out, [$tabla, $ambito, $ruta]);
                $total++;
            }
        }
    }
}
fclose($out);
echo "Referencias léxicas tabla/archivo: $total. SQL dinámico y alias requieren revisión manual.\n";
