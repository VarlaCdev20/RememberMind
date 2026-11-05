<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! in_array('--instalar-aprobado-personal-paises', $argv, true)) throw new RuntimeException('Falta alcance aprobado.');
$db = DB::connection();
if ($db->getDriverName() !== 'pgsql' || $db->getDatabaseName() !== 'remembermind_dev' || ! in_array($db->getConfig('host'), ['127.0.0.1','localhost'], true)) throw new RuntimeException('Destino distinto de la base local autorizada.');
foreach (['output/personal-paises-sqlite-final.xml','output/personal-paises-postgres-final.xml'] as $ruta) {
    $xml = simplexml_load_file(base_path($ruta));
    if (! $xml) throw new RuntimeException('Falta evidencia desechable.');
    $suites = $xml->xpath('//testsuite[@file]');
    if (! $suites || array_sum(array_map(fn($s)=>(int)$s['tests'],$suites)) < 48 || array_sum(array_map(fn($s)=>(int)$s['failures']+(int)$s['errors'],$suites))) throw new RuntimeException('Las pruebas del alcance no están verificadas.');
}
$ddl = 'database/propuestas/gerencia/2026_11_04_000004_pais_documento_telefono_personal.php';
$nombre = pathinfo($ddl, PATHINFO_FILENAME);
$columnasNuevas = ['pais_documento','tipo_documento','pais_telefono'];
if (DB::table('migrations')->where('migration',$nombre)->exists()) {
    if (! Schema::hasColumns('personal',$columnasNuevas)) throw new RuntimeException('Registro y esquema no coinciden.');
    echo "La ampliación de países ya está instalada.\n"; exit(0);
}
foreach ($columnasNuevas as $c) if (Schema::hasColumn('personal',$c)) throw new RuntimeException('Estructura parcial o sin registro; no se sobrescribe.');
$tablas = Schema::getTableListing(); sort($tablas);
if (count($tablas) !== 122) throw new RuntimeException('Inventario distinto del aprobado; requiere diagnóstico.');
$hashDdl = hash_file('sha256',base_path($ddl));
$respaldo = storage_path('app/private/backups/personal-paises-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(4)).'.dump');
if (! is_dir(dirname($respaldo))) mkdir(dirname($respaldo),0700,true);
$bin = 'C:/Program Files/PostgreSQL/18/bin/';
$dump = new Process([$bin.'pg_dump.exe','-h',$db->getConfig('host'),'-p',(string)$db->getConfig('port'),'-U',$db->getConfig('username'),'-d',$db->getDatabaseName(),'--format=custom','--no-owner','--file='.$respaldo],base_path(),['PGPASSWORD'=>(string)$db->getConfig('password')],timeout:180);
$dump->run();
if (! $dump->isSuccessful() || ! is_file($respaldo) || ! filesize($respaldo)) throw new RuntimeException('No se confirmó el respaldo; no se instala.');
$catalogo = new Process([$bin.'pg_restore.exe','--list',$respaldo],base_path(),timeout:30); $catalogo->run();
if (! $catalogo->isSuccessful()) throw new RuntimeException('Catálogo de respaldo ilegible.');
if (! DB::scalar("SELECT pg_try_advisory_lock(hashtext('remembermind-personal-paises-20261104'))")) throw new RuntimeException('Otra instalación está en curso.');
try {
    $evidencia = DB::transaction(function () use($ddl,$nombre,$columnasNuevas,$tablas,$hashDdl) {
        DB::statement("SET LOCAL lock_timeout = '10s'"); DB::statement("SET LOCAL statement_timeout = '90s'");
        DB::statement('LOCK TABLE personal IN ACCESS EXCLUSIVE MODE');
        if (hash_file('sha256',base_path($ddl)) !== $hashDdl) throw new RuntimeException('Cambió el DDL aprobado.');
        $columnas = Schema::getColumnListing('personal');
        $seleccion = implode(',',array_map(fn($c)=>'"'.$c.'"',$columnas));
        $consulta = "SELECT md5(COALESCE(string_agg(md5(row_to_json(t)::text),'' ORDER BY md5(row_to_json(t)::text)),'')) FROM (SELECT {$seleccion} FROM personal) t";
        $antes = DB::scalar($consulta); $cantidad = DB::table('personal')->count();
        (require base_path($ddl))->up();
        if ($antes !== DB::scalar($consulta) || $cantidad !== DB::table('personal')->count()) throw new RuntimeException('Cambió información existente; se revierte.');
        if (array_values(array_diff(Schema::getColumnListing('personal'),$columnas)) !== $columnasNuevas) throw new RuntimeException('Extensión distinta de las tres columnas aprobadas.');
        if (DB::table('personal')->whereNotNull('pais_documento')->orWhereNotNull('tipo_documento')->orWhereNotNull('pais_telefono')->exists()) throw new RuntimeException('No se admite inferir países previos.');
        DB::table('migrations')->insert(['migration'=>$nombre,'batch'=>(int)DB::table('migrations')->max('batch')+1]);
        $despues = Schema::getTableListing(); sort($despues);
        if ($despues !== $tablas) throw new RuntimeException('Cambió el inventario de tablas.');
        return ['instalado'=>true,'filas_previas'=>$cantidad,'datos_previos_identicos'=>true,'campos_previos_nulos'=>true,'columnas'=>$columnasNuevas,'tablas_fisicas'=>count($tablas)];
    });
    file_put_contents(base_path('output/personal-paises-instalacion-20261104.json'),json_encode($evidencia+['ddl_sha256'=>$hashDdl,'respaldo_privado'=>$respaldo,'respaldo_sha256'=>hash_file('sha256',$respaldo),'catalogo_respaldo_verificado'=>true],JSON_THROW_ON_ERROR|JSON_PRETTY_PRINT));
    echo "Instaladas tres columnas opcionales en Personal; filas anteriores idénticas y metadatos nulos. Sin tablas ni permisos nuevos.\n";
} finally { DB::statement("SELECT pg_advisory_unlock(hashtext('remembermind-personal-paises-20261104'))"); }
