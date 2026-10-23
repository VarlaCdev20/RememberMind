<?php

declare(strict_types=1);

namespace OrionFase02;

use PDO;
use RuntimeException;

final class GuardiaDesechable
{
    public static function conectar(): PDO
    {
        $db = (string) getenv('DB_DATABASE');
        $usuario = (string) getenv('DB_USERNAME');
        $host = (string) getenv('DB_HOST');
        $puerto = (string) getenv('DB_PORT');
        if (getenv('RM_TEST_PROFILE') !== 'postgres' || ! preg_match('/^remembermind_test_[a-f0-9]{8}_testing$/D', $db)
            || $usuario !== $db.'_runner' || ! in_array($host, ['127.0.0.1', 'localhost'], true)
            || ! ctype_digit($puerto) || (int) $puerto < 1 || (int) $puerto > 65535 || getenv('DB_URL')) {
            throw new RuntimeException('El prototipo sólo admite el runner PostgreSQL desechable propio.');
        }
        $pdo = new PDO("pgsql:host=$host;port=$puerto;dbname=$db;options='-c search_path=public -c statement_timeout=20000'", $usuario, (string) getenv('DB_PASSWORD'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        self::verificar($pdo);

        return $pdo;
    }

    public static function verificar(PDO $pdo): void
    {
        $r = $pdo->query("SELECT current_database() AS base,current_user AS usuario,current_schema() AS esquema,
            (SELECT pg_get_userbyid(datdba) FROM pg_database WHERE datname=current_database()) AS propietario,
            (SELECT rolsuper OR rolcreatedb OR rolcreaterole FROM pg_roles WHERE rolname=current_user) AS privilegiado")->fetch(PDO::FETCH_ASSOC);
        if ($r['base'] !== getenv('DB_DATABASE') || $r['usuario'] !== getenv('DB_USERNAME') || $r['propietario'] !== $r['usuario']
            || $r['esquema'] !== 'public' || $r['privilegiado'] !== false) {
            throw new RuntimeException('La identidad SQL no acredita una base exclusiva de ensayo.');
        }
    }

    public static function instalar(PDO $pdo): void
    {
        self::verificar($pdo);
        if ($pdo->inTransaction() || $pdo->query("SELECT count(*) FROM pg_namespace WHERE nspname='orion_f02'")->fetchColumn() != 0) {
            throw new RuntimeException('Instalación nueva requerida; no se sustituye un prototipo previo.');
        }
        $pdo->beginTransaction();
        try {
            $pdo->exec(file_get_contents(__DIR__.'/esquema.up.sql'));
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
