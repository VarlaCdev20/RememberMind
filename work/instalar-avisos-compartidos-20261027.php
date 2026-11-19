<?php

// Instalación acotada de los seis campos y activación local adoptadas por la propietaria.
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Modulos\Institucion\Aplicacion\Consultas\ConsultarAvisosPlanificacion;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

$db = DB::connection();
if (PHP_SAPI !== 'cli' || ! app()->environment('local') || $db->getDriverName() !== 'pgsql'
    || $db->getDatabaseName() !== 'remembermind_dev' || ! in_array($db->getConfig('host'), ['127.0.0.1', 'localhost'], true)) {
    throw new RuntimeException('Destino distinto de la base local autorizada.');
}
$modo = $argv[1] ?? 'inspeccionar';
if (! in_array($modo, ['inspeccionar', 'instalar'], true)) throw new RuntimeException('Modo no admitido.');
$ddl = 'database/propuestas/gerencia/2026_10_27_000003_campos_aviso_evento_asignacion.php';
$hashDdl = hash_file('sha256', base_path($ddl));
$columnas = ConsultarAvisosPlanificacion::CAMPOS;
$presentes = array_values(array_intersect($columnas, Schema::getColumnListing('eventos_asignacion_personal')));
$fisicas = Schema::getTableListing();
$tecnicas = ['migrations', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs',
    'personal_access_tokens', 'activity_log', 'permissions', 'roles', 'model_has_permissions', 'model_has_roles', 'role_has_permissions', 'secuencias_codigos'];
$tablas = array_map(fn ($tabla) => (string) str($tabla)->afterLast('.'), $fisicas);
$resumen = ['fecha' => now()->toIso8601String(), 'base' => $db->getDatabaseName(), 'negocio' => count(array_diff($tablas, $tecnicas)),
    'tecnicas' => count(array_intersect($tablas, $tecnicas)), 'columnas_presentes' => $presentes, 'ddl_sha256' => $hashDdl,
    'capacidad_activa' => app(ConsultarAvisosPlanificacion::class)->disponible()];
if ($modo === 'inspeccionar') {
    $resumen['conexion_cola_configurada'] = config('queue.default');
    if (count($presentes) === 6) {
        $resumen['restricciones'] = DB::select("SELECT conname,contype,convalidated FROM pg_constraint WHERE conrelid='eventos_asignacion_personal'::regclass AND (conname LIKE 'eap_aviso_%' OR conname LIKE '%cod_usuario_afectado_aviso%') ORDER BY conname");
        $resumen['indices_aviso'] = DB::select("SELECT indexname FROM pg_indexes WHERE tablename='eventos_asignacion_personal' AND indexname LIKE 'eap_aviso_%' ORDER BY indexname");
        $resumen['trigger_evento'] = DB::select("SELECT tgname,tgenabled,pg_get_triggerdef(oid) AS definicion FROM pg_trigger WHERE tgrelid='eventos_asignacion_personal'::regclass AND NOT tgisinternal");
        $resumen['migracion_registrada'] = DB::table('migrations')->where('migration', pathinfo($ddl, PATHINFO_FILENAME))->count() === 1;
    }
    echo json_encode($resumen, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR); exit;
}
if (($argv[2] ?? '') !== 'CAMPOS-Y-AVISO-COMPARTIDO-APROBADOS' || $resumen['negocio'] !== 106 || $resumen['tecnicas'] !== 16 || $presentes) {
    throw new RuntimeException('Alcance o inventario distintos; no instalar.');
}
$manifiesto = json_decode(file_get_contents(base_path('output/auditoria-integral-20261025/gerencia-campos-aviso-manifiesto.json')), true, flags: JSON_THROW_ON_ERROR);
if ($manifiesto['ddl_sha256'] !== $hashDdl || ! $manifiesto['campos_y_alcance_adoptados']) throw new RuntimeException('El DDL difiere del diseño revisado.');
$rutaEnv = base_path('.env');
if (! is_writable($rutaEnv)) throw new RuntimeException('No se puede activar la configuración local.');
$envAntes = file_get_contents($rutaEnv);
if (preg_match_all('/^AVISOS_PLANIFICACION_HABILITADOS=.*$/m', $envAntes) > 1) throw new RuntimeException('Configuración duplicada; revisar sin sobrescribir.');
$bin = 'C:/Program Files/PostgreSQL/18/bin/';
$respaldo = storage_path('app/private/backups/avisos-campos-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(4)).'.dump');
if (! is_dir(dirname($respaldo))) mkdir(dirname($respaldo), 0700, true);
$dump = new Process([$bin.'pg_dump.exe', '-h', $db->getConfig('host'), '-p', (string) $db->getConfig('port'), '-U', $db->getConfig('username'),
    '-d', $db->getDatabaseName(), '--format=custom', '--no-owner', '--file='.$respaldo], base_path(), ['PGPASSWORD' => (string) $db->getConfig('password')], timeout: 120);
$dump->run();
if (! $dump->isSuccessful() || ! is_file($respaldo) || ! filesize($respaldo)) throw new RuntimeException('No se confirmó el respaldo; sin instalación.');
$catalogo = new Process([$bin.'pg_restore.exe', '--list', $respaldo], base_path(), timeout: 30); $catalogo->run();
if (! $catalogo->isSuccessful()) throw new RuntimeException('No se pudo leer el catálogo del respaldo.');
$resumen['respaldo'] = ['ruta_privada' => str_replace(base_path().DIRECTORY_SEPARATOR, '', $respaldo), 'bytes' => filesize($respaldo),
    'sha256' => hash_file('sha256', $respaldo), 'catalogo_verificado' => true];
$rutaInforme = base_path('output/auditoria-integral-20261025/gerencia-campos-aviso-instalacion.json');
if (! DB::scalar("select pg_try_advisory_lock(hashtext('remembermind-campos-aviso'))")) throw new RuntimeException('Otra instalación está en curso.');
try {
    $verificado = DB::transaction(function () use ($ddl, $columnas, $tablas) {
        DB::statement("SET LOCAL lock_timeout='10s'"); DB::statement("SET LOCAL statement_timeout='90s'");
        DB::statement('LOCK TABLE eventos_asignacion_personal IN ACCESS EXCLUSIVE MODE');
        $originales = Schema::getColumnListing('eventos_asignacion_personal');
        $seleccion = implode(',', array_map(fn ($c) => '"'.$c.'"', $originales));
        $huella = fn () => DB::scalar("SELECT md5(COALESCE(string_agg(md5(row_to_json(t)::text),'' ORDER BY t.cod_evento_asignacion),'')) FROM (SELECT {$seleccion} FROM eventos_asignacion_personal) t");
        $antes = $huella(); $cantidad = DB::table('eventos_asignacion_personal')->count();
        $funcionCompartida = DB::scalar("SELECT md5(pg_get_functiondef('ger_historia_inmutable()'::regprocedure))");
        $triggerVinculos = DB::scalar("SELECT pg_get_triggerdef(oid) FROM pg_trigger WHERE tgrelid='versiones_vinculo_contacto'::regclass AND tgname='ger_historia_inmutable'");
        (require base_path($ddl))->up();
        DB::table('migrations')->insert(['migration' => pathinfo($ddl, PATHINFO_FILENAME), 'batch' => ((int) DB::table('migrations')->max('batch')) + 1]);
        if ($huella() !== $antes || DB::table('eventos_asignacion_personal')->count() !== $cantidad) throw new RuntimeException('Cambió el acto previo; revertir.');
        if (DB::table('eventos_asignacion_personal')->where(function ($q) use ($columnas) { foreach ($columnas as $c) $q->orWhereNotNull($c); })->exists()) throw new RuntimeException('No rellenar seguimiento desconocido.');
        $despues = array_map(fn ($t) => (string) str($t)->afterLast('.'), Schema::getTableListing());
        if ($tablas !== $despues) throw new RuntimeException('La ampliación no debe cambiar tablas.');
        if ($funcionCompartida !== DB::scalar("SELECT md5(pg_get_functiondef('ger_historia_inmutable()'::regprocedure))")
            || $triggerVinculos !== DB::scalar("SELECT pg_get_triggerdef(oid) FROM pg_trigger WHERE tgrelid='versiones_vinculo_contacto'::regclass AND tgname='ger_historia_inmutable'")) throw new RuntimeException('Cambió la protección de vínculos.');
        return ['negocio' => 106, 'tecnicas' => 16, 'fisicas' => 122, 'seis_campos_presentes' => Schema::hasColumns('eventos_asignacion_personal', $columnas),
            'actos_originales_identicos' => true, 'seguimiento_previo_null' => true, 'proteccion_vinculos_identica' => true];
    });
    $resumen['estado'] = 'CAMPOS_INSTALADOS_PENDIENTE_ACTIVACION'; $resumen['verificacion'] = $verificado;
    file_put_contents($rutaInforme, json_encode($resumen, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    if (file_get_contents($rutaEnv) !== $envAntes) throw new RuntimeException('La configuración cambió concurrentemente. No se sobrescribe; campos instalados, activación pendiente.');
    $linea = 'AVISOS_PLANIFICACION_HABILITADOS=true';
    $envNuevo = preg_match('/^AVISOS_PLANIFICACION_HABILITADOS=.*$/m', $envAntes)
        ? preg_replace('/^AVISOS_PLANIFICACION_HABILITADOS=.*$/m', $linea, $envAntes)
        : rtrim($envAntes).PHP_EOL.$linea.PHP_EOL;
    if (file_put_contents($rutaEnv, $envNuevo, LOCK_EX) === false) throw new RuntimeException('Campos instalados, activación pendiente de configuración.');
    Artisan::call('config:clear'); config(['remembermind.avisos_planificacion_habilitados' => true]);
    if (! app(ConsultarAvisosPlanificacion::class)->disponible()) throw new RuntimeException('Activación no reconocida.');
    $resumen['estado'] = 'INSTALADO_Y_HABILITADO_LOCAL';
    $resumen['capacidad_antes'] = $resumen['capacidad_activa'];
    $resumen['capacidad_activa'] = true;
    $resumen['activacion'] = ['flag_local' => true, 'permisos_nuevos' => false, 'tablas_nuevas' => false];
    file_put_contents($rutaInforme, json_encode($resumen, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    echo json_encode(['estado' => $resumen['estado'], 'verificacion' => $verificado, 'evidencia' => $rutaInforme], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
} finally { DB::select("select pg_advisory_unlock(hashtext('remembermind-campos-aviso'))"); }
