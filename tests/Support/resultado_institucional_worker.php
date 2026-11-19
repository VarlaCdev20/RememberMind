<?php

declare(strict_types=1);

use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GuardarResultadoInstitucional;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Tests\Support\EntornoPruebas;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$datos = json_decode(file_get_contents($argv[1]), true, flags: JSON_THROW_ON_ERROR);
$c = $datos['conexion'];
if (! EntornoPruebas::postgresDesechable($c['database'], $c['username'], $c['host'])) { throw new RuntimeException('Se exige PostgreSQL desechable.'); }
foreach (['APP_ENV' => 'testing', 'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'APP_CONFIG_CACHE' => $datos['directorio'].'/ausente.php'] as $k => $v) {
    putenv($k.'='.$v); $_ENV[$k] = $_SERVER[$k] = $v;
}
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['database.default' => 'pgsql', 'database.connections.pgsql' => $c]); DB::purge('pgsql');
$u = User::findOrFail($datos['usuario']); auth()->login($u);
touch($datos['directorio'].'/listo-'.$argv[2]);
$barrera = fopen($datos['directorio'].'/barrera', 'r'); flock($barrera, LOCK_SH); flock($barrera, LOCK_UN); fclose($barrera);
$d = app(GuardarResultadoInstitucional::class)->ejecutar($u, Residente::findOrFail($datos['residente']), $datos['solicitud']);
echo json_encode(['id' => $d['contenido']['id'], 'huella' => $d['huella']], JSON_THROW_ON_ERROR);
