<?php

declare(strict_types=1);

// Crea y elimina exclusivamente una base y runner nuevos con nombres aleatorios.
$raiz = dirname(__DIR__, 2);
require $raiz.'/vendor/autoload.php';
$app = require $raiz.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$conexion = Illuminate\Support\Facades\DB::connection();
if ($conexion->getDriverName() !== 'pgsql' || ! in_array($conexion->getConfig('host'), ['127.0.0.1', 'localhost'], true)) {
    throw new RuntimeException('El ensayo exige PostgreSQL local como conexión de administración.');
}
$pdo = $conexion->getPdo();
$base = 'remembermind_test_'.bin2hex(random_bytes(4)).'_testing';
$runner = $base.'_runner';
$secreto = bin2hex(random_bytes(24));
$rolCreado = $baseCreada = false;
$configTemporal = null;
$resultado = 1;

try {
    $pdo->exec('CREATE ROLE "'.$runner.'" LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE PASSWORD '.$pdo->quote($secreto));
    $rolCreado = true;
    $pdo->exec('CREATE DATABASE "'.$base.'" OWNER "'.$runner.'" TEMPLATE template0');
    $baseCreada = true;
    $pdo->exec('REVOKE CONNECT ON DATABASE "'.$base.'" FROM PUBLIC');

    $xml = new DOMDocument();
    $xml->load($raiz.'/phpunit.xml');
    $xml->documentElement->setAttribute('bootstrap', $raiz.'/tests/bootstrap.php');
    foreach ($xml->getElementsByTagName('directory') as $directorio) {
        $directorio->nodeValue = $raiz.'/'.trim($directorio->textContent);
    }
    $variables = [
        'RM_TEST_PROFILE' => 'postgres', 'DB_CONNECTION' => 'pgsql', 'DB_DATABASE' => $base,
        'DB_USERNAME' => $runner, 'DB_PASSWORD' => $secreto, 'DB_HOST' => '127.0.0.1',
        'DB_PORT' => (string) $conexion->getConfig('port'), 'DB_URL' => '',
        'AUTH_REMEMBER_ENABLED' => 'false',
    ];
    $bloquePhp = $xml->getElementsByTagName('php')->item(0);
    foreach ($variables as $nombre => $valor) {
        $elemento = null;
        foreach ($bloquePhp->getElementsByTagName('env') as $existente) {
            if ($existente->getAttribute('name') === $nombre) { $elemento = $existente; break; }
        }
        if (! $elemento) { $elemento = $xml->createElement('env'); $bloquePhp->appendChild($elemento); }
        $elemento->setAttribute('name', $nombre);
        $elemento->setAttribute('value', $valor);
        $elemento->setAttribute('force', 'true');
    }
    $configTemporal = tempnam(sys_get_temp_dir(), 'remembermind-recordarme-');
    if ($configTemporal === false || $xml->save($configTemporal) === false) {
        throw new RuntimeException('No se pudo preparar la configuración temporal de pruebas.');
    }
    $entorno = getenv();
    foreach ($bloquePhp->getElementsByTagName('env') as $variable) {
        $entorno[$variable->getAttribute('name')] = $variable->getAttribute('value');
    }
    $proceso = proc_open([PHP_BINARY, $raiz.'/vendor/bin/phpunit', '--configuration', $configTemporal,
        $raiz.'/tests/Feature/RecordarmeTest.php'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $tuberias, $raiz,
        $entorno, ['bypass_shell' => true]);
    if (! is_resource($proceso)) throw new RuntimeException('No inició el proceso de pruebas.');
    $salida = stream_get_contents($tuberias[1]); fclose($tuberias[1]);
    $errores = stream_get_contents($tuberias[2]); fclose($tuberias[2]);
    $resultado = proc_close($proceso);
    $evidencia = str_replace($secreto, '[SECRETO_DE_ENSAYO_OMITIDO]', $salida.$errores);
    file_put_contents($raiz.'/output/pruebas-recordarme-postgres-20261026.txt', $evidencia);
    echo $evidencia;
} catch (Throwable $error) {
    echo 'Ensayo PostgreSQL no completado: '.get_class($error).PHP_EOL;
} finally {
    if ($configTemporal && is_file($configTemporal)) unlink($configTemporal);
    // Defensa adicional antes de cualquier eliminación: solo identidades generadas aquí.
    if (! preg_match('/^remembermind_test_[a-f0-9]{8}_testing$/D', $base) || $runner !== $base.'_runner') {
        throw new RuntimeException('Identidad de limpieza inválida.');
    }
    if ($baseCreada) $pdo->exec('DROP DATABASE "'.$base.'" WITH (FORCE)');
    if ($rolCreado) $pdo->exec('DROP ROLE "'.$runner.'"');
    echo 'Base y runner desechables eliminados; base institucional sin migrar en este ensayo.'.PHP_EOL;
}
exit($resultado);
