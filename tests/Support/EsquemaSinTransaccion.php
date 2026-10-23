<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

/** Para coordinadores que deben confirmar su propia transacción en una base desechable. */
trait EsquemaSinTransaccion
{
    public function setUpEsquemaSinTransaccion(): void
    {
        // Tests\TestCase y el listener de fresh verifican destino/identidad antes de DDL.
        $this->artisan('migrate:fresh')->assertExitCode(0);
        $this->app[Kernel::class]->setArtisan(null);
        RefreshDatabaseState::$migrated = false;
    }

    public function tearDownEsquemaSinTransaccion(): void
    {
        // El siguiente test recreará el esquema. No se intenta revertir historia clínica.
        RefreshDatabaseState::$migrated = false;
    }
}
