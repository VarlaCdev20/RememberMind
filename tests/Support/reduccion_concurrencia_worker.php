<?php

declare(strict_types=1);

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\AlmacenInstantaneas;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use MotorExperto\dominio\orion\Services\Dcl\ClasificadorPetersenService;
use Tests\Support\EntornoPruebas;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as E;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$datos = json_decode(file_get_contents($argv[1]), true, flags: JSON_THROW_ON_ERROR);
$c = $datos['conexion'];
if (! EntornoPruebas::postgresDesechable($c['database'], $c['username'], $c['host'])) {
    throw new RuntimeException('Solo se admite el PostgreSQL desechable propio.');
}
foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'pgsql', 'DB_DATABASE' => $c['database'],
    'DB_USERNAME' => $c['username'], 'DB_PASSWORD' => $c['password'], 'DB_HOST' => $c['host'], 'DB_PORT' => $c['port'],
    'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync',
    'APP_CONFIG_CACHE' => $datos['directorio'].'/config-ausente.php'] as $nombre => $valor) {
    putenv($nombre.'='.$valor);
    $_ENV[$nombre] = $_SERVER[$nombre] = $valor;
}
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['database.default' => 'pgsql', 'database.connections.pgsql' => $c]);
DB::purge('pgsql');
$i = $argv[2];
touch($datos['directorio'].'/listo-'.$i);
$barrera = fopen($datos['directorio'].'/barrera', 'r');
flock($barrera, LOCK_SH);
flock($barrera, LOCK_UN);
fclose($barrera);
try {
    DB::transaction(function () use ($datos, $i): void {
        DB::statement("SET LOCAL lock_timeout='10s'");
        if ($datos['modo'] === 'clasificacion') {
            (new AlmacenInstantaneas('pgsql'))->guardarClasificacion((new ClasificadorPetersenService)->clasificar(E::perfil()));
        } elseif ($datos['modo'] === 'iadl') {
            DB::table('hechos_instantanea_experta')->where('cod_hecho_instantanea', $datos['hecho'])->update($datos['cambios'][$i]);
        } elseif ($datos['modo'] === 'padre' && $i === '2') {
            DB::table('evaluacion_criterios')->where('cod_evaluacion_criterio', $datos['criterio'])->update(['cod_evaluacion_experta' => 'SIM-EVAL-OTRA']);
        } else {
            DB::table('evidencias_criterio_evaluacion')->insert($datos['vinculos'][$i]);
        }
        // Mantener el ganador sin confirmar fuerza solapamiento real con el competidor.
        DB::select('SELECT pg_sleep(0.15)');
    });
    echo 'guardado';
} catch (QueryException $e) {
    if (! str_contains($e->getMessage(), 'red_hecho_anexo_unico')
        && ! str_contains($e->getMessage(), 'red_vinculo_coleccion')
        && ! str_contains($e->getMessage(), 'red_vinculo_contexto')
        && ! str_contains($e->getMessage(), 'red_contexto_padre_inmutable')) {
        throw $e;
    }
    echo 'rechazado';
}
