<?php

declare(strict_types=1);
use App\Models\Alerta;
use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Acciones\AlertasService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\EntornoPruebas;

require __DIR__.'/EntornoPruebas.php';
EntornoPruebas::validarVariables();
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! $app->environment('testing') || DB::getDriverName() !== 'pgsql') {
    throw new RuntimeException('El worker exige PostgreSQL testing desechable.');
}
[$script, $usuarioId, $alertaId, $barrera, $indice] = $argv;
$usuario = User::findOrFail($usuarioId);
Auth::setUser($usuario);
file_put_contents($barrera.'/'.$indice.'.ready', 'ready');
$limite = microtime(true) + 30;
while (! file_exists($barrera.'/go')) {
    if (microtime(true) > $limite) {
        throw new RuntimeException('La barrera de concurrencia no fue liberada.');
    }
    usleep(2000);
}
try {
    app(AlertasService::class)->cerrar(
        Alerta::findOrFail($alertaId), 'Cierre operativo sintético concurrente.', $usuario, coordinacion: true);
    echo 'CERRADA';
} catch (HttpException $e) {
    if ($e->getStatusCode() !== 409) {
        throw $e;
    }
    echo 'CONFLICTO';
}
