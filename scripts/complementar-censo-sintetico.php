<?php

use Database\Seeders\ComplementarCensoSinteticoSeeder;
use Database\Seeders\HistoriaCuidadosResidentesSeeder;
use Database\Seeders\SeguimientoResidentesSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$opciones = getopt('', ['aplicar', 'pg-dump:', 'cuidados', 'seguimiento']);
$aplicar = array_key_exists('aplicar', $opciones);
$seeder = match (true) {
    array_key_exists('seguimiento', $opciones) => SeguimientoResidentesSeeder::class,
    array_key_exists('cuidados', $opciones) => HistoriaCuidadosResidentesSeeder::class,
    default => ComplementarCensoSinteticoSeeder::class,
};

try {
    ComplementarCensoSinteticoSeeder::comprobarDestino();
    if ($aplicar) {
        $programa = $opciones['pg-dump'] ?? 'pg_dump';
        $directorio = storage_path('app/private/censo-sintetico');
        if (! is_dir($directorio) && ! mkdir($directorio, 0700, true) && ! is_dir($directorio)) {
            throw new RuntimeException('No se pudo preparar el directorio privado de respaldo.');
        }

        $archivo = $directorio.DIRECTORY_SEPARATOR.'antes-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(4)).'.dump';
        $conexion = DB::connection();
        $respaldo = new Process([
            $programa, '--no-password', '--format=custom', '--file='.$archivo,
            '--host='.$conexion->getConfig('host'), '--port='.$conexion->getConfig('port'),
            '--username='.$conexion->getConfig('username'), '--dbname='.$conexion->getDatabaseName(),
        ], null, ['PGPASSWORD' => (string) $conexion->getConfig('password')]);
        $respaldo->setTimeout(120);
        if ($respaldo->run() !== 0 || ! is_file($archivo) || filesize($archivo) === 0) {
            throw new RuntimeException('No se pudo respaldar la base. Comprueba la ruta de pg_dump y su conexión local.');
        }
        echo 'Respaldo privado: '.basename($archivo).PHP_EOL;
    }

    $resultado = app($seeder)->ejecutar($aplicar);
    echo json_encode(['modo' => $aplicar ? 'aplicado' : 'ensayo revertido'] + $resultado,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
} catch (Throwable $error) {
    report($error);
    fwrite(STDERR, 'La carga no se completó. Consulta el registro local de Laravel.'.PHP_EOL);
    exit(1);
}
