<?php

/** Comando optativo: php database/seeders/LaPaz/ejecutar.php --base=RememberMind */
use Database\Seeders\LaPaz\ConservacionDatos;
use Database\Seeders\LaPaz\HistoriaResidencialSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__, 3).'/vendor/autoload.php';
$app = require dirname(__DIR__, 3).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$opciones = getopt('', ['base:', 'verificar']);
$seeder = app(HistoriaResidencialSeeder::class);
$confirmada = false;
$seeder->avance = fn ($numero) => fwrite(STDOUT, "Historia $numero/48 incorporada.\n");

try {
    $base = DB::connection()->getDatabaseName();
    if (! app()->environment('local') || DB::getDriverName() !== 'pgsql'
        || ! isset($opciones['base']) || ! is_string($opciones['base']) || $opciones['base'] !== $base
        || ! in_array(config('database.connections.'.config('database.default').'.host'), ['127.0.0.1', 'localhost', '::1'], true)) {
        throw new RuntimeException('Indique --base=NOMBRE_EXACTO de la conexión PostgreSQL local. No se modificó ningún dato.');
    }
    if (DB::selectOne('select current_database() as nombre')->nombre !== $base) {
        throw new RuntimeException('La identidad real de PostgreSQL no coincide con la configuración.');
    }
    config(['seeders.lapaz.base_autorizada' => $base]);
    $existe = DB::table('preadmisiones')->whereIn('cod_preadmision', HistoriaResidencialSeeder::solicitudes())->exists();
    if (array_key_exists('verificar', $opciones) || $existe) {
        $seeder->assertIntegrity();
        fwrite(STDOUT, "Cohorte existente verificada. No se insertaron ni sobrescribieron filas.\n");
        exit(0);
    }

    $disco = Storage::disk('local');
    $carpeta = 'la-paz-2025-2026/respaldo-'.date('Ymd-His').'-'.bin2hex(random_bytes(4));
    if (! $disco->makeDirectory($carpeta)) {
        throw new RuntimeException('No se pudo crear el directorio privado del respaldo.');
    }
    $archivo = $disco->path($carpeta.'/anterior.dump');
    $conexion = config('database.connections.'.config('database.default'));
    $binario = (getenv('ProgramFiles') ?: 'C:/Program Files').'/PostgreSQL/18/bin/pg_dump.exe';
    if (! is_file($binario)) {
        throw new RuntimeException('Se necesita PostgreSQL 18/pg_dump para respaldar antes de cargar.');
    }
    $entorno = getenv();
    $entorno['PGPASSWORD'] = (string) ($conexion['password'] ?? '');
    $proceso = proc_open([$binario, '--host='.$conexion['host'], '--port='.$conexion['port'],
        '--username='.$conexion['username'], '--dbname='.$base, '--format=custom', '--no-password', '--file='.$archivo],
        [0 => ['pipe', 'r'], 1 => ['file', $disco->path($carpeta.'/respaldo-salida.log'), 'w'],
            2 => ['file', $disco->path($carpeta.'/respaldo-error.log'), 'w']], $canales, null, $entorno);
    unset($entorno['PGPASSWORD']);
    if (! is_resource($proceso)) {
        throw new RuntimeException('No se pudo iniciar el respaldo.');
    }
    fclose($canales[0]);
    if (proc_close($proceso) !== 0 || ! is_file($archivo) || filesize($archivo) < 100) {
        throw new RuntimeException('El respaldo no terminó correctamente; revise el registro privado. No se ejecutó el seeder.');
    }
    fwrite(STDOUT, 'Respaldo privado creado. Base de destino: '.$base.".\n");
    $conservacion = new ConservacionDatos;
    $antes = $conservacion->capturar();
    $resultado = DB::transaction(function () use ($seeder, $conservacion, $antes): array {
        DB::select('select pg_advisory_xact_lock(?)', [2026102048]);
        // La segunda instancia que esperaba el bloqueo vuelve a comprobar antes de escribir archivos.
        if (DB::table('preadmisiones')->whereIn('cod_preadmision', HistoriaResidencialSeeder::solicitudes())->exists()) {
            throw new RuntimeException('Otra ejecución completó la cohorte. Vuelva a ejecutar para verificarla.');
        }
        $seeder->run();
        $preservadas = $conservacion->comprobar();
        $creadas = [];
        $permitidas = HistoriaResidencialSeeder::tablasPermitidas();
        foreach ($antes as $tabla => $n) {
            $diferencia = DB::table($tabla)->count() - $n;
            if ($diferencia !== 0 && ! in_array($tabla, $permitidas, true)) {
                throw new RuntimeException('Cambio no permitido en '.$tabla.'. La carga se revierte.');
            }
            if ($diferencia > 0) {
                $creadas[$tabla] = $diferencia;
            }
        }
        if (array_sum($creadas) < 10000) {
            throw new RuntimeException('No se alcanzaron los 10.000 registros nuevos.');
        }

        return ['version' => HistoriaResidencialSeeder::VERSION, 'inicio' => '2025-01-01',
            'corte' => HistoriaResidencialSeeder::CUTOFF, 'zona_horaria' => 'America/La_Paz',
            'filas_preexistentes_conservadas' => $preservadas, 'filas_nuevas' => array_sum($creadas),
            'por_tabla' => $creadas, 'salidas_experto_creadas' => 0, 'esquema_modificado' => false];
    });
    $confirmada = true;
    if (! $disco->put('la-paz-2025-2026/resultado-carga.json', json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR))) {
        throw new RuntimeException('La base se confirmó pero falló el informe privado. Ejecute --verificar; no vuelva a insertar la cohorte.');
    }
    fwrite(STDOUT, json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL);
    fwrite(STDOUT, 'Credenciales y manifiesto: '.$disco->path('la-paz-2025-2026').PHP_EOL);
} catch (Throwable $error) {
    // Detalle técnico solo en el registro privado de Laravel.
    report($error);
    if (! $confirmada) {
        $seeder->retirarArchivosRevertidos();
    }
    $mensaje = $error instanceof RuntimeException && ! $error instanceof QueryException
        ? $error->getMessage() : 'Falló la carga. Consulte storage/logs/laravel.log; no se confirmó una carga parcial.';
    fwrite(STDERR, $mensaje.PHP_EOL);
    exit(1);
}
