<?php

declare(strict_types=1);

use App\Models\Instrumento;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Valoraciones\Aplicacion\Acciones\AplicarInstrumento;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\EntornoPruebas;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$datos = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
$c = $datos['conexion'];
if (! EntornoPruebas::postgresDesechable($c['database'], $c['username'], $c['host'])) {
    throw new RuntimeException('La carrera requiere el runner PostgreSQL desechable.');
}
foreach (['APP_ENV' => 'testing', 'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array',
    'APP_CONFIG_CACHE' => $datos['directorio'].'/cache-ausente.php'] as $n => $v) {
    putenv($n.'='.$v);
    $_ENV[$n] = $_SERVER[$n] = $v;
}
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['database.default' => 'pgsql', 'database.connections.pgsql' => $c,
    'filesystems.disks.local' => ['driver' => 'local', 'root' => $datos['directorio']]]);
DB::purge('pgsql');
$i = (int) $argv[2];
$usuario = User::query()->findOrFail($datos['usuarios'][$i]);
auth()->login($usuario);
touch($datos['directorio'].'/listo-'.$i);
$barrera = fopen($datos['directorio'].'/barrera', 'r');
flock($barrera, LOCK_SH);
flock($barrera, LOCK_UN);
fclose($barrera);
$entrada = $datos['entrada'];
if ($datos['operacion'] === 'rectificacion') {
    $entrada['respuestas'][0]['valor_numero'] = $i + 2;
}
try {
    $a = app(AplicarInstrumento::class)->transcribir($usuario, $entrada,
        Instrumento::query()->findOrFail($datos['instrumento']), Residente::query()->findOrFail($datos['residente']));
    echo json_encode(['estado' => 'guardado', 'codigo' => $a->getKey()], JSON_THROW_ON_ERROR);
} catch (HttpException $e) {
    if ($e->getStatusCode() !== 409) {
        throw $e;
    }
    echo json_encode(['estado' => 'conflicto'], JSON_THROW_ON_ERROR);
}
