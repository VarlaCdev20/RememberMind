<?php

declare(strict_types=1);

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$opciones = getopt('', ['aplicar', 'base:']);
$db = DB::connection();
$base = $db->getDatabaseName();
if ($db->getDriverName() !== 'pgsql' || ! in_array($db->getConfig('host'), ['localhost', '127.0.0.1', '::1'], true)) {
    throw new RuntimeException('Este instalador sólo admite PostgreSQL local identificado explícitamente.');
}
$instalada = ! Schema::hasTable('variables_expertas') && Schema::hasColumn('nodos_semanticos', 'cod_variable_experta');
$leer = static function (bool $consolidado): array {
    $r = [];
    foreach (BloquesExpertos::DEFINICIONES as $entidad => $d) {
        $q = $consolidado ? BloquesExpertos::consulta($entidad) : DB::table($entidad);
        $r[$entidad] = $q->orderBy($d['id'])->get()->map(static function ($f): array {
            $fila = (array) $f;
            ksort($fila);

            return $fila;
        })->all();
    }

    return $r;
};
$huella = static fn (array $r): string => hash('sha256', json_encode($r, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
$antes = $leer($instalada);
$reporte = ['base' => $base, 'instalada' => $instalada, 'tablas_fisicas' => count(Schema::getTables()),
    'filas_bloques' => array_map('count', $antes), 'huella_bloques' => $huella($antes)];
if (! array_key_exists('aplicar', $opciones) || $instalada) {
    echo json_encode($reporte, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
    exit(0);
}
if (($opciones['base'] ?? null) !== $base || $base !== 'remembermind_dev' || $reporte['tablas_fisicas'] !== 120) {
    throw new RuntimeException('El destino y el inventario deben coincidir con la base institucional local autorizada de 104 tablas de negocio.');
}
foreach (array_keys(BloquesExpertos::DEFINICIONES) as $tabla) {
    if (! Schema::hasTable($tabla)) {
        throw new RuntimeException('El esquema de entrada no coincide con las cuatro relaciones aprobadas.');
    }
}
$bin = (getenv('ProgramFiles') ?: 'C:/Program Files').'/PostgreSQL/18/bin/';
$directorio = (getenv('LOCALAPPDATA') ?: throw new RuntimeException('Falta la ubicación privada de respaldos.')).'/RememberMind/backups';
if (! is_dir($directorio) && ! mkdir($directorio, 0700, true)) {
    throw new RuntimeException('No se pudo preparar el respaldo privado.');
}
$respaldo = $directorio.'/pre-bloques-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4)).'.dump';
$ejecutar = static function (array $comando) use ($db): void {
    $entorno = getenv();
    $entorno['PGPASSWORD'] = (string) $db->getConfig('password');
    $p = proc_open($comando, [0 => ['pipe', 'r'], 1 => ['file', PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null', 'w'], 2 => ['pipe', 'w']], $tuberias, null, $entorno);
    if (! is_resource($p)) {
        throw new RuntimeException('No se pudo iniciar la herramienta de respaldo.');
    }
    fclose($tuberias[0]);
    stream_get_contents($tuberias[2]);
    fclose($tuberias[2]);
    if (proc_close($p) !== 0) {
        throw new RuntimeException('El respaldo o su verificación fallaron; no se instala la consolidación.');
    }
};
$yaEstabaEnMantenimiento = $app->isDownForMaintenance();
try {
    if (! $yaEstabaEnMantenimiento && Artisan::call('down', ['--retry' => 60]) !== 0) {
        throw new RuntimeException('No se pudo proteger el mantenimiento.');
    }
    $ejecutar([$bin.'pg_dump.exe', '--format=custom', '--file='.$respaldo, '--host='.$db->getConfig('host'),
        '--port='.$db->getConfig('port'), '--username='.$db->getConfig('username'), '--dbname='.$base]);
    if (! is_file($respaldo) || filesize($respaldo) === 0) {
        throw new RuntimeException('El respaldo no contiene datos.');
    }
    $ejecutar([$bin.'pg_restore.exe', '--list', $respaldo]);
    // Sólo esta migración aprobada: nunca fresh, seed ni otras migraciones pendientes.
    $antes = $leer(false);
    $codigo = Artisan::call('migrate', ['--path' => 'database/migrations/2026_10_22_120000_consolidate_bloques_expertos_singulares.php', '--force' => true]);
    if ($codigo !== 0) {
        throw new RuntimeException('La migración no confirmó su ejecución. Revise el registro local.');
    }
    $despues = $leer(true);
    if ($antes !== $despues || count(Schema::getTables()) !== 116) {
        throw new RuntimeException('La comprobación posterior no confirmó preservación y conteo. El respaldo se conserva.');
    }
    $reporte = ['base' => $base, 'instalada' => true, 'tablas_negocio' => 100, 'tablas_fisicas' => 116,
        'filas_bloques' => array_map('count', $despues), 'huella_bloques' => $huella($despues),
        'respaldo_privado' => $respaldo, 'sha256_respaldo' => hash_file('sha256', $respaldo),
        'recuperacion_bloques_exacta' => true, 'fecha_utc' => gmdate(DATE_ATOM)];
    file_put_contents($respaldo.'.verificacion.json', json_encode($reporte, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    echo json_encode($reporte, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
} finally {
    if (! $yaEstabaEnMantenimiento) {
        Artisan::call('up');
    }
}
