<?php

// Instalación local expresamente autorizada; no es endpoint, seeder ni deploy automático.
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

$modo = $argv[1] ?? 'inspeccionar';
if (PHP_SAPI !== 'cli' || ! in_array($modo, ['inspeccionar', 'instalar'], true)) throw new RuntimeException('Modo no admitido.');
$db = DB::connection();
if (! app()->environment('local') || $db->getDriverName() !== 'pgsql' || $db->getDatabaseName() !== 'remembermind_dev'
    || ! in_array($db->getConfig('host'), ['127.0.0.1', 'localhost'], true)) {
    throw new RuntimeException('El destino no es la base local expresamente autorizada.');
}
$tecnicas = ['migrations', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs',
    'personal_access_tokens', 'activity_log', 'permissions', 'roles', 'model_has_permissions', 'model_has_roles', 'role_has_permissions', 'secuencias_codigos'];
$tablas = fn () => array_map(fn ($tabla) => (string) str($tabla)->afterLast('.'), Schema::getTableListing());
$ddl = ['database/propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php',
    'database/propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php'];
$huellas = array_combine($ddl, array_map(fn ($ruta) => hash_file('sha256', base_path($ruta)), $ddl));
$nuevas = ['solicitudes_documentales_personal', 'destinatarios_solicitud_documental', 'entregas_solicitud_documental',
    'movimientos_laborales_personal', 'versiones_vinculo_contacto', 'eventos_asignacion_personal'];
$antes = $tablas();
$instaladas = array_values(array_intersect($antes, $nuevas));
$resumen = ['fecha' => now()->toIso8601String(), 'entorno' => app()->environment(), 'base' => $db->getDatabaseName(),
    'fisicas' => count($antes), 'negocio' => count(array_diff($antes, $tecnicas)), 'tecnicas' => count(array_intersect($antes, $tecnicas)),
    'tablas_extension_presentes' => $instaladas, 'ddl_sha256' => $huellas,
    'capacidades' => ['documentacion' => app(\App\Modulos\Identidad\Aplicacion\Consultas\CapacidadDocumentalPersonal::class)->disponible(),
        'historia_vinculos' => Schema::hasTable('versiones_vinculo_contacto'),
        'historia_asignaciones' => Schema::hasTable('eventos_asignacion_personal')]];
if ($modo === 'inspeccionar') { echo json_encode($resumen, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR); exit; }
$resumen['capacidades_antes'] = $resumen['capacidades'];
unset($resumen['capacidades']);
if (($argv[2] ?? '') !== 'AUTORIZACION-PROPIETARIA-35-37') throw new RuntimeException('Falta alcance explícito de instalación.');
if (count($antes) !== 116 || count(array_diff($antes, $tecnicas)) !== 100 || $instaladas
    || Schema::hasColumn('usuarios', 'fecha_hora_alta') || Schema::hasColumn('documentos', 'cod_usuario_carga')) {
    throw new RuntimeException('Inventario distinto o transición parcial: revisar antes de instalar.');
}
$manifiesto = json_decode(file_get_contents(base_path('output/auditoria-integral-20261025/gerencia-propuesta-instalacion.json')), true, flags: JSON_THROW_ON_ERROR);
foreach ($manifiesto['ddl'] as $archivo) {
    if (($huellas[$archivo['archivo']] ?? null) !== $archivo['sha256']) throw new RuntimeException('El DDL difiere de la propuesta revisada.');
}
$bin = 'C:/Program Files/PostgreSQL/18/bin/';
if (! is_file($bin.'pg_dump.exe') || ! is_file($bin.'pg_restore.exe')) throw new RuntimeException('Falta el runtime de respaldo verificado.');
$rutaRespaldo = storage_path('app/private/backups/gerencia-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(4)).'.dump');
if (! is_dir(dirname($rutaRespaldo))) mkdir(dirname($rutaRespaldo), 0700, true);
$proceso = new Process([$bin.'pg_dump.exe', '-h', $db->getConfig('host'), '-p', (string) $db->getConfig('port'),
    '-U', $db->getConfig('username'), '-d', $db->getDatabaseName(), '--format=custom', '--no-owner', '--file='.$rutaRespaldo],
    base_path(), ['PGPASSWORD' => (string) $db->getConfig('password')], timeout: 120);
$proceso->run();
if (! $proceso->isSuccessful() || ! is_file($rutaRespaldo) || filesize($rutaRespaldo) === 0) throw new RuntimeException('No se confirmó un respaldo; no se instaló el esquema.');
$catalogo = new Process([$bin.'pg_restore.exe', '--list', $rutaRespaldo], base_path(), timeout: 30);
$catalogo->run();
if (! $catalogo->isSuccessful()) throw new RuntimeException('El catálogo del respaldo no se pudo leer; no se instaló el esquema.');
$resumen['respaldo'] = ['ruta_privada' => str_replace(base_path().DIRECTORY_SEPARATOR, '', $rutaRespaldo),
    'bytes' => filesize($rutaRespaldo), 'sha256' => hash_file('sha256', $rutaRespaldo), 'catalogo_verificado' => true];
$rutaInforme = base_path('output/auditoria-integral-20261025/gerencia-instalacion-20261027.json');
file_put_contents($rutaInforme, json_encode($resumen + ['estado' => 'RESPALDO_VERIFICADO_SIN_INSTALAR'], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

if (! DB::scalar("select pg_try_advisory_lock(hashtext('remembermind-gerencia-35-37'))")) throw new RuntimeException('Existe otra instalación en curso.');
try {
    $verificacion = DB::transaction(function () use ($ddl, $tablas, $tecnicas, $nuevas) {
        DB::statement("SET LOCAL lock_timeout = '10s'");
        DB::statement("SET LOCAL statement_timeout = '90s'");
        // Impide escrituras en las dos relaciones expandidas durante la comparación.
        DB::statement('LOCK TABLE usuarios, documentos IN ACCESS EXCLUSIVE MODE');
        $cuentas = DB::table('usuarios')->count();
        $documentos = DB::table('documentos')->count();
        $columnasUsuario = Schema::getColumnListing('usuarios');
        $columnasDocumento = Schema::getColumnListing('documentos');
        $huellaFilas = function ($tabla, $columnas, $clave) {
            $seleccion = implode(',', array_map(fn ($columna) => '"'.$columna.'"', $columnas));
            return DB::scalar("SELECT md5(COALESCE(string_agg(md5(row_to_json(t)::text),'' ORDER BY t.\"{$clave}\"),'')) FROM (SELECT {$seleccion} FROM \"{$tabla}\") t");
        };
        $huellasAntes = [$huellaFilas('usuarios', $columnasUsuario, 'cod_usuario'), $huellaFilas('documentos', $columnasDocumento, 'cod_documento')];
        foreach ($ddl as $ruta) {
            $migracion = pathinfo($ruta, PATHINFO_FILENAME);
            if (DB::table('migrations')->where('migration', $migracion)->exists()) throw new RuntimeException('La transición ya figura instalada.');
            (require base_path($ruta))->up();
            DB::table('migrations')->insert(['migration' => $migracion, 'batch' => ((int) DB::table('migrations')->max('batch')) + 1]);
        }
        $despues = $tablas();
        if (count(array_diff($despues, $tecnicas)) !== 106 || count(array_intersect($despues, $tecnicas)) !== 16) throw new RuntimeException('Inventario final inesperado; revertir transacción.');
        if ($cuentas !== DB::table('usuarios')->count() || $documentos !== DB::table('documentos')->count()
            || $huellasAntes !== [$huellaFilas('usuarios', $columnasUsuario, 'cod_usuario'), $huellaFilas('documentos', $columnasDocumento, 'cod_documento')]) {
            throw new RuntimeException('El contenido previo cambió; revertir transacción.');
        }
        foreach ($nuevas as $tabla) if (DB::table($tabla)->exists()) throw new RuntimeException('La instalación no debe inventar historia.');
        if (! app(\App\Modulos\Identidad\Aplicacion\Consultas\CapacidadDocumentalPersonal::class)->disponible()) {
            throw new RuntimeException('La capacidad documental no reconoce el esquema instalado.');
        }
        if (DB::table('usuarios')->whereNotNull('fecha_hora_alta')->orWhereNotNull('cambio_password_pendiente')->exists()
            || DB::table('documentos')->whereNotNull('cod_usuario_carga')->orWhereNotNull('fecha_hora_carga')->orWhereNotNull('fecha_emision')
                ->orWhereNotNull('cod_usuario_anulacion')->orWhereNotNull('fecha_hora_anulacion')->orWhereNotNull('motivo_anulacion')->exists()) {
            throw new RuntimeException('La instalación no debe rellenar procedencia desconocida.');
        }
        return ['fisicas' => count($despues), 'negocio' => 106, 'tecnicas' => 16, 'historia_nueva_vacia' => true,
            'columnas_historicas_nuevas_null' => true, 'contenido_previo_usuarios_documentos_identico' => true,
            'capacidad_documental_disponible' => true, 'ddl_registrados_en_migrations' => count($ddl)];
    });
    $resumen['estado'] = 'INSTALADO_Y_VERIFICADO';
    $resumen['despues'] = $verificacion;
    file_put_contents($rutaInforme, json_encode($resumen, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    echo json_encode(['estado' => $resumen['estado'], 'despues' => $verificacion, 'evidencia' => $rutaInforme], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
} finally {
    DB::select("select pg_advisory_unlock(hashtext('remembermind-gerencia-35-37'))");
}
