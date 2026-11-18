<?php

declare(strict_types=1);

$database = getenv('RM_CONTEXTO_DATABASE');
$host = getenv('RM_CONTEXTO_HOST');
$port = (int) getenv('RM_CONTEXTO_PORT');
if (! is_string($database) || ! str_ends_with($database, '_testing') || $host !== '127.0.0.1' || $port === 5432) {
    throw new RuntimeException('El trabajador requiere el PostgreSQL desechable del ensayo.');
}
$pdo = new PDO('pgsql:host='.$host.';port='.$port.';dbname='.$database,
    getenv('RM_CONTEXTO_USER'), getenv('RM_CONTEXTO_PASSWORD'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec("SET lock_timeout='300ms'");
try {
    $query = $pdo->prepare('UPDATE atenciones SET cod_residente=? WHERE cod_atencion=?');
    $query->execute(['SINTETICO-OTRO', 'SIM-AT']);
    echo json_encode(['estado' => 'ACTUALIZADO'], JSON_THROW_ON_ERROR);
} catch (PDOException $error) {
    echo json_encode(['estado' => $error->getCode()], JSON_THROW_ON_ERROR);
}
