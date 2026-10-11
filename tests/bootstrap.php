<?php

declare(strict_types=1);

if (getenv('APP_ENV') !== 'testing' || getenv('DB_CONNECTION') !== 'sqlite'
    || getenv('DB_DATABASE') !== ':memory:' || getenv('DB_URL') !== '') {
    throw new RuntimeException('Las pruebas predeterminadas requieren testing y SQLite :memory:.');
}

// Nunca heredar caches de la instalación operativa, tampoco su registro de rutas.
foreach (['APP_CONFIG_CACHE', 'APP_ROUTES_CACHE'] as $key) {
    $path = sys_get_temp_dir().'/remembermind-test-'.getmypid().'-'.bin2hex(random_bytes(8)).'-'.$key.'.php';
    putenv($key.'='.$path);
    $_ENV[$key] = $path;
    $_SERVER[$key] = $path;
}

require dirname(__DIR__).'/vendor/autoload.php';
