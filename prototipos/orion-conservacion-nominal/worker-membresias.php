<?php

declare(strict_types=1);

// Protocolo de pruebas sintéticas con tres conexiones. No es un endpoint ni acepta otra base.
require dirname(__DIR__, 2).'/vendor/autoload.php';
require dirname(__DIR__).'/orion-conservacion-0_2/GuardiaDesechable.php';
require __DIR__.'/RepositorioNominal.php';
require __DIR__.'/MembresiasSQL.php';

use MotorExperto\soporte\conservacion\InstantaneaPetersen;
use OrionFase02\GuardiaDesechable;
use OrionNominal\MembresiasSQL;

if (count($argv) !== 3 || ! preg_match('/^127\.0\.0\.1:[0-9]+$/D', $argv[1])
    || ! in_array($argv[2], ['INSERTAR', 'REINTENTAR'], true)) {
    throw new RuntimeException('Interfaz de ensayo inválida.');
}
$db = GuardiaDesechable::conectar();
$socket = stream_socket_client('tcp://'.$argv[1], $errno, $error, 10);
if ($socket === false) {
    throw new RuntimeException('Sin coordinador de pruebas.');
}
stream_set_timeout($socket, 25);
try {
    fwrite($socket, json_encode(['pid' => (int) $db->query('SELECT pg_backend_pid()')->fetchColumn()], JSON_THROW_ON_ERROR)."\n");
    $entrada = json_decode((string) fgets($socket, 2_000_000), true, flags: JSON_THROW_ON_ERROR);
    if (($entrada['evaluacion'] ?? null) !== 'MEMBRESIAS-TEST') {
        throw new RuntimeException('El worker sólo admite la identidad explícita del test.');
    }
    $repo = new MembresiasSQL($db, new DateTimeZone($entrada['zona_original']));
    if ($argv[2] === 'REINTENTAR') {
        $dto = InstantaneaPetersen::verificar($entrada['contenido'], $entrada['huella']);
        $salida = ['nuevo' => $repo->guardar('MEMBRESIAS-TEST', $dto)];
    } else {
        $fila = $entrada['fila'];
        if (array_diff(array_keys($fila), ['cod_soporte_adquirido', 'cod_juicio_adquirido',
            'cod_soporte_petersen', 'orden_membresia']) || count($fila) !== 4) {
            throw new RuntimeException('Fila de ensayo fuera del contrato.');
        }
        try {
            $q = $db->prepare('INSERT INTO soportes_petersen_adquiridos('.implode(',', array_keys($fila)).') VALUES (?,?,?,?)');
            $q->execute(array_values($fila));
            $salida = ['insertada' => true];
        } catch (PDOException $e) {
            $salida = ['sqlstate' => $e->getCode()];
        }
    }
    fwrite($socket, json_encode($salida, JSON_THROW_ON_ERROR)."\n");
} finally {
    fclose($socket);
}
