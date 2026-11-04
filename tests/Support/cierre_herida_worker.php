<?php

use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Acciones\RegistrarCuidadosDeTurno;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Support\EntornoPruebas;

require dirname(__DIR__).'/bootstrap.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! $app->environment('testing') || config('database.default') !== 'pgsql'
    || ! EntornoPruebas::postgresDesechable(config('database.connections.pgsql.database'), config('database.connections.pgsql.username'), config('database.connections.pgsql.host'))) {
    throw new RuntimeException('El worker exige PostgreSQL desechable.');
}
[$script, $direccion, $usuario, $residente, $herida, $marca, $fecha] = $argv;
Carbon::setTestNow($fecha);
config(['remembermind.acceso_clinico_temporal_superadmin' => false]);
Auth::login(User::findOrFail($usuario));
$pid = DB::selectOne('select pg_backend_pid() as pid')->pid;
$socket = stream_socket_client('tcp://'.$direccion, $codigo, $mensaje, 10);
if (! $socket) {
    throw new RuntimeException('Barrera de prueba no disponible.');
}
stream_set_timeout($socket, 20);
fwrite($socket, json_encode(['ready' => true, 'pid' => $pid])."\n");
if (trim((string) fgets($socket)) !== 'GO') {
    throw new RuntimeException('Barrera no liberada.');
}
$inicio = hrtime(true);
try {
    app(RegistrarCuidadosDeTurno::class)->cerrarLesion(Auth::user(), [
        'codResidente' => $residente, 'resultadoCierreLesion' => 'Resultado sintético '.$marca,
        'motivoCierreLesion' => 'Motivo concurrente '.$marca,
    ], $herida);
    $resultado = ['result' => 'CERRADA'];
} catch (ValidationException $error) {
    $resultado = ['result' => 'RECHAZADA', 'errors' => array_keys($error->errors())];
} catch (Throwable $error) {
    $resultado = ['result' => 'ERROR', 'class' => $error::class];
}
fwrite($socket, json_encode($resultado + ['marca' => $marca, 'start' => $inicio, 'end' => hrtime(true)])."\n");
fclose($socket);
exit($resultado['result'] === 'ERROR' ? 1 : 0);
