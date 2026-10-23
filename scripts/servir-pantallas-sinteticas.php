<?php

declare(strict_types=1);

// Router de archivos explícitos, sin bootstrap Laravel, sesiones, consultas SQL o mutaciones.
if (PHP_SAPI !== 'cli-server' || ! in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit;
}
$ruta = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$directorio = getenv('RM_COHORTE30_PANTALLAS');
$archivo = null;
$tipo = null;
if (is_string($directorio) && preg_match('~^/(S01|S04|S07|S10)\.html$~D', $ruta ?? '', $m)) {
    $archivo = $directorio.'/'.$m[1].'.html';
    $tipo = 'text/html; charset=utf-8';
} elseif (preg_match('~^/assets/([A-Za-z0-9_.-]+\.(css|woff2?|svg|png))$~D', $ruta ?? '', $m)) {
    $archivo = dirname(__DIR__).'/public/build/assets/'.$m[1];
    $tipo = match ($m[2]) {
        'css' => 'text/css; charset=utf-8', 'woff' => 'font/woff', 'woff2' => 'font/woff2', 'svg' => 'image/svg+xml', 'png' => 'image/png',
    };
}
if ($archivo === null || ! is_file($archivo) || is_link($archivo)) {
    http_response_code(404);
    exit;
}
if ($tipo === 'text/html; charset=utf-8' && ! str_contains(file_get_contents($archivo), 'DATOS SINTÉTICOS · CASO FICTICIO')) {
    http_response_code(403);
    exit;
}
header('Content-Type: '.$tipo);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
readfile($archivo);
