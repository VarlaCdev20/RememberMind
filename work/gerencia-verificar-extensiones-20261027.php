<?php

// Lectura agregada de la instalación local autorizada; no modifica datos ni esquema.
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$db = DB::connection();
if (PHP_SAPI !== 'cli' || ! app()->environment('local') || $db->getDriverName() !== 'pgsql'
    || $db->getDatabaseName() !== 'remembermind_dev' || ! in_array($db->getConfig('host'), ['127.0.0.1', 'localhost'], true)) {
    throw new RuntimeException('Verificación exclusiva de la base local autorizada.');
}
$evidencia = json_decode(file_get_contents(base_path('output/auditoria-integral-20261025/gerencia-instalacion-20261027.json')), true, flags: JSON_THROW_ON_ERROR);
$tablas = ['solicitudes_documentales_personal', 'destinatarios_solicitud_documental', 'entregas_solicitud_documental',
    'movimientos_laborales_personal', 'versiones_vinculo_contacto', 'eventos_asignacion_personal'];
foreach ($evidencia['ddl_sha256'] as $ruta => $hash) {
    if (hash_file('sha256', base_path($ruta)) !== $hash) throw new RuntimeException('El DDL verificado cambió después de instalar.');
}
$migraciones = array_map(fn ($ruta) => pathinfo($ruta, PATHINFO_FILENAME), array_keys($evidencia['ddl_sha256']));
if (DB::table('migrations')->whereIn('migration', $migraciones)->count() !== 2) throw new RuntimeException('Falta registro de transición.');
$restricciones = DB::table('pg_catalog.pg_constraint as c')->join('pg_catalog.pg_class as t', 't.oid', '=', 'c.conrelid')
    ->join('pg_catalog.pg_namespace as n', 'n.oid', '=', 't.relnamespace')->where('n.nspname', 'public')
    ->whereIn('t.relname', $tablas)->orderBy('t.relname')->orderBy('c.conname')
    ->get(['t.relname as tabla', 'c.conname as nombre', 'c.contype as tipo', 'c.convalidated as validada', DB::raw('pg_get_constraintdef(c.oid) as definicion')]);
foreach ($tablas as $tabla) {
    if (! Schema::hasTable($tabla) || $restricciones->where('tabla', $tabla)->where('tipo', 'f')->isEmpty()) {
        throw new RuntimeException('Falta una relación o sus FK.');
    }
}
if ($restricciones->contains(fn ($r) => ! $r->validada)) throw new RuntimeException('Existen restricciones sin validar.');
$triggers = DB::table('pg_catalog.pg_trigger as g')->join('pg_catalog.pg_class as t', 't.oid', '=', 'g.tgrelid')
    ->join('pg_catalog.pg_namespace as n', 'n.oid', '=', 't.relnamespace')->where('n.nspname', 'public')
    ->whereIn('t.relname', ['versiones_vinculo_contacto', 'eventos_asignacion_personal'])->where('g.tgisinternal', false)
    ->orderBy('t.relname')->orderBy('g.tgname')->get(['t.relname as tabla', 'g.tgname as nombre', 'g.tgenabled as habilitado']);
if ($triggers->count() !== 4 || $triggers->contains(fn ($t) => $t->habilitado !== 'O')) throw new RuntimeException('Falta protección de secuencia o inmutabilidad.');
$informe = ['fecha' => now()->toIso8601String(), 'base' => $db->getDatabaseName(), 'estado' => 'VERIFICADO_POR_LECTURA_POSTERIOR',
    'documentacion_disponible' => app(\App\Modulos\Identidad\Aplicacion\Consultas\CapacidadDocumentalPersonal::class)->disponible(),
    'tablas_fisicas' => count(Schema::getTableListing()), 'migraciones_registradas' => 2,
    'ddl_hashes_conservados' => true, 'restricciones' => $restricciones->all(), 'triggers_historicos' => $triggers->all()];
if (! $informe['documentacion_disponible'] || $informe['tablas_fisicas'] !== 122) throw new RuntimeException('Capacidad o inventario no corresponden.');
$ruta = base_path('output/auditoria-integral-20261025/gerencia-verificacion-instalacion-20261027.json');
file_put_contents($ruta, json_encode($informe, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
echo json_encode(['estado' => $informe['estado'], 'fisicas' => $informe['tablas_fisicas'], 'documentacion_disponible' => true,
    'restricciones_validadas' => $restricciones->count(), 'triggers_historicos_habilitados' => $triggers->count(), 'evidencia' => $ruta], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
