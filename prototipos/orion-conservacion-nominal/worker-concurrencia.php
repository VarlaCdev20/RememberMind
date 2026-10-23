<?php

declare(strict_types=1);

// Sólo el runner efímero: no inicia Laravel ni admite otra identidad SQL.
require dirname(__DIR__, 2).'/vendor/autoload.php';
foreach (['GuardiaDesechable', 'EntradasCompuestas'] as $clase) {
    require_once dirname(__DIR__).'/orion-conservacion-0_2/'.$clase.'.php';
}
foreach (['ContratoNominal', 'EsquemaNominal', 'ProyeccionNominal', 'ContenidoCompleto', 'RepositorioNominal'] as $clase) {
    require_once __DIR__.'/'.$clase.'.php';
}

use MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional;
use OrionFase02\GuardiaDesechable;
use OrionNominal\RepositorioNominal;

[$script, $direccion, $modo, $archivo, $solicitud, $fuente, $propietario] = $argv;
if (! preg_match('/^127\.0\.0\.1:[0-9]+$/D', $direccion)
    || ! in_array($modo, ['INSERTAR_HIJA', 'REINTENTAR'], true)) {
    throw new RuntimeException('Interfaz de ensayo inválida.');
}
$ruta = realpath($archivo);
if ($ruta === false || basename($ruta) !== 'caso-sintetico.php'
    || ! preg_match('/^remembermind-nominal-[a-f0-9]{20}$/D', basename(dirname($ruta)))) {
    throw new RuntimeException('El worker requiere el caso privado del ensayo.');
}
$db = GuardiaDesechable::conectar();
$completo = ($argv[7] ?? null) === 'COMPLETO';
$mapa = $completo ? require __DIR__.'/mapa-completo.php' : null;
$repo = new RepositorioNominal($db, $mapa);
$caso = require $ruta;
$dto = InstantaneaEvaluacionInstitucional::verificar($caso['contenido'], $caso['huella']);
if ($dto->contenido['instrumental']['contenido']['contexto']['residente'] !== 'RES-TEST') {
    throw new RuntimeException('El worker sólo admite el residente sintético del test.');
}
$destino = $dto->contenido['instrumental']['contenido']['contexto']['evaluacion'];
$socket = stream_socket_client('tcp://'.$direccion, $errno, $error, 10);
if ($socket === false) {
    throw new RuntimeException('Sin coordinador de concurrencia.');
}
stream_set_timeout($socket, 20);
try {
    fwrite($socket, json_encode(['pid' => (int) $db->query('SELECT pg_backend_pid()')->fetchColumn()], JSON_THROW_ON_ERROR)."\n");
    if (trim((string) fgets($socket)) !== 'GO') {
        throw new RuntimeException('El coordinador no autorizó la competencia.');
    }
    if ($modo === 'REINTENTAR') {
        $resultado = ['nuevo' => $repo->guardar($destino, $solicitud, $dto, $caso['fuente'])];
    } else {
        $tabla = $completo ? $mapa['modelos']['c002']['tabla'] : 'capturas_adquiridas';
        $fk = $completo ? 'cod_propietario' : 'cod_entrada_institucional';
        $q = $db->prepare("SELECT * FROM $tabla WHERE cod_evaluacion_experta=? AND $fk IS NOT NULL LIMIT 1");
        $q->execute([$fuente]);
        $fila = $q->fetch(PDO::FETCH_ASSOC) ?: throw new RuntimeException('Sin captura sintética original.');
        $fila['cod_fila'] = bin2hex(random_bytes(10));
        $fila['cod_evaluacion_experta'] = $destino;
        $fila[$fk] = $propietario;
        $fila['posicion_coleccion'] = 999;
        try {
            $q = $db->prepare('INSERT INTO '.$tabla.'('.implode(',', array_keys($fila)).') VALUES ('.implode(',', array_fill(0, count($fila), '?')).')');
            foreach (array_values($fila) as $i => $dato) {
                $q->bindValue($i + 1, $dato, is_bool($dato) ? PDO::PARAM_BOOL : ($dato === null ? PDO::PARAM_NULL : PDO::PARAM_STR));
            }
            $q->execute();
            $resultado = ['insertada' => true];
        } catch (PDOException $e) {
            $resultado = ['rechazo_sqlstate' => $e->getCode()];
        }
    }
    fwrite($socket, json_encode($resultado, JSON_THROW_ON_ERROR)."\n");
} finally {
    fclose($socket);
}
