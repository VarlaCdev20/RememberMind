<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! in_array('--instalar-fotografia-area-aprobada', $argv, true)) {
    throw new RuntimeException('Falta el alcance aprobado de fotografía del área.');
}
$db = DB::connection();
if ($db->getDriverName() !== 'pgsql' || $db->getDatabaseName() !== 'remembermind_dev'
    || ! in_array($db->getConfig('host'), ['127.0.0.1', 'localhost'], true)) {
    throw new RuntimeException('Destino distinto de la base local autorizada.');
}
foreach (['output/areas-fotografia-sqlite.xml', 'output/areas-fotografia-postgres.xml'] as $ruta) {
    $xml = simplexml_load_file(base_path($ruta));
    $casos = $xml ? $xml->xpath('//testcase[@class="Tests\\Feature\\AreasFotografiaTest"]') : [];
    if (count($casos) < 13 || $xml->xpath('//failure|//error')) {
        throw new RuntimeException('Falta evidencia satisfactoria del alcance en SQLite/PostgreSQL.');
    }
}
$ddl = 'database/migrations/2026_11_08_000001_add_fotografia_principal_to_areas_table.php';
$nombre = pathinfo($ddl, PATHINFO_FILENAME);
if (DB::table('migrations')->where('migration', $nombre)->exists()) {
    if (! Schema::hasColumn('areas', 'cod_fotografia')) {
        throw new RuntimeException('Registro y esquema no coinciden.');
    }
    echo "La fotografía principal del área ya está instalada.\n";
    exit(0);
}
if (Schema::getColumnListing('areas') !== ['cod_area', 'nombre', 'descripcion', 'estado']) {
    throw new RuntimeException('Estructura de Áreas distinta del corte aprobado; no se sobrescribe.');
}
$hashDdl = hash_file('sha256', base_path($ddl));
$respaldo = storage_path('app/private/backups/areas-fotografia-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(4)).'.dump');
if (! is_dir(dirname($respaldo))) {
    mkdir(dirname($respaldo), 0700, true);
}
$bin = 'C:/Program Files/PostgreSQL/18/bin/';
$dump = new Process([$bin.'pg_dump.exe', '-h', $db->getConfig('host'), '-p', (string) $db->getConfig('port'), '-U', $db->getConfig('username'),
    '-d', $db->getDatabaseName(), '--format=custom', '--no-owner', '--file='.$respaldo], base_path(), ['PGPASSWORD' => (string) $db->getConfig('password')], timeout: 180);
$dump->run();
if (! $dump->isSuccessful() || ! is_file($respaldo) || ! filesize($respaldo)) {
    throw new RuntimeException('No se confirmó el respaldo; no se instala.');
}
$catalogo = new Process([$bin.'pg_restore.exe', '--list', $respaldo], base_path(), timeout: 30);
$catalogo->run();
if (! $catalogo->isSuccessful()) {
    throw new RuntimeException('Catálogo de respaldo ilegible.');
}
if (! DB::scalar("SELECT pg_try_advisory_lock(hashtext('remembermind-area-fotografia-20261108'))")) {
    throw new RuntimeException('Otra instalación está en curso.');
}
try {
    $evidencia = DB::transaction(function () use ($ddl, $nombre, $hashDdl) {
        DB::statement("SET LOCAL lock_timeout = '10s'");
        DB::statement("SET LOCAL statement_timeout = '90s'");
        DB::statement('LOCK TABLE areas IN ACCESS EXCLUSIVE MODE');
        $tablas = Schema::getTableListing();
        sort($tablas);
        $consulta = "SELECT md5(COALESCE(string_agg(md5(row_to_json(t)::text),'' ORDER BY t.cod_area),'')) FROM (SELECT cod_area,nombre,descripcion,estado FROM areas) t";
        $antes = DB::scalar($consulta);
        $cantidad = DB::table('areas')->count();
        $permisos = DB::table('role_has_permissions')->count();
        if (hash_file('sha256', base_path($ddl)) !== $hashDdl) {
            throw new RuntimeException('Cambió el DDL autorizado.');
        }
        (require base_path($ddl))->up();
        if ($antes !== DB::scalar($consulta) || $cantidad !== DB::table('areas')->count()) {
            throw new RuntimeException('Cambió información previa; se revierte.');
        }
        if (Schema::getColumnListing('areas') !== ['cod_area', 'nombre', 'descripcion', 'estado', 'cod_fotografia']) {
            throw new RuntimeException('Extensión distinta de la autorizada.');
        }
        if (DB::table('areas')->whereNotNull('cod_fotografia')->exists()) {
            throw new RuntimeException('No se admite asignar fotografías previas por suposición.');
        }
        $despues = Schema::getTableListing();
        sort($despues);
        if ($tablas !== $despues || $permisos !== DB::table('role_has_permissions')->count()) {
            throw new RuntimeException('Cambió el inventario o los permisos.');
        }
        DB::table('migrations')->insert(['migration' => $nombre, 'batch' => (int) DB::table('migrations')->max('batch') + 1]);

        return ['instalado' => true, 'motor' => 'pgsql', 'filas_previas' => $cantidad, 'datos_previos_identicos' => true,
            'fotografias_previas_nulas' => true, 'columnas_nuevas' => ['cod_fotografia'], 'tablas_fisicas' => count($tablas), 'permisos_conservados' => true];
    });
    file_put_contents(base_path('output/areas-fotografia-instalacion-20261108.json'), json_encode($evidencia + [
        'ddl_sha256' => $hashDdl, 'respaldo_privado' => $respaldo, 'respaldo_sha256' => hash_file('sha256', $respaldo), 'catalogo_respaldo_verificado' => true,
    ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    echo "Instalado el vínculo opcional de fotografía del área. Datos anteriores, tablas y permisos conservados.\n";
} finally {
    DB::statement("SELECT pg_advisory_unlock(hashtext('remembermind-area-fotografia-20261108'))");
}
