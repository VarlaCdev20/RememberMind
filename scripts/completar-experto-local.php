<?php

declare(strict_types=1);

use App\Models\Residente;
use App\Models\VersionModeloExperto;
use Database\Seeders\PermisosConsultaFuentesExpertasSeeder;
use Database\Seeders\PermisosLecturaIadlPetersenSeeder;
use Database\Seeders\PermisosPetersenOriginalSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
set_exception_handler(static function (Throwable $error): never {
    report($error);
    fwrite(STDERR, "Instalación no confirmada. Consulte el diagnóstico privado de Laravel antes de repetir.\n");
    exit(1);
});
$opciones = getopt('', ['aplicar', 'base:']);
$db = DB::connection();
$base = $db->getDatabaseName();
if (! $app->environment('local') || $db->getDriverName() !== 'pgsql'
    || ! in_array($db->getConfig('host'), ['127.0.0.1', 'localhost', '::1'], true)) {
    throw new RuntimeException('La instalación admite únicamente PostgreSQL local.');
}
$aprobadas = ['2026_10_20_180000_consolidate_cuatro_tablas_expertas', '2026_10_22_120000_consolidate_bloques_expertos_singulares'];
$pendientes = array_values(array_filter($aprobadas, fn ($m) => ! DB::table('migrations')->where('migration', $m)->exists()));
$plan = ['base' => $base, 'modo' => 'SOLO_LECTURA', 'residentes_antes' => Residente::count(),
    'casos_coh30' => Residente::query()->where('numero_documento', 'like', 'COH30-%')->count(),
    'versiones_antes' => VersionModeloExperto::count(), 'migraciones_aprobadas_pendientes' => $pendientes,
    'alcance' => '30 casos sintéticos, contratos y permisos ya aprobados; sin firmas profesionales ni resincronización de roles'];
if (! isset($opciones['aplicar'])) {
    echo json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
    exit(0);
}
if (($opciones['base'] ?? '') !== $base || ! in_array($base, ['RememberMind', 'remembermind_dev'], true) || $app->configurationIsCached()) {
    throw new RuntimeException('Confirme --base con la base local autorizada; no se modifica una configuración cacheada.');
}
if (! in_array($plan['casos_coh30'], [0, 30], true)) {
    throw new RuntimeException('Existe una cohorte parcial. No se sobrescribe ni se elimina.');
}
$entorno = ['PGPASSWORD' => (string) $db->getConfig('password')];
$bin = (getenv('ProgramFiles') ?: 'C:/Program Files').'/PostgreSQL/18/bin/';
foreach (['pg_dump.exe', 'pg_restore.exe'] as $nombre) {
    if (! is_file($bin.$nombre)) {
        throw new RuntimeException('No se encontró la herramienta PostgreSQL de respaldo.');
    }
}
$privado = storage_path('app/private/auditorias-locales/completar-experto');
if (! is_dir($privado) && ! mkdir($privado, 0700, true) && ! is_dir($privado)) {
    throw new RuntimeException('No se pudo preparar la ubicación privada.');
}
$respaldo = $privado.'/antes-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4)).'.dump';
$bloqueo = DB::selectOne("select pg_try_advisory_lock(hashtext('remembermind:completar-experto')) as obtenido");
if (! filter_var($bloqueo->obtenido, FILTER_VALIDATE_BOOL)) {
    throw new RuntimeException('Otra instalación está en curso.');
}
$mantenimiento = $app->isDownForMaintenance();
try {
    if (! $mantenimiento && Artisan::call('down', ['--retry' => 30]) !== 0) {
        throw new RuntimeException('No se pudo proteger la instalación.');
    }
    $dump = new Process([$bin.'pg_dump.exe', '--host='.$db->getConfig('host'), '--port='.$db->getConfig('port'),
        '--username='.$db->getConfig('username'), '--dbname='.$base, '--format=custom', '--file='.$respaldo], dirname(__DIR__), $entorno);
    $dump->setTimeout(180);
    $dump->run();
    if (! $dump->isSuccessful() || ! is_file($respaldo) || filesize($respaldo) === 0) {
        throw new RuntimeException('No se confirmó el respaldo; no se aplicaron cambios.');
    }
    $validar = new Process([$bin.'pg_restore.exe', '--list', $respaldo]);
    $validar->run();
    if (! $validar->isSuccessful()) {
        throw new RuntimeException('El catálogo del respaldo no es legible; no se aplicaron cambios.');
    }
    foreach ($pendientes as $migracion) {
        if (Artisan::call('migrate', ['--path' => 'database/migrations/'.$migracion.'.php', '--force' => true]) !== 0) {
            throw new RuntimeException('Una migración aprobada no terminó. Se conserva el respaldo; no se inicia la carga.');
        }
    }
    if (Schema::hasTable('variables_expertas') || ! Schema::hasColumn('nodos_semanticos', 'cod_variable_experta')) {
        throw new RuntimeException('La estructura instalada no coincide con las consolidaciones aprobadas.');
    }
    // Instaladores existentes: restauran únicamente el contrato aprobado, sin ejecutar RolesAndPermissionsSeeder.
    $instaladores = [PermisosPetersenOriginalSeeder::class,
        PermisosLecturaIadlPetersenSeeder::class,
        PermisosConsultaFuentesExpertasSeeder::class];
    DB::transaction(static function () use ($instaladores): void {
        foreach ($instaladores as $instalador) {
            app($instalador)->run();
        }
    });
    $variables = ['SISTEMA_EXPERTO_SIMULACION_LOCAL' => 'true', 'SISTEMA_EXPERTO_SIMULACION_BASE' => $base];
    $comando = [PHP_BINARY, base_path('scripts/cargar-cohorte-local.php'), $base,
        $plan['casos_coh30'] === 0 ? '--instalar-conocimiento' : '--verificar'];
    $cargar = new Process($comando, base_path(), $variables);
    $cargar->setTimeout(600);
    $cargar->run();
    if (! $cargar->isSuccessful()) {
        // No publicar salida que pueda contener SQL o datos clínicos de una excepción.
        file_put_contents($privado.'/fallo-'.gmdate('Ymd-His').'.log', $cargar->getErrorOutput().$cargar->getOutput());
        throw new RuntimeException('La carga o verificación no se confirmó. Consulte el diagnóstico privado; no repita sin revisar.');
    }
    try {
        $informeCarga = json_decode($cargar->getOutput(), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $error) {
        file_put_contents($privado.'/fallo-'.gmdate('Ymd-His').'.log', $cargar->getErrorOutput().$cargar->getOutput());
        throw new RuntimeException('El cargador no devolvió una confirmación válida. Revise el diagnóstico privado antes de repetir.', previous: $error);
    }
    $verificacion = json_decode(file_get_contents(storage_path('app/private/auditorias-locales/cohorte30/verificacion.json')), true, 512, JSON_THROW_ON_ERROR);
    if (($informeCarga['cohorte_disponible'] ?? 0) !== 30 || ($informeCarga['discrepancias'] ?? null) !== 0
        || count($verificacion['casos'] ?? []) !== 30 || ! ($verificacion['comparacion']['satisfactorio'] ?? false)) {
        throw new RuntimeException('No se confirmaron los 30 resultados; no se cambia la configuración web.');
    }
    // Sólo estas dos claves locales; preserva el resto de .env, sin imprimir secretos.
    $rutaEnv = base_path('.env');
    $contenidoEnv = file_get_contents($rutaEnv);
    if ($contenidoEnv === false) {
        throw new RuntimeException('La carga terminó; no se pudo leer la configuración web.');
    }
    foreach ($variables as $clave => $valor) {
        $patron = '/^'.preg_quote($clave, '/').'=.*$/m';
        $contenidoEnv = preg_match($patron, $contenidoEnv)
            ? preg_replace($patron, $clave.'='.$valor, $contenidoEnv)
            : rtrim($contenidoEnv).PHP_EOL.$clave.'='.$valor.PHP_EOL;
    }
    if (file_put_contents($rutaEnv, $contenidoEnv, LOCK_EX) === false) {
        throw new RuntimeException('La carga terminó; no se pudo guardar la configuración web.');
    }
    $reporte = $plan + ['respaldo_sha256' => hash_file('sha256', $respaldo)];
    $reporte['modo'] = 'APLICADO_Y_VERIFICADO';
    $reporte['residentes_despues'] = Residente::count();
    $reporte['versiones_despues'] = VersionModeloExperto::count();
    $reporte['instaladores_permisos_aprobados'] = $instaladores;
    $reporte['cohorte'] = $informeCarga;
    if (file_put_contents($respaldo.'.json', json_encode($reporte, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)) === false) {
        throw new RuntimeException('La instalación terminó; no se confirmó su informe privado. No repita la carga.');
    }
    echo json_encode($reporte, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
} finally {
    DB::select("select pg_advisory_unlock(hashtext('remembermind:completar-experto'))");
    if (! $mantenimiento) {
        Artisan::call('up');
    }
}
