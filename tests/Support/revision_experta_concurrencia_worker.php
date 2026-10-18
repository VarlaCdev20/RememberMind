<?php

declare(strict_types=1);

use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RevisarValoracionExperta;
use App\Modulos\ApoyoDecision\Dominio\Revision\DisciplinaRevision;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Tests\Support\EntornoPruebas;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$datos = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
$conexion = $datos['conexion'];
if (! EntornoPruebas::postgresDesechable($conexion['database'], $conexion['username'], $conexion['host'])) {
    throw new RuntimeException('La carrera exige PostgreSQL propio del runner.');
}
foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'pgsql', 'DB_DATABASE' => $conexion['database'],
    'DB_USERNAME' => $conexion['username'], 'DB_PASSWORD' => $conexion['password'], 'DB_HOST' => $conexion['host'], 'DB_PORT' => $conexion['port'],
    'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync',
    'APP_CONFIG_CACHE' => $datos['directorio'].'/config-absent.php'] as $nombre => $valor) {
    putenv($nombre.'='.$valor);
    $_ENV[$nombre] = $_SERVER[$nombre] = $valor;
}
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['database.default' => 'pgsql', 'database.connections.pgsql' => $conexion, 'sistema_experto.lectura_clinica.VER_TEST' => $datos['contrato']]);
DB::purge('pgsql');
$usuario = User::findOrFail($datos['usuarios'][$argv[2]]);
auth()->login($usuario);
touch($datos['directorio'].'/listo-'.$argv[2]);
$barrera = fopen($datos['directorio'].'/barrera', 'r');
flock($barrera, LOCK_SH);
flock($barrera, LOCK_UN);
fclose($barrera);
try {
    app(RevisarValoracionExperta::class)->ejecutar($usuario, 'E_REV', DisciplinaRevision::MEDICINA, 'CONFIRMADA', 'Revisión concurrente '.$argv[2]);
    echo json_encode(['estado' => 'guardado'], JSON_THROW_ON_ERROR);
} catch (DomainException $e) {
    if (! str_contains($e->getMessage(), 'Recargue')) {
        throw $e;
    }
    echo json_encode(['estado' => 'rechazado'], JSON_THROW_ON_ERROR);
}
