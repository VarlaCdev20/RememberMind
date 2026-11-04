<?php

use App\Models\PaseTurno;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Acciones\PaseTurnoService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\EntornoPruebas;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$datos = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
$conexion = $datos['conexion'];
if (! EntornoPruebas::postgresDesechable($conexion['database'], $conexion['username'], $conexion['host'])) {
    throw new RuntimeException('Se requiere la conexión PostgreSQL propia del runner.');
}
foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'pgsql', 'DB_DATABASE' => $conexion['database'], 'DB_USERNAME' => $conexion['username'], 'DB_PASSWORD' => $conexion['password'], 'DB_HOST' => $conexion['host'], 'DB_PORT' => $conexion['port'], 'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'APP_CONFIG_CACHE' => $datos['directorio'].'/config-absent.php'] as $nombre => $valor) {
    putenv($nombre.'='.$valor);
    $_ENV[$nombre] = $_SERVER[$nombre] = $valor;
}
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['app.env' => 'testing', 'database.default' => 'pgsql', 'database.connections.pgsql' => $conexion]);
DB::purge('pgsql');
Carbon\Carbon::setTestNow($datos['momento']);
$usuario = User::findOrFail($datos['usuario']);
auth()->login($usuario);
touch($datos['directorio'].'/listo-'.$argv[2]);
$barrera = fopen($datos['directorio'].'/barrera', 'r');
flock($barrera, LOCK_SH);
flock($barrera, LOCK_UN);
fclose($barrera);
try {
    $service = app(PaseTurnoService::class);
    if ($datos['operacion'] === 'recibir') {
        $service->confirmarRecepcion(PaseTurno::findOrFail($datos['pase']), 'Recepción concurrente sintética', $usuario);
    } elseif ($argv[2] === '1') {
        $service->confirmarEntrega($usuario, ['cod_residente' => $datos['residente'], 'resumen' => 'Entrega concurrente sintética']);
    } else {
        $service->emitir($usuario, $datos['residente'], $datos['entrada']);
    }
    echo json_encode(['resultado' => 'guardado'], JSON_THROW_ON_ERROR);
} catch (ValidationException $exception) {
    if (! isset($exception->errors()['cod_residente'])) {
        throw $exception;
    }
    echo json_encode(['resultado' => 'rechazado'], JSON_THROW_ON_ERROR);
} catch (HttpException $exception) {
    if ($exception->getStatusCode() !== 409) {
        throw $exception;
    }
    echo json_encode(['resultado' => 'rechazado'], JSON_THROW_ON_ERROR);
}
