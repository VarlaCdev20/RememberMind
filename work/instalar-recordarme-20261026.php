<?php

declare(strict_types=1);

// Operación específica autorizada por la propietaria. No es un instalador general.
if (PHP_SAPI !== 'cli' || ($argv[1] ?? '') !== '--aprobado-20261026') {
    exit('Se requiere la confirmación explícita del alcance aprobado.'.PHP_EOL);
}
$raiz = dirname(__DIR__);
require $raiz.'/vendor/autoload.php';
$app = require $raiz.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = Illuminate\Support\Facades\DB::connection();
if (! $app->environment('local') || $db->getDriverName() !== 'pgsql'
    || $db->getDatabaseName() !== 'remembermind_dev'
    || ! in_array($db->getConfig('host'), ['127.0.0.1', 'localhost'], true)) {
    throw new RuntimeException('El destino no coincide con la instalación local aprobada.');
}
$control = static function () use ($db): array {
    $cuentas = $db->selectOne("SELECT count(*)::int AS total, md5(coalesce(string_agg(md5((to_jsonb(u) - 'remember_token')::text), '' ORDER BY cod_usuario), '')) AS huella FROM usuarios u");
    $vinculos = $db->select("SELECT conname, pg_get_constraintdef(oid) AS definicion FROM pg_constraint WHERE contype IN ('p','f','u') AND (conrelid = 'usuarios'::regclass OR confrelid = 'usuarios'::regclass) ORDER BY conname");
    $tablas = $db->selectOne("SELECT count(*)::int AS total FROM pg_tables WHERE schemaname = 'public'");

    return ['cuentas' => (array) $cuentas, 'vinculos' => hash('sha256', json_encode($vinculos, JSON_THROW_ON_ERROR)), 'tablas' => (int) $tablas->total];
};
$evidencia = $db->transaction(function () use ($db, $control): array {
    $db->statement("SET LOCAL lock_timeout = '5s'");
    $db->statement('LOCK TABLE usuarios IN SHARE ROW EXCLUSIVE MODE');
    $antes = $control();
    $codigo = Illuminate\Support\Facades\Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_10_26_000001_add_remember_token_to_usuarios.php', '--force' => true,
    ]);
    if ($codigo !== 0) throw new RuntimeException('La migración específica no terminó correctamente.');
    $despues = $control();
    if ($antes !== $despues) throw new RuntimeException('El control de integridad detectó cambios fuera de la columna aprobada.');
    $columna = $db->selectOne("SELECT data_type, character_maximum_length, is_nullable FROM information_schema.columns WHERE table_schema = 'public' AND table_name = 'usuarios' AND column_name = 'remember_token'");
    if (! $columna || $columna->data_type !== 'character varying' || (int) $columna->character_maximum_length !== 100 || $columna->is_nullable !== 'YES') {
        throw new RuntimeException('La columna instalada no coincide con la aprobada.');
    }

    return ['database' => $db->getDatabaseName(), 'columna_instalada' => true, 'tipo' => 'VARCHAR(100) NULL',
        'cuentas_antes' => $antes['cuentas']['total'], 'cuentas_despues' => $despues['cuentas']['total'],
        'contenido_cuentas_conservado' => true, 'pk_fk_unique_conservadas' => true,
        'tablas_antes' => $antes['tablas'], 'tablas_despues' => $despues['tablas']];
});

// Cambiar exclusivamente las dos opciones aprobadas, preservando el resto del .env.
$archivo = fopen($raiz.'/.env', 'r+');
if (! $archivo || ! flock($archivo, LOCK_EX)) throw new RuntimeException('No se pudo bloquear la configuración local.');
try {
    $original = stream_get_contents($archivo);
    $salto = str_contains($original, "\r\n") ? "\r\n" : "\n";
    $actualizado = $original;
    foreach (['AUTH_REMEMBER_ENABLED' => 'true', 'AUTH_REMEMBER_MINUTES' => '43200'] as $clave => $valor) {
        $patron = '/^'.preg_quote($clave, '/').'=[^\r\n]*/m';
        if (preg_match($patron, $actualizado)) {
            $actualizado = preg_replace($patron, $clave.'='.$valor, $actualizado);
        } else {
            $actualizado = rtrim($actualizado, "\r\n").$salto.$clave.'='.$valor.$salto;
        }
    }
    $sinOpciones = static fn (string $texto): string => rtrim(preg_replace('/^AUTH_REMEMBER_(?:ENABLED|MINUTES)=[^\r\n]*\r?\n?/m', '', $texto), "\r\n");
    if ($sinOpciones($original) !== $sinOpciones($actualizado)) throw new RuntimeException('La edición afectaría configuración ajena al alcance.');
    rewind($archivo);
    if (fwrite($archivo, $actualizado) !== strlen($actualizado)) throw new RuntimeException('No se completó la escritura de configuración.');
    ftruncate($archivo, strlen($actualizado));
    fflush($archivo);
} finally {
    flock($archivo, LOCK_UN);
    fclose($archivo);
}
if (Illuminate\Support\Facades\Artisan::call('config:clear') !== 0) throw new RuntimeException('No se pudo refrescar la configuración.');
$evidencia['duracion_minutos_aprobada'] = 43200;
$evidencia['opciones_configuracion_actualizadas'] = true;
$texto = json_encode($evidencia, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR).PHP_EOL;
file_put_contents($raiz.'/output/instalacion-recordarme-20261026.json', $texto);
echo $texto;
