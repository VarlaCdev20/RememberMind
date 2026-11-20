<?php

declare(strict_types=1);

/** Instalación explícita del alcance aprobado el04/11. No evalúa residentes ni despliega código. */
use Database\Seeders\PermisoEjecucionInstitucionalSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$ddl = 'database/propuestas/experto/2026_11_04_000001_conservar_resultado_institucional.php';
$db = DB::connection();
if (! in_array('--instalar-aprobado-2026-11-04', $argv, true)) { throw new RuntimeException('Indique explícitamente el alcance aprobado antes de instalar.'); }
if ($db->getDriverName() !== 'pgsql' || $db->getDatabaseName() !== 'remembermind_dev' || ! in_array($db->getConfig('host'), ['127.0.0.1', 'localhost'], true)) {
    throw new RuntimeException('Este instalador solo permite la base configurada y autorizada de esta estación.');
}
foreach (['output/resultado-institucional-20261104-sqlite-final.xml', 'output/resultado-institucional-20261104-postgres-final.xml'] as $reporte) {
    $xml = simplexml_load_file(base_path($reporte));
    if (! $xml) { throw new RuntimeException('Falta la evidencia de pruebas desechables.'); }
    $suites = $xml->xpath('//testsuite[@file]');
    if (! $suites || array_sum(array_map(fn ($s) => (int) $s['tests'], $suites)) < 15
        || array_sum(array_map(fn ($s) => (int) $s['failures'] + (int) $s['errors'], $suites)) !== 0) {
        throw new RuntimeException('Las pruebas del alcance no están verificadas.');
    }
}
$tablas = Schema::getTableListing(); sort($tablas);
if (count($tablas) !== (122 - (int) Schema::hasColumn('preadmisiones', 'cod_valoracion_enfermeria') - (int) Schema::hasTable('registros_consumo') - (int) Schema::hasColumn('registros_consumo', 'tipo_eliminacion') - (int) Schema::hasTable('registros_observaciones_cuidado') - (int) Schema::hasColumn('nodos_semanticos', 'cod_criterio_dominio_resultado') - (int) Schema::hasColumn('registros_observaciones_cuidado', 'horas_sueno') - (int) Schema::hasTable('mediciones_clinicas') - 2 * (int) Schema::hasTable('valoraciones_profesionales') - (int) Schema::hasTable('registros_estudio') - (int) Schema::hasTable('vinculos_evidencia_experta') - 3 * (int) Schema::hasTable('registros_clinicos') - (int) Schema::hasTable('asignaciones_jornada') - (int) Schema::hasTable('componentes_regla_experta') - (int) Schema::hasColumn('valoraciones_profesionales', 'cod_seguimiento_pedagogico') - (int) Schema::hasColumn('documentos', 'cod_documento_clinico') - (int) Schema::hasColumn('mediciones_clinicas', 'cod_valoracion_dolor') - (int) Schema::hasTable('registros_textuales_clinicos') - (int) Schema::hasTable('historial_laboral_personal') - (int) Schema::hasColumn('registros_textuales_clinicos', 'cod_pase') - 2 * (int) Schema::hasColumn('registros_observaciones_cuidado', 'cod_control_iadl') - (int) Schema::hasColumn('evaluaciones_reglas', 'cod_elemento_evaluacion') - (int) Schema::hasColumn('reglas_aplicadas_dcl_instantanea', 'cod_elemento_regla_dcl'))) { throw new RuntimeException('El inventario configurado no coincide con el inventario de negocio instalado más 16 tablas técnicas.'); }
$nombre = pathinfo($ddl, PATHINFO_FILENAME);
if (DB::table('migrations')->where('migration', $nombre)->exists()) {
    if (! Schema::hasColumn('evaluaciones_expertas', 'institucional_resultado')) { throw new RuntimeException('Registro de migración sin estructura; requiere diagnóstico.'); }
    (new PermisoEjecucionInstitucionalSeeder)->run();
    echo "La extensión autorizada ya está instalada.\n";
    exit(0);
}
if (Schema::hasColumn('evaluaciones_expertas', 'institucional_resultado')) { throw new RuntimeException('Estructura sin registro de extensión; no se sobrescribe.'); }
$hashDdl = hash_file('sha256', base_path($ddl));
$respaldo = storage_path('app/private/backups/resultado-institucional-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(4)).'.dump');
if (! is_dir(dirname($respaldo))) { mkdir(dirname($respaldo), 0700, true); }
$bin = 'C:/Program Files/PostgreSQL/18/bin/';
$dump = new Process([$bin.'pg_dump.exe', '-h', $db->getConfig('host'), '-p', (string) $db->getConfig('port'), '-U', $db->getConfig('username'),
    '-d', $db->getDatabaseName(), '--format=custom', '--no-owner', '--file='.$respaldo], base_path(), ['PGPASSWORD' => (string) $db->getConfig('password')], timeout: 180);
$dump->run();
if (! $dump->isSuccessful() || ! is_file($respaldo) || filesize($respaldo) === 0) { throw new RuntimeException('No se confirmó el respaldo; no se instala.'); }
$catalogo = new Process([$bin.'pg_restore.exe', '--list', $respaldo], base_path(), timeout: 30); $catalogo->run();
if (! $catalogo->isSuccessful()) { throw new RuntimeException('El respaldo no tiene un catálogo legible.'); }
if (! DB::scalar("SELECT pg_try_advisory_lock(hashtext('remembermind-resultado-institucional-20261104'))")) { throw new RuntimeException('Otra instalación está en curso.'); }
try {
    $verificado = DB::transaction(function () use ($ddl, $nombre, $hashDdl, $tablas): array {
        DB::statement("SET LOCAL lock_timeout = '10s'"); DB::statement("SET LOCAL statement_timeout = '90s'");
        DB::statement('LOCK TABLE evaluaciones_expertas, hechos_evaluacion_experta IN ACCESS EXCLUSIVE MODE');
        if (hash_file('sha256', base_path($ddl)) !== $hashDdl) { throw new RuntimeException('El DDL cambió durante la preparación.'); }
        $columnas = $hashes = $cantidades = [];
        foreach (['evaluaciones_expertas', 'hechos_evaluacion_experta'] as $tabla) {
            $columnas[$tabla] = Schema::getColumnListing($tabla);
            $seleccion = implode(',', array_map(fn ($c) => '"'.$c.'"', $columnas[$tabla]));
            $hashes[$tabla] = DB::scalar("SELECT md5(COALESCE(string_agg(md5(row_to_json(t)::text),'' ORDER BY md5(row_to_json(t)::text)),'')) FROM (SELECT {$seleccion} FROM {$tabla}) t");
            $cantidades[$tabla] = DB::table($tabla)->count();
        }
        (require base_path($ddl))->up();
        $nuevas = [];
        foreach ($columnas as $tabla => $cols) {
            $seleccion = implode(',', array_map(fn ($c) => '"'.$c.'"', $cols));
            $ahora = DB::scalar("SELECT md5(COALESCE(string_agg(md5(row_to_json(t)::text),'' ORDER BY md5(row_to_json(t)::text)),'')) FROM (SELECT {$seleccion} FROM {$tabla}) t");
            if ($ahora !== $hashes[$tabla] || $cantidades[$tabla] !== DB::table($tabla)->count()) { throw new RuntimeException('Cambió historia previa; se revierte.'); }
            $nuevas[$tabla] = array_values(array_diff(Schema::getColumnListing($tabla), $cols));
            if (DB::table($tabla)->where(function ($q) use ($nuevas, $tabla): void { foreach ($nuevas[$tabla] as $c) { $q->orWhereNotNull($c); } })->exists()) {
                throw new RuntimeException('No se permite rellenar historia desconocida.');
            }
        }
        if (count($nuevas['evaluaciones_expertas']) !== 16 || count($nuevas['hechos_evaluacion_experta']) !== 3) { throw new RuntimeException('Extensión fuera de las19 columnas aprobadas.'); }
        (new PermisoEjecucionInstitucionalSeeder)->run();
        DB::table('migrations')->insert(['migration' => $nombre, 'batch' => (int) DB::table('migrations')->max('batch') + 1]);
        $despues = Schema::getTableListing(); sort($despues);
        if ($despues !== $tablas) { throw new RuntimeException('El inventario debe permanecer sin tablas nuevas.'); }
        return ['tablas_fisicas' => count($despues), 'tablas_negocio' => count($despues) - 16, 'tablas_tecnicas' => 16, 'columnas_nuevas' => $nuevas,
            'filas_previas_conservadas' => $cantidades, 'historia_previa_identica' => true, 'campos_nuevos_previos_null' => true];
    });
    $informe = $verificado + ['fecha' => now()->toIso8601String(), 'ddl_sha256' => $hashDdl, 'instalado' => true,
        'respaldo_privado' => $respaldo, 'respaldo_sha256' => hash_file('sha256', $respaldo), 'respaldo_catalogo_verificado' => true,
        'permiso' => 'evaluaciones_expertas.ejecutar_institucional', 'roles' => ['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A'], 'replay_dto_completo' => false];
    file_put_contents(base_path('output/resultado-institucional-20261104-instalacion.json'), json_encode($informe, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Extensión instalada:19 columnas;106 tablas de negocio. Historia anterior comprobada; respaldo privado verificado.\n";
} finally {
    DB::statement("SELECT pg_advisory_unlock(hashtext('remembermind-resultado-institucional-20261104'))");
}
