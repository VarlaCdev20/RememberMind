<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Solo para migrate:fresh del runner propio: DROP TABLE no elimina las funciones de los triggers. */
final class RestablecerEsquemaPostgres
{
    public static function antesDeFresh(?string $nombre): void
    {
        $nombre ??= DB::getDefaultConnection();
        $declarada = config('database.connections.'.$nombre, []);
        if (! empty($declarada['read']) || ! empty($declarada['write']) || ! empty($declarada['url'])) {
            throw new RuntimeException('El runner no admite destinos alternativos de lectura/escritura ni URL.');
        }
        $db = DB::connection($nombre);
        $config = $db->getConfig();
        if ($db->getDriverName() !== 'pgsql') {
            if (app()->environment('testing') && $db->getDriverName() === 'sqlite'
                && ($config['database'] ?? null) === ':memory:' && empty($config['url'])) {
                return;
            }
            throw new RuntimeException('Se rechaza fresh sobre una conexión no desechable.');
        }
        if (! app()->environment('testing') || getenv('RM_TEST_PROFILE') !== 'postgres'
            || ! EntornoPruebas::postgresDesechable((string) ($config['database'] ?? ''), (string) ($config['username'] ?? ''), (string) ($config['host'] ?? ''))
            || ! empty($config['url']) || ! empty($config['read']) || ! empty($config['write'])
            || ($config['search_path'] ?? null) !== 'public') {
            throw new RuntimeException('Solo se restablece public en el runner PostgreSQL desechable verificado.');
        }
        foreach (DB::getConnections() as $conexion) {
            $pdo = $conexion->getRawPdo();
            if ($conexion->getConfig('database') === $config['database']
                && ($conexion->transactionLevel() !== 0 || ($pdo instanceof \PDO && $pdo->inTransaction()))) {
                throw new RuntimeException('No se restablece un esquema con transacciones abiertas.');
            }
        }
        $identidad = $db->selectOne('SELECT current_database() AS database, current_user AS username, current_schema() AS schema,
            (SELECT CASE WHEN rolsuper OR rolcreatedb OR rolcreaterole THEN 1 ELSE 0 END FROM pg_roles WHERE rolname=current_user) AS privileged,
            (SELECT pg_get_userbyid(datdba) FROM pg_database WHERE datname=current_database()) AS owner', [], false);
        if ($identidad->database !== $config['database'] || $identidad->username !== $config['username']
            || $identidad->owner !== $config['username'] || $identidad->schema !== 'public' || (int) $identidad->privileged !== 0) {
            throw new RuntimeException('La identidad SQL no acredita propiedad exclusiva y privilegios limitados del runner.');
        }
        // Cerrar PDO auxiliares del mismo destino; no tocar conexiones de otras bases.
        foreach (DB::getConnections() as $alias => $conexion) {
            if ($alias !== $nombre && $conexion->getConfig('database') === $config['database']
                && $conexion->getConfig('host') === $config['host'] && $conexion->getConfig('port') === $config['port']) {
                DB::purge($alias);
            }
        }
        $db->statement('DROP SCHEMA IF EXISTS public CASCADE');
        $db->statement('CREATE SCHEMA public');
        DB::purge($nombre);
    }
}
