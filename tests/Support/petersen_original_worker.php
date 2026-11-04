<?php

declare(strict_types=1);

use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarValoracionPetersenOriginal;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Tests\Support\EntornoPruebas;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$datos = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
$c = $datos['conexion'];
if (! EntornoPruebas::postgresDesechable($c['database'], $c['username'], $c['host'])) {
    throw new RuntimeException('La carrera exige un runner PostgreSQL desechable.');
}
foreach (['APP_ENV' => 'testing', 'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array',
    'APP_CONFIG_CACHE' => $datos['directorio'].'/cache-ausente.php'] as $nombre => $valor) {
    putenv($nombre.'='.$valor);
    $_ENV[$nombre] = $_SERVER[$nombre] = $valor;
}
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['database.default' => 'pgsql', 'database.connections.pgsql' => $c]);
DB::purge('pgsql');
$usuario = User::query()->findOrFail($datos['usuario']);
auth()->login($usuario);
touch($datos['directorio'].'/listo-'.$argv[2]);
$barrera = fopen($datos['directorio'].'/barrera', 'r');
flock($barrera, LOCK_SH);
flock($barrera, LOCK_UN);
fclose($barrera);
$entrada = $datos['entrada'];
if ($datos['operacion'] === 'rectificacion') {
    $entrada['solicitud_uuid'] = $datos['solicitudes'][(int) $argv[2]];
    $entrada['justificacion'] .= ' Propuesta '.$argv[2];
}
try {
    $v = app(RegistrarValoracionPetersenOriginal::class)->ejecutar($usuario, Residente::query()->findOrFail($datos['residente']), $entrada);
    echo json_encode(['estado' => 'guardado', 'codigo' => $v->getKey()], JSON_THROW_ON_ERROR);
} catch (DomainException $ex) {
    if (! str_contains($ex->getMessage(), 'cadena cambió')) {
        throw $ex;
    }
    echo json_encode(['estado' => 'conflicto'], JSON_THROW_ON_ERROR);
}
