<?php

use App\Backend\Modulos\Admisiones\Acciones\FormalizarAdmision;
use App\Models\Preadmision;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! $app->environment('testing') || config('database.default') !== 'pgsql'
    || ! preg_match('/^remembermind_f3_test_[a-f0-9]{8}$/', config('database.connections.pgsql.database'))) {
    throw new RuntimeException('El worker solo opera en PostgreSQL desechable de Fase 3.');
}
[$script, $direccion, $codUsuario, $codPreadmision, $codCama] = $argv;
Auth::login(User::findOrFail($codUsuario));
$pid = DB::selectOne('select pg_backend_pid() as pid')->pid;
$socket = stream_socket_client('tcp://'.$direccion, $codigo, $mensaje, 10);
if (! $socket) {
    throw new RuntimeException('No se pudo conectar a la barrera local de pruebas.');
}
stream_set_timeout($socket, 20);
fwrite($socket, json_encode(['ready' => true, 'pid' => $pid])."\n");
if (trim((string) fgets($socket)) !== 'GO') {
    throw new RuntimeException('No se recibió liberación de la barrera.');
}
$inicio = hrtime(true);
try {
    $residente = app(FormalizarAdmision::class)->ejecutar(Preadmision::findOrFail($codPreadmision), [
        'cod_cama' => $codCama,
        'contacto' => ['nombres' => 'Contacto sintético concurrente', 'apellido_paterno' => 'Prueba'],
    ], Auth::user());
    $resultado = ['result' => 'ADMITIDA', 'residente' => $residente->cod_residente];
} catch (ValidationException $e) {
    $resultado = ['result' => 'RECHAZADA', 'errors' => array_keys($e->errors())];
} catch (Throwable $e) {
    // El proceso padre necesita distinguir un fallo técnico de una denegación válida.
    $resultado = ['result' => 'ERROR', 'class' => $e::class];
}
fwrite($socket, json_encode($resultado + ['pid' => $pid, 'start' => $inicio, 'end' => hrtime(true)])."\n");
fclose($socket);
exit($resultado['result'] === 'ERROR' ? 1 : 0);
