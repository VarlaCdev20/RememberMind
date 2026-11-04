<?php

declare(strict_types=1);

use App\Models\{Residente, User};
use App\Modulos\Valoraciones\Aplicacion\Acciones\RegistrarControlFuncionalIadl;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Support\EntornoPruebas;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$contexto = json_decode(file_get_contents($argv[1]), true, flags: JSON_THROW_ON_ERROR);
$c = $contexto['conexion'];
if (($c['driver'] ?? '') !== 'pgsql' || ! EntornoPruebas::postgresDesechable($c['database'], $c['username'], $c['host'])) {
    throw new RuntimeException('Solo admite PostgreSQL desechable.');
}
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! app()->environment('testing')) throw new RuntimeException('Solo admite testing.');
config(['database.default' => 'pgsql', 'database.connections.pgsql' => $c, 'session.driver' => 'array']);
DB::purge('pgsql');
$usuario = User::findOrFail($contexto['usuario']);
auth()->login($usuario);
$numero = (int) $argv[2];
file_put_contents($contexto['directorio'].'/listo-'.$numero, 'listo');
$barrera = fopen($contexto['directorio'].'/barrera', 'r');
flock($barrera, LOCK_SH);
flock($barrera, LOCK_UN);
fclose($barrera);
try {
    $registro = app(RegistrarControlFuncionalIadl::class)->registrar($usuario, Residente::findOrFail($contexto['residente']),
        $contexto['entrada'], $contexto['codigos'][$numero], $contexto['anterior']);
    echo json_encode(['estado' => 'OK', 'registro' => $registro->getKey()], JSON_THROW_ON_ERROR);
} catch (ValidationException) {
    echo json_encode(['estado' => 'RECHAZADO'], JSON_THROW_ON_ERROR);
}
