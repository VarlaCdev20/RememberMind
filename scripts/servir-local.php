<?php

// Límites por proceso: no modifica el php.ini global ni la configuración de otras aplicaciones.
use Symfony\Component\Process\Process;

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';
$port = $argv[1] ?? '8000';
if (PHP_SAPI !== 'cli' || ! ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
    fwrite(STDERR, "Uso: php scripts/servir-local.php [puerto]\n");
    exit(1);
}
$router = $root.'/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php';
$process = new Process([
    PHP_BINARY, '-d', 'upload_max_filesize=8M', '-d', 'post_max_size=45M',
    '-S', '127.0.0.1:'.$port, $router,
], $root.'/public');
$process->setTimeout(null);
fwrite(STDOUT, "RememberMind local: http://127.0.0.1:{$port} · carga por imagen 8 MB · petición 45 MB\n");
exit($process->run(static function ($type, $buffer): void {
    fwrite($type === Process::ERR ? STDERR : STDOUT, $buffer);
}));
