<?php

use App\Models\User;
use App\Modulos\Medicacion\Aplicacion\Acciones\RegistrarAdministracionMedicacionService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Support\EntornoPruebas;

// Solo datos sintéticos y conexión PostgreSQL desechable proporcionados por el runner.
require dirname(__DIR__, 2).'/vendor/autoload.php';
putenv('APP_ENV=testing');
putenv('DB_URL=');
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$datos = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
$conexion = $datos['conexion'];
if (! EntornoPruebas::postgresDesechable($conexion['database'], $conexion['username'], $conexion['host'])) {
    throw new RuntimeException('Base no desechable.');
}
config(['app.env' => 'testing', 'database.default' => 'pgsql', 'database.connections.pgsql' => $conexion]);
DB::purge('pgsql');
Carbon\Carbon::setTestNow($datos['momento']);
$usuario = User::findOrFail($datos['usuario']);
touch($datos['directorio'].'/listo-'.$argv[2]);
$barrera = fopen($datos['directorio'].'/barrera', 'r');
flock($barrera, LOCK_SH);
flock($barrera, LOCK_UN);
fclose($barrera);
try {
    app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
        $usuario, $datos['residente'], $datos['prescripcion'], '08:00', true, dosisAdministrada: '50', fechaHoraAdministracion: '2026-10-14 08:10:00', codHorario: $datos['horario']);
    echo json_encode(['resultado' => 'guardada'], JSON_THROW_ON_ERROR);
} catch (ValidationException $error) {
    if (! str_contains(json_encode($error->errors()), 'ya tiene')) {
        throw $error;
    }
    echo json_encode(['resultado' => 'rechazada'],JSON_THROW_ON_ERROR);
}
