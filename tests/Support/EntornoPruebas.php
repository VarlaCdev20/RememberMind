<?php

declare(strict_types=1);

namespace Tests\Support;

use RuntimeException;

final class EntornoPruebas
{
    public static function postgresDesechable(string $database, string $username, string $host): bool
    {
        return preg_match('/^remembermind_test_[a-f0-9]{8}_testing$/D', $database) === 1
            && $username === $database.'_runner'
            && $host === '127.0.0.1';
    }

    public static function validarVariables(): void
    {
        if (getenv('APP_ENV') !== 'testing' || getenv('DB_URL') !== '') {
            throw new RuntimeException('Las pruebas exigen testing y ninguna URL de conexión heredada.');
        }
        if (getenv('RM_TEST_PROFILE') === 'postgres') {
            if (getenv('DB_CONNECTION') !== 'pgsql'
                || ! self::postgresDesechable((string) getenv('DB_DATABASE'), (string) getenv('DB_USERNAME'), (string) getenv('DB_HOST'))
                || ! ctype_digit((string) getenv('DB_PORT')) || getenv('DB_PASSWORD') === false || getenv('DB_PASSWORD') === '') {
                throw new RuntimeException('PostgreSQL exige perfil explícito, base dedicada y runner propio en loopback.');
            }

            return;
        }
        if (getenv('DB_CONNECTION') !== 'sqlite' || getenv('DB_DATABASE') !== ':memory:') {
            throw new RuntimeException('Las pruebas predeterminadas requieren SQLite :memory:.');
        }
    }
}
