<?php

namespace App\Modulos\ApoyoDecision\Dominio\Reglas;

/** Identifica destinos técnicos; el llamador exige además entorno testing. */
final class BaseTecnicaDesechable
{
    public static function permite(string $driver, string $database, ?string $username = null, ?string $host = null): bool
    {
        if ($driver === 'sqlite') {
            return $database === ':memory:';
        }
        if ($driver !== 'pgsql') {
            return false;
        }

        // Conserva el contrato dedicado anterior de herramientas técnicas existentes.
        return preg_match('/^remembermind_experto_test_[0-9]{8}_[a-z0-9]+$/D', $database) === 1
            || (preg_match('/^remembermind_test_[a-f0-9]{8}_testing$/D', $database) === 1
                && $username === $database.'_runner' && $host === '127.0.0.1');
    }
}
