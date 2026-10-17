<?php

use Illuminate\Http\Request;

// Router exclusivo de la prueba HTTP: nunca conecta con la instalación operativa.
$root = realpath((string) getenv('RM_QA_HTTP_ROOT'));
if (PHP_SAPI !== 'cli-server' || ! $root || dirname($root) !== realpath(sys_get_temp_dir())
    || ! preg_match('/^remembermind-wound-http-[a-f0-9]{16}$/D', basename($root))
    || ! is_file($root.'/qa.sqlite')) {
    throw new RuntimeException('Se requiere el directorio temporal propio de QA.');
}
$repo = dirname(__DIR__, 2);
foreach ([
    'APP_ENV' => 'testing', 'APP_DEBUG' => 'false', 'APP_CONFIG_CACHE' => $root.'/unused-config.php',
    'APP_ROUTES_CACHE' => $root.'/unused-routes.php', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $root.'/qa.sqlite',
    'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'file', 'LOG_CHANNEL' => 'stderr',
    'VIEW_COMPILED_PATH' => $root.'/views',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}
define('LARAVEL_START', microtime(true));
require $repo.'/vendor/autoload.php';
$app = require $repo.'/bootstrap/app.php';
$app->booted(function () use ($root) {
    if (! app()->environment('testing') || config('database.default') !== 'sqlite'
        || config('database.connections.sqlite.database') !== $root.'/qa.sqlite') {
        throw new RuntimeException('Conexión de QA inválida.');
    }
    config([
        'filesystems.disks.local.root' => $root.'/private',
        'filesystems.disks.tmp-for-tests' => ['driver' => 'local', 'root' => $root.'/private', 'throw' => true],
        'session.files' => $root.'/sessions', 'session.secure' => false, 'session.domain' => null, 'session.expire_on_close' => true,
        'remembermind.acceso_clinico_temporal_superadmin' => false,
    ]);
});
$app->handleRequest(Request::capture());
