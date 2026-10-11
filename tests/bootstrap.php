<?php

declare(strict_types=1);
use Tests\Support\EntornoPruebas;

require_once __DIR__.'/Support/EntornoPruebas.php';
EntornoPruebas::validarVariables();

// Nunca heredar caches de la instalación operativa, tampoco su registro de rutas.
foreach (['APP_CONFIG_CACHE', 'APP_ROUTES_CACHE'] as $key) {
    $path = sys_get_temp_dir().'/remembermind-test-'.getmypid().'-'.bin2hex(random_bytes(8)).'-'.$key.'.php';
    putenv($key.'='.$path);
    $_ENV[$key] = $path;
    $_SERVER[$key] = $path;
}

$views = sys_get_temp_dir().'/remembermind-test-views-'.getmypid().'-'.bin2hex(random_bytes(8));
if (! mkdir($views, 0700)) {
    throw new RuntimeException('No se pudo aislar la caché de vistas de pruebas.');
}
putenv('VIEW_COMPILED_PATH='.$views);
$_ENV['VIEW_COMPILED_PATH'] = $_SERVER['VIEW_COMPILED_PATH'] = $views;

// Este directorio se creó aquí, sin contenido operativo ni enlaces seguidos.
register_shutdown_function(static function () use ($views): void {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($views, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
        if ($file->isDir() && ! $file->isLink()) {
            rmdir($file->getPathname());
        } else {
            unlink($file->getPathname());
        }
    }
    rmdir($views);
});

require dirname(__DIR__).'/vendor/autoload.php';
