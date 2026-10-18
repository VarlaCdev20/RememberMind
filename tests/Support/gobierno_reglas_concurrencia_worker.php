<?php

declare(strict_types=1);

use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\EjecutarObservaciones;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GestionarReglasExpertas;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\EntornoPruebas;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$datos = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
$conexion = $datos['conexion'];
if (! EntornoPruebas::postgresDesechable($conexion['database'], $conexion['username'], $conexion['host'])) {
    throw new RuntimeException('La carrera exige PostgreSQL desechable del runner.');
}
foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'pgsql', 'DB_DATABASE' => $conexion['database'],
    'DB_USERNAME' => $conexion['username'], 'DB_PASSWORD' => $conexion['password'], 'DB_HOST' => $conexion['host'],
    'DB_PORT' => $conexion['port'], 'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array',
    'QUEUE_CONNECTION' => 'sync', 'APP_CONFIG_CACHE' => $datos['directorio'].'/config-absent.php'] as $nombre => $valor) {
    putenv($nombre.'='.$valor);
    $_ENV[$nombre] = $_SERVER[$nombre] = $valor;
}
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['database.default' => 'pgsql', 'database.connections.pgsql' => $conexion]);
DB::purge('pgsql');
$indice = (int) $argv[2];
$usuario = User::query()->findOrFail($datos['usuarios'][$indice]);
auth()->login($usuario);
touch($datos['directorio'].'/listo-'.$indice);
$barrera = fopen($datos['directorio'].'/barrera', 'r');
flock($barrera, LOCK_SH);
flock($barrera, LOCK_UN);
fclose($barrera);
try {
    $gestor = app(GestionarReglasExpertas::class);
    if (str_starts_with($datos['operacion'], 'ejecucion_')) {
        if ($indice === 1) {
            app(EjecutarObservaciones::class)->ejecutar($usuario, Residente::query()->findOrFail($datos['residente']), (string) Str::uuid());
        } elseif ($datos['operacion'] === 'ejecucion_rechazo') {
            $gestor->revisar($usuario, $datos['regla'], 'RECHAZADA', 'Decisión concurrente posterior o anterior al cálculo.');
        } else {
            $gestor->crearRevision($usuario, $datos['version'], 'ORION-RACE-OBS', 'Archivar en concurrencia conservando todas las historias.', retiradas: [$datos['regla']]);
        }
    } elseif ($datos['operacion'] === 'revision' || ($datos['operacion'] === 'mixta' && $indice === 1)) {
        $gestor->revisar($usuario, 'RULE_A', $indice === 1 ? 'APROBADA' : 'RECHAZADA', 'Decisión concurrente '.$indice);
    } else {
        $gestor->crearRevision($usuario, 'VER_TEST', 'ORION-RACE-'.$indice, 'Revisión concurrente de conocimiento '.$indice);
    }
    echo json_encode(['estado' => 'guardado'], JSON_THROW_ON_ERROR);
} catch (DomainException $e) {
    if (! str_contains($e->getMessage(), 'decisión original') && ! str_contains($e->getMessage(), 'retirada')) {
        throw $e;
    }
    echo json_encode(['estado' => 'conflicto'], JSON_THROW_ON_ERROR);
}
